<?php
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_PLUGIN . "wikiiocmodel/");
include_once (WIKI_IOC_MODEL . 'actions/ProjectMetadataAction.php');

/**
 * Desa els canvis fets al formulari que defineix el projecte
 */
class BasicSetProjectMetaDataAction extends ProjectMetadataAction {

    protected function setParams($params) {
        parent::setParams($params);
        $this->getModel()->init([ProjectKeys::KEY_ID              => $this->params[ProjectKeys::KEY_ID],
                                 ProjectKeys::KEY_PROJECT_TYPE    => $this->params[ProjectKeys::KEY_PROJECT_TYPE],
                                 ProjectKeys::KEY_REV             => $this->params[ProjectKeys::KEY_REV],
                                 ProjectKeys::KEY_METADATA_SUBSET => $this->params[ProjectKeys::KEY_METADATA_SUBSET]
                               ]);
    }

    /**
     * Envía los datos $metaData del proyecto al ProjectModel y obtiene la estructura y los valores del proyecto
     * @return array con la estructura y los valores del proyecto
     */
    protected function responseProcess() {
        $extraProject = $this->params['extraProject'];
        $model = $this->getModel();
        $modelAttrib = $model->getModelAttributes();

        //sólo se ejecuta si existe el proyecto
        if ($model->existProject()) {

            $metaDataValues = $this->netejaKeysFormulari($this->params);
            if (!$model->validaNom($metaDataValues['autor']))
                throw new UnknownUserException($metaDataValues['autor']." (indicat al camp 'autor') ");
            if (!$model->validaNom($metaDataValues['responsable']))
                throw new UnknownUserException($metaDataValues['responsable']." (indicat al camp 'responsable') ");
            if (!$model->validaSubSet($modelAttrib[ProjectKeys::KEY_METADATA_SUBSET]))
                throw new UnknownUserException($modelAttrib[ProjectKeys::KEY_METADATA_SUBSET]." (indicat al 'metaDataSubSet') ");

            $metaData = [
                ProjectKeys::KEY_ID_RESOURCE => $modelAttrib[ProjectKeys::KEY_ID],
                ProjectKeys::KEY_PERSISTENCE => $this->persistenceEngine,
                ProjectKeys::KEY_PROJECT_TYPE => $modelAttrib[ProjectKeys::KEY_PROJECT_TYPE],
                ProjectKeys::KEY_METADATA_SUBSET => $modelAttrib[ProjectKeys::KEY_METADATA_SUBSET],
                ProjectKeys::KEY_FILTER => $this->params[ProjectKeys::KEY_FILTER],  //opcional
                ProjectKeys::KEY_METADATA_VALUE => str_replace("\\r\\n", "\\n", json_encode($metaDataValues))
            ];

            $model->setData($metaData);
            $response = $model->getData();  //obtiene la estructura y el contenido del proyecto
            $response[ProjectKeys::KEY_GENERATED] = $model->isProjectGenerated();

            if ($response[ProjectKeys::KEY_GENERATED]) {
                $include = [
                     'id' => $modelAttrib[ProjectKeys::KEY_ID]
                    ,'link_page' => $modelAttrib[ProjectKeys::KEY_ID].":".end(explode(":", $response['projectMetaData']["plantilla"]['value']))
                    ,'old_autor' => $extraProject['old_autor']
                    ,'old_responsable' => $extraProject['old_responsable']
                    ,'new_autor' => $response['projectMetaData']['autor']['value']
                    ,'new_responsable' => $response['projectMetaData']['responsable']['value']
                    ,'userpage_ns' => WikiGlobalConfig::getConf('userpage_ns','wikiiocmodel')
                    ,'shortcut_name' => WikiGlobalConfig::getConf('shortcut_page_name','wikiiocmodel')
                ];
                $model->modifyACLPageToUser($include);
            }
            if (!$this->params[ProjectKeys::KEY_KEEP_DRAFT]) {
                $model->removeDraft();
            }

            if ($this->params[ProjectKeys::KEY_NO_RESPONSE]) {
                $response[ProjectKeys::KEY_CODETYPE] = 0;
            }else{
                $response['info'] = $this->generateInfo("info", WikiIocLangManager::getLang('project_saved'), $modelAttrib[ProjectKeys::KEY_ID]);
                $response[ProjectKeys::KEY_ID] = $this->idToRequestId($modelAttrib[ProjectKeys::KEY_ID]);
            }
        }

        if (!$response) {
            throw new ProjectExistException($modelAttrib[ProjectKeys::KEY_ID]);
        }else {
            //Añadir propiedades/restricciones del configMain para la creación de elementos dentro del proyecto
            parent::addResponseProperties($response);
            return $response;
        }
    }

    private function netejaKeysFormulari($array) {
        $cleanArray = [];
        $excludeKeys = ['id','do','sectok','projectType','ns','submit', 'cancel','close','keep_draft','no_response','extraProject','metaDataSubSet'];
        foreach ($array as $key => $value) {
            if (!in_array($key, $excludeKeys)) {
                $cleanArray[$key] = $value;
            }
        }
        return $cleanArray;
    }
}
