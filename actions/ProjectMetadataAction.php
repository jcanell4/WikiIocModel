<?php
/**
 * Clase que define los elementos comunes de las Actions de este proyecto
 *
 * @culpable Rafael Claver
 */
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
require_once DOKU_PLUGIN . "wikiiocmodel/actions/AbstractWikiAction.php";
require_once DOKU_PLUGIN . "wikiiocmodel/projects/documentation/datamodel/ProjectModel.php";

//[JOSEP] ALERTA: Cal pujar tota la classe a wikiiocmodel/actions
abstract class ProjectMetadataAction extends AbstractWikiAction {

    protected $persistenceEngine;
    protected $projectModel;

    public function init($modelManager) {
        parent::init($modelManager);
        $this->persistenceEngine = $modelManager->getPersistenceEngine();
        $this->projectModel = new ProjectModel($this->persistenceEngine);
    }

    protected function getModel() {
        return $this->projectModel;
    }

    protected function idToRequestId($requestId) {
        return str_replace(":", "_", $requestId);
    }

    //Añadir propiedades/restricciones del configMain para la creación de elementos dentro del proyecto
    protected function addResponseProperties(&$response) {
        $response['create'][ProjectKeys::KEY_MD_CT_SUBPROJECTS] = $this->projectModel->getMetaDataComponent($this->params[ProjectKeys::KEY_PROJECT_TYPE], ProjectKeys::KEY_MD_CT_SUBPROJECTS); //valores permitidos para el elemento 'create project': array | true (all) | false (none)
        $response['create'][ProjectKeys::KEY_MD_CT_DOCUMENTS] = $this->projectModel->getMetaDataComponent($this->params[ProjectKeys::KEY_PROJECT_TYPE], ProjectKeys::KEY_MD_CT_DOCUMENTS); //valores permitidos para el elemento 'create document': array | true (all) | false (none)
        $response['create'][ProjectKeys::KEY_MD_CT_FOLDERS] = $this->projectModel->getMetaDataComponent($this->params[ProjectKeys::KEY_PROJECT_TYPE], ProjectKeys::KEY_MD_CT_FOLDERS); //valores permitidos para el elemento 'create folder': true (all) | false (none)
    }

}
