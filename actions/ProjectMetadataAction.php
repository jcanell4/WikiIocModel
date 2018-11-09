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
        $this->projectModel = new $ownProjectModel($this->persistenceEngine, $modelManager->getProjectTypeDir());
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

    protected function findProjectTypeDir($projectType){
        global $plugin_controller;
        $plugin_list = $plugin_controller->getList('action');
        //busca el tipo de proyecto solicitado en todos los directorios de plugins del tipo action
        foreach ($plugin_list as $plugin) {
            $dir = DOKU_PLUGIN."$plugin/projects/$projectType/";
            if (file_exists("$dir.DokuModelManager.php")) {
                break;
            }
        }
        return $dir;
    }

}
