<?php
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
include_once (DOKU_PLUGIN . 'wikiiocmodel/projects/iocdocum/actions/ProjectMetadataAction.php');

/**
 * Desa els canvis fets al formulari que defineix el projecte
 */
class SetProjectMetaDataAction extends ProjectMetadataAction {

    /**
     * Envía los datos $metaData del proyecto al ProjectModel y obtiene la estructura y los valores del proyecto
     * @return array con la estructura y los valores del proyecto
     */
    public function responseProcess() {
        $dataProject = $this->params['dataProject'];
        $extraProject = $this->params['extraProject'];
        $this->getModel()->init($dataProject[ProjectKeys::KEY_ID], $dataProject[ProjectKeys::KEY_PROJECT_TYPE]);

        //sólo se ejecuta si existe el proyecto
        if ($this->getModel()->existProject($dataProject[ProjectKeys::KEY_ID])) {

            $metaDataValues = $this->netejaKeysFormulari($dataProject);
            if (!$this->getModel()->validaNom($metaDataValues['autor']))
                throw new UnknownUserException($metaDataValues['autor']." (indicat al camp 'autor') ");
            if (!$this->getModel()->validaNom($metaDataValues['responsable']))
                throw new UnknownUserException($metaDataValues['responsable']." (indicat al camp 'responsable') ");

            $metaData = [
                ProjectKeys::KEY_PERSISTENCE => $this->persistenceEngine,
                ProjectKeys::KEY_PROJECT_TYPE => $dataProject[ProjectKeys::KEY_PROJECT_TYPE], //opcional
                ProjectKeys::KEY_METADATA_SUBSET => ProjectKeys::VAL_DEFAULTSUBSET,
                ProjectKeys::KEY_ID_RESOURCE => $dataProject[ProjectKeys::KEY_ID],
                ProjectKeys::KEY_FILTER => $dataProject[ProjectKeys::KEY_FILTER],  //opcional
                ProjectKeys::KEY_METADATA_VALUE => json_encode($metaDataValues)
            ];

            $this->getModel()->setData($metaData);
            $response = $this->getModel()->getData();

            if ($this->getModel()->isProjectGenerated($dataProject[ProjectKeys::KEY_ID], $dataProject[ProjectKeys::KEY_PROJECT_TYPE])) {
                $data = $this->getModel()->getData();   //obtiene la estructura y el contenido del proyecto
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
                $this->getModel()->modifyACLPageToUser($include);
            }
            if (!$dataProject[ProjectKeys::KEY_KEEP_DRAFT]) {
                $this->getModel()->removeDraft();
            }

            if ($dataProject[ProjectKeys::KEY_NO_RESPONSE]) {
                $response[ProjectKeys::KEY_CODETYPE] = 0;
            }else{
                $response['info'] = $this->generateInfo("info", WikiIocLangManager::getLang('project_saved'), $dataProject[ProjectKeys::KEY_ID]);
                $response[ProjectKeys::KEY_ID] = $this->idToRequestId($dataProject[ProjectKeys::KEY_ID]);
            }
        }

        if (!$response)
            throw new ProjectExistException($dataProject[ProjectKeys::KEY_ID]);
        else
            return $response;
    }

    private function netejaKeysFormulari($array) {
        $cleanArray = [];
        $excludeKeys = ['id','do','sectok','projectType','ns','submit', 'cancel','close','keep_draft','no_response'];
        foreach ($array as $key => $value) {
            if (!in_array($key, $excludeKeys)) {
                $cleanArray[$key] = $value;
            }
        }
        return $cleanArray;
    }
}