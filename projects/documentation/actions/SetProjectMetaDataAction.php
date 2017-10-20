<?php
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
include_once (DOKU_PLUGIN . 'wikiiocmodel/projects/documentation/actions/ProjectMetadataAction.php');
require_once (DOKU_PLUGIN . 'ownInit/WikiGlobalConfig.php');

/**
 * Desa els canvis fets al formulari que defineix el projecte
 */
class SetProjectMetaDataAction extends ProjectMetadataAction {

    /**
     * Envía los datos $metaData del proyecto al ProjectModel y obtiene la estructura y los valores del proyecto
     * @param array $paramsArr [dataProject[], extraProject[]]
     * @return array con la estructura y los valores del proyecto
     */
    public function responseProcess($paramsArr=array()) {
        $dataProject = $paramsArr['dataProject'];
        $extraProject = $paramsArr['extraProject'];
        $this->projectModel->init($dataProject[ProjectKeys::KEY_ID], $dataProject[ProjectKeys::KEY_PROJECT_TYPE]);

        //sólo se ejecuta si existe el proyecto
        if ($this->projectModel->existProject($dataProject[ProjectKeys::KEY_ID])) {

            $metaDataValues = $this->netejaKeysFormulari($dataProject);
            if (!$this->projectModel->validaNomAutor($metaDataValues['autor'])) {
                throw new UnknownUserException($metaDataValues['autor']." (indicat al camp 'autor') ");
            }
            $metaData = [
                ProjectKeys::KEY_PERSISTENCE => $this->persistenceEngine,
                ProjectKeys::KEY_PROJECT_TYPE => $dataProject[ProjectKeys::KEY_PROJECT_TYPE], //opcional
                ProjectKeys::KEY_METADATA_SUBSET => ProjectKeys::VAL_DEFAULTSUBSET,
                ProjectKeys::KEY_ID_RESOURCE => $dataProject[ProjectKeys::KEY_ID],
                ProjectKeys::KEY_FILTER => $dataProject[ProjectKeys::KEY_FILTER],  //opcional
                ProjectKeys::KEY_METADATA_VALUE => json_encode($metaDataValues)
            ];
            $ret = $this->projectModel->setData($metaData);

            if ($this->projectModel->isProjectGenerated($dataProject[ProjectKeys::KEY_ID], $dataProject[ProjectKeys::KEY_PROJECT_TYPE])) {
                $data = $this->projectModel->getData();   //obtiene la estructura y el contenido del proyecto
                $include = [
                     'id' => $dataProject[ProjectKeys::KEY_ID]
                    ,'link_page' => $dataProject[ProjectKeys::KEY_ID].":".end(explode(":", $data['projectMetaData']['values']["plantilla"]))
                    ,'old_autor' => $extraProject['old_autor']
                    ,'old_responsable' => $extraProject['old_responsable']
                    ,'new_autor' => $data['projectMetaData']['values']['autor']
                    ,'new_responsable' => $data['projectMetaData']['values']['responsable']
                    ,'userpage_ns' => WikiGlobalConfig::getConf('userpage_ns','wikiiocmodel')
                    ,'shortcut_name' => WikiGlobalConfig::getConf('shortcut_page_name','wikiiocmodel')
                ];
                $this->projectModel->modifyACLPageToUser($include);
            }
            $ret['info'] = $this->generateInfo("info", WikiIocLangManager::getLang('project_saved'), $dataProject[ProjectKeys::KEY_ID]);
            $ret[ProjectKeys::KEY_ID] = $this->idToRequestId($dataProject[ProjectKeys::KEY_ID]);
        }
        if (!$ret)
            throw new ProjectExistException($dataProject[ProjectKeys::KEY_ID]);
        else
            return $ret;
    }

    private function netejaKeysFormulari($array) {
        $cleanArray = [];
        $excludeKeys = ['id','ns','projectType','do','submit','sectok'];
        foreach ($array as $key => $value) {
            if (!in_array($key, $excludeKeys)) {
                $cleanArray[$key] = $value;
            }
        }
        return $cleanArray;
    }
}