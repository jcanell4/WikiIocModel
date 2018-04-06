<?php
/**
 * Converteix la revisió en el projecte actual (reverteix el el projecte a una versió anterior)
 * @culpable Rafael
 */
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
include_once (DOKU_PLUGIN . 'wikiiocmodel/projects/documentation/actions/ProjectMetadataAction.php');

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
     * @return array con la estructura y los valores del proyecto
     */
    protected function runProcess() {
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

            $response = $model->setData($metaData);

            if ($model->isProjectGenerated($id, $pType)) {
                $data = $model->getData();   //obtiene la estructura y el contenido del proyecto
                $include = [
                     'id' => $id
                    ,'link_page' => $id.":".end(explode(":", $data['projectMetaData']['values']["plantilla"]))
                    ,'old_autor' => $dataProject['autor']
                    ,'old_responsable' => $dataProject['responsable']
                    ,'new_autor' => $data['projectMetaData']['values']['autor']
                    ,'new_responsable' => $data['projectMetaData']['values']['responsable']
                    ,'userpage_ns' => WikiGlobalConfig::getConf('userpage_ns','wikiiocmodel')
                    ,'shortcut_name' => WikiGlobalConfig::getConf('shortcut_page_name','wikiiocmodel')
                ];
                $model->modifyACLPageToUser($include);
            }

            //Elimina todos los borradores dado que estamos haciendo una reversión del proyecto
            $model->removeDraft();

            $response['info'] = $this->generateInfo("info", WikiIocLangManager::getLang('project_reverted'), $id);
            $response[ProjectKeys::KEY_ID] = $this->idToRequestId($id);
        }

        if (!$response)
            throw new ProjectExistException($id);
        else
            return $response;
    }

    protected function responseProcess() {
        $this->startProcess();
        $ret = $this->runProcess();
        return $ret;
    }

}