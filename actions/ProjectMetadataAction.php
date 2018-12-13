<?php
/**
 * ProjectMetadataAction: Define los elementos comunes de las Actions de un proyecto
 * @culpable Rafael Claver
 */
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');

abstract class ProjectMetadataAction extends AbstractWikiAction {

    protected $persistenceEngine;
    protected $projectModel;
    protected $resourceLocker;

    public function init($modelManager) {
        parent::init($modelManager);
        $this->persistenceEngine = $modelManager->getPersistenceEngine();
        $ownProjectModel = $modelManager->getProjectType()."ProjectModel";
        $this->projectModel = new $ownProjectModel($this->persistenceEngine);
        $this->resourceLocker = new ResourceLocker($this->persistenceEngine);
    }

    protected function getModel() {
        return $this->projectModel;
    }

    protected function idToRequestId($requestId) {
        return str_replace(":", "_", $requestId);
    }

    //Añadir propiedades/restricciones del configMain para la creación de elementos dentro del proyecto
    protected function addResponseProperties(&$response) {
        $response[ProjectKeys::KEY_CREATE][ProjectKeys::KEY_MD_CT_SUBPROJECTS] = $this->projectModel->getMetaDataComponent($this->params[ProjectKeys::KEY_PROJECT_TYPE], ProjectKeys::KEY_MD_CT_SUBPROJECTS); //valores permitidos para el elemento 'create project': array | true (all) | false (none)
        $response[ProjectKeys::KEY_CREATE][ProjectKeys::KEY_MD_CT_DOCUMENTS] = $this->projectModel->getMetaDataComponent($this->params[ProjectKeys::KEY_PROJECT_TYPE], ProjectKeys::KEY_MD_CT_DOCUMENTS); //valores permitidos para el elemento 'create document': array | true (all) | false (none)
        $response[ProjectKeys::KEY_CREATE][ProjectKeys::KEY_MD_CT_FOLDERS] = $this->projectModel->getMetaDataComponent($this->params[ProjectKeys::KEY_PROJECT_TYPE], ProjectKeys::KEY_MD_CT_FOLDERS); //valores permitidos para el elemento 'create folder': true (all) | false (none)
    }

    protected function postResponseProcess(&$response) {
        if ($this->params[ProjectKeys::KEY_METADATA_SUBSET]!=="undefined" && $this->params[ProjectKeys::KEY_METADATA_SUBSET] !== ProjectKeys::VAL_DEFAULTSUBSET) {
            //$response[ProjectKeys::KEY_ID] = $this->projectModel->addSubSetSufix($response[ProjectKeys::KEY_ID], $this->params[ProjectKeys::KEY_METADATA_SUBSET]);
            $response['projectExtraData'][ProjectKeys::KEY_METADATA_SUBSET] = $this->params[ProjectKeys::KEY_METADATA_SUBSET];
            $response['isSubSet'] = TRUE;
        }
    }

    public function generateMessageInfoForSubSetProject($id, $subSet, $message) {
        if ($subSet !== "undefined" && $subSet !== ProjectKeys::VAL_DEFAULTSUBSET) {
            $addmessage = " (subconjunt $subSet).";
        }else{
            $addmessage = "";
        }
        $new_message = $this->generateInfo("info", WikiIocLangManager::getLang($message), $id);
        $new_message['message'] .= $addmessage;
        return $new_message;
    }

}
