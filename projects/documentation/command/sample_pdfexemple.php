<?php
/**
 * Exemple d'extensió dels plugins amb nous 'commnads'
 * aquest exemple no és operatiu, només serveix per verificar el disseny estructural i el fluxe
 *
 * @culpable Rafael Claver
 */
if (!defined('DOKU_INC')) die();

class command_plugin_wikiiocmodel_projects_documentation_pdfexemple extends abstract_command_class {

    private $dataControls;

    public function __construct() {
        parent::__construct();
        $this->types[AjaxKeys::KEY_ID] = self::T_STRING;
        $this->types[AjaxKeys::KEY_DO] = self::T_STRING;
        $this->types['rev'] = self::T_STRING;
        $this->types['range'] = self::T_STRING;
        $this->types['summary'] = self::T_STRING;

        $defaultValues = ['do' => 'wikiiocmodel_projects_documentation_pdfexemple'];
        $this->setParameters($defaultValues);
    }

    public function init( $modelManager = NULL ) {
        parent::init($modelManager);
        $persistenceEngine = $this->modelWrapper->getPersistenceEngine();
        $projectMetaDataQuery = $persistenceEngine->createProjectMetaDataQuery();
        $this->dataControls = $projectMetaDataQuery->getMetaViewConfig($this->projectType, "controls");
    }

    protected function process() {
        $action = new action_plugin_wikiiocmodel_projects_documentation($this->dataControls);
        return $action;
    }

    protected function getDefaultResponse($response, &$ret) {
        $ret->addAlert("UuuuuuEEEE!");
    }
}
