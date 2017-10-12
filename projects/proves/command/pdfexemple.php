<?php
/**
 * Exemple d'extensió dels plugins amb nous 'commnads'
 * aquest exemple no és operatiu, només serveix per verificar el disseny estructural i el fluxe
 *
 * @culpable Rafael Claver
 */
if (!defined('DOKU_INC')) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
if (!defined('DOKU_COMMAND')) define('DOKU_COMMAND', DOKU_PLUGIN . "ajaxcommand/");

require_once(DOKU_COMMAND . 'AjaxCmdResponseGenerator.php');
require_once(DOKU_COMMAND . 'abstract_command_class.php');

class command_plugin_wikiiocmodel_projects_documentation_pdfexemple extends abstract_command_class {

    private $dataControls;

    public function __construct() {
        parent::__construct();
        $this->types['id'] = abstract_command_class::T_STRING;
        $this->types['rev'] = abstract_command_class::T_STRING;
        $this->types['range'] = abstract_command_class::T_STRING;
        $this->types['summary'] = abstract_command_class::T_STRING;
        $this->types['do'] = abstract_command_class::T_STRING;

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
        $ret->addAlert("UUUEEEE!");
    }
}
