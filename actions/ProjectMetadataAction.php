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

    protected function preResponseProcess() {
        if ($this->projectModel->getDataProject($this->params[ProjectKeys::KEY_ID], $this->params[ProjectKeys::KEY_PROJECT_TYPE], $this->params[ProjectKeys::KEY_METADATA_SUBSET])) {
            //versión guardada en el subset del fichero system del proyecto
            $ver_project = $this->projectModel->getProjectSystemSubSetAttr("version", $this->params[ProjectKeys::KEY_METADATA_SUBSET]);
            if ($ver_project == NULL) $ver_project = 0;
            //versión establecida en el archivo configMain.json (subset correspondiente) del tipo de proyecto
            $ver_config = $this->projectModel->getMetaDataAnyAttr("version");
            if ($ver_config == NULL) $ver_config = 0;

            if ($ver_project > $ver_config) {
                throw new Exception ("La versió del projecte és major que la versió definida al tipus de projecte: $ver_project > $ver_config");
            }
            if ($ver_project !== $ver_config) {
                $upgader = new UpgradeManager($this->projectModel, $this->params[ProjectKeys::KEY_PROJECT_TYPE], $this->params[ProjectKeys::KEY_METADATA_SUBSET], $ver_project, $ver_config);
                $upgader->process($ver_project, $ver_config);
            }
        }
    }

    protected function postResponseProcess(&$response) {
        if ($this->params[ProjectKeys::KEY_METADATA_SUBSET] && $this->params[ProjectKeys::KEY_METADATA_SUBSET]!=="undefined" && $this->params[ProjectKeys::KEY_METADATA_SUBSET] !== ProjectKeys::VAL_DEFAULTSUBSET) {
            //$response[ProjectKeys::KEY_ID] = $this->projectModel->addSubSetSufix($response[ProjectKeys::KEY_ID], $this->params[ProjectKeys::KEY_METADATA_SUBSET]);
            $response[ProjectKeys::KEY_PROJECT_EXTRADATA][ProjectKeys::KEY_METADATA_SUBSET] = $this->params[ProjectKeys::KEY_METADATA_SUBSET];
            $response['isSubSet'] = TRUE;
        }
        $response[ProjectKeys::KEY_GENERATED] = $this->getModel()->isProjectGenerated();
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
