<?php
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
include_once (DOKU_PLUGIN . 'wikiiocmodel/actions/ProjectMetadataAction.php');

/**
 * Desa els canvis fets al formulari que defineix el projecte
 */
class BasicSetProjectMetaDataAction extends ProjectMetadataAction {

    /**
     * Envía los datos $metaData del proyecto al ProjectModel y obtiene la estructura y los valores del proyecto
     * @return array con la estructura y los valores del proyecto
     */
    public function responseProcess() {
        $dataProject = $this->params['dataProject'];
        $extraProject = $this->params['extraProject'];
        $model = $this->getModel();
        $model->init($dataProject[ProjectKeys::KEY_ID], $dataProject[ProjectKeys::KEY_PROJECT_TYPE]);

        //sólo se ejecuta si existe el proyecto
        if ($model->existProject()) {

            $metaDataValues = $this->netejaKeysFormulari($dataProject);
            if (!$model->validaNom($metaDataValues['autor']))
                throw new UnknownUserException($metaDataValues['autor']." (indicat al camp 'autor') ");
            if (!$model->validaNom($metaDataValues['responsable']))
                throw new UnknownUserException($metaDataValues['responsable']." (indicat al camp 'responsable') ");

            $metaData = [
                ProjectKeys::KEY_PERSISTENCE => $this->persistenceEngine,
                ProjectKeys::KEY_PROJECT_TYPE => $dataProject[ProjectKeys::KEY_PROJECT_TYPE], //opcional
                ProjectKeys::KEY_METADATA_SUBSET => ProjectKeys::VAL_DEFAULTSUBSET,
                ProjectKeys::KEY_ID_RESOURCE => $dataProject[ProjectKeys::KEY_ID],
                ProjectKeys::KEY_FILTER => $dataProject[ProjectKeys::KEY_FILTER],  //opcional
                ProjectKeys::KEY_METADATA_VALUE => str_replace("\\r\\n", "\\n", json_encode($metaDataValues))
            ];

            $model->setData($metaData);
            $response = $model->getData();  //obtiene la estructura y el contenido del proyecto

            if ($model->isProjectGenerated()) {
                $include = [
                     'id' => $dataProject[ProjectKeys::KEY_ID]
                    ,'link_page' => $dataProject[ProjectKeys::KEY_ID].":".end(explode(":", $response['projectMetaData']["plantilla"]['value']))
                    ,'old_autor' => $extraProject['old_autor']
                    ,'old_responsable' => $extraProject['old_responsable']
                    ,'new_autor' => $response['projectMetaData']['autor']['value']
                    ,'new_responsable' => $response['projectMetaData']['responsable']['value']
                    ,'userpage_ns' => WikiGlobalConfig::getConf('userpage_ns','wikiiocmodel')
                    ,'shortcut_name' => WikiGlobalConfig::getConf('shortcut_page_name','wikiiocmodel')
                ];
                $model->modifyACLPageToUser($include);
            }
            if (!$dataProject[ProjectKeys::KEY_KEEP_DRAFT]) {
                $model->removeDraft();
            }

            if ($dataProject[ProjectKeys::KEY_NO_RESPONSE]) {
                $response[ProjectKeys::KEY_CODETYPE] = 0;
            }else{
                $response['info'] = $this->generateInfo("info", WikiIocLangManager::getLang('project_saved'), $dataProject[ProjectKeys::KEY_ID]);
                $response[ProjectKeys::KEY_ID] = $this->idToRequestId($dataProject[ProjectKeys::KEY_ID]);
            }
        }

        if (!$response) {
            throw new ProjectExistException($dataProject[ProjectKeys::KEY_ID]);
        }else {
            //Añadir propiedades/restricciones del configMain para la creación de elementos dentro del proyecto
            parent::addResponseProperties($response);
            return $response;
        }
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