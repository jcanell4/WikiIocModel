<?php
/**
 * Converteix la revisió en el projecte actual (reverteix el el projecte a una versió anterior)
 * @culpable Rafael
 */
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
include_once (DOKU_PLUGIN . 'wikiiocmodel/actions/ProjectMetadataAction.php');

class RevertProjectMetaDataAction extends ProjectMetadataAction {

    public function init($modelManager) {
        parent::init($modelManager);
    }

    protected function startProcess() {
        $this->getModel()->init($this->params[ProjectKeys::KEY_ID],
                                $this->params[ProjectKeys::KEY_PROJECT_TYPE],
                                $this->params[ProjectKeys::KEY_REV]);
    }

    /**
     * Envía los datos de la revisión al projectModel para sustituir al proyecto actual
     * @return array con la estructura y los valores del proyecto (la revisión se habrá convertido en el proyecto actual)
     */
    private function localRunProcess() {
        $id = $this->params[ProjectKeys::KEY_ID];
        $pType = $this->params[ProjectKeys::KEY_PROJECT_TYPE];
        $rev = $this->params[ProjectKeys::KEY_REV];
        $model = $this->getModel();

        //sólo se ejecuta si existe el proyecto
        if ($model->existProject($id)) {

            $dataProject = $model->getDataProject($id, $pType);
            $dataRevision = $model->getDataRevisionProject($id, $rev);

            $metaData = [
                ProjectKeys::KEY_PERSISTENCE => $this->persistenceEngine,
                ProjectKeys::KEY_PROJECT_TYPE => $pType,
                ProjectKeys::KEY_METADATA_SUBSET => ProjectKeys::VAL_DEFAULTSUBSET,
                ProjectKeys::KEY_ID_RESOURCE => $id,
                ProjectKeys::KEY_FILTER => $this->params[ProjectKeys::KEY_FILTER],  //opcional
                ProjectKeys::KEY_METADATA_VALUE => json_encode($dataRevision)
            ];

            $model->setData($metaData);
            $response = $model->getData();

            if ($model->isProjectGenerated($id, $pType)) {
                $include = [
                     'id' => $id
                    ,'link_page' => $id.":".end(explode(":", $response['projectMetaData']["plantilla"]['value']))
                    ,'old_autor' => $dataProject['autor']
                    ,'old_responsable' => $dataProject['responsable']
                    ,'new_autor' => $response['projectMetaData']['autor']['value']
                    ,'new_responsable' => $response['projectMetaData']['responsable']['value']
                    ,'userpage_ns' => WikiGlobalConfig::getConf('userpage_ns','wikiiocmodel')
                    ,'shortcut_name' => WikiGlobalConfig::getConf('shortcut_page_name','wikiiocmodel')
                ];
                $model->modifyACLPageToUser($include);
            }

            //Elimina todos los borradores dado que estamos haciendo una reversión del proyecto
            $model->removeDraft();
            //afegim les revisions del projecte a la resposta
            $response[ProjectKeys::KEY_REV] = $this->projectModel->getProjectRevisionList($this->params[ProjectKeys::KEY_ID], 0);

            $response['info'] = $this->generateInfo("info", WikiIocLangManager::getLang('project_reverted'), $id);
            $response[ProjectKeys::KEY_ID] = $this->idToRequestId($id);
        }

        if (!$response) throw new ProjectExistException($id);
        return $response;
    }

    protected function responseProcess() {
        $this->startProcess();
        $ret = $this->localRunProcess();
        return $ret;
    }

}