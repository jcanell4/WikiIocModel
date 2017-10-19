<?php
/**
 * projectrender : command que es dispara pels botons que generen HTML i PDF
 * @culpable Rafael Claver
 */
if (!defined('DOKU_INC')) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
if (!defined('DOKU_COMMAND')) define('DOKU_COMMAND', DOKU_PLUGIN . "ajaxcommand/");

require_once(DOKU_COMMAND . 'AjaxCmdResponseGenerator.php');
require_once(DOKU_COMMAND . 'JsonGenerator.php');
require_once(DOKU_COMMAND . 'abstract_command_class.php');
require_once(DOKU_PLUGIN . 'iocexportl/action.php');

class command_plugin_wikiiocmodel_projects_documentation_projectexport extends abstract_command_class {
//class projectrenderer extends abstract_command_class {

    public function __construct() {
        parent::__construct();
        $this->types['id'] = abstract_command_class::T_STRING;
        $this->types['projectType'] = abstract_command_class::T_STRING;
        $this->types['mode'] = abstract_command_class::T_STRING;
//        $this->types['renderType'] = abstract_command_class::T_STRING;
//        $this->types['specificRender'] = abstract_command_class::T_STRING;

        //$defaultValues = ['do' => 'wikiiocmodel_projects_documentation_projectrenderer'];
        //$this->setParameters($defaultValues);
    }

    public function init( $modelManager=NULL ) {
        global $plugin_controller;
        //$paramModelManagerType = $this->getConf('paramModelManagerType'); ERROR
        $plugin_controller->setCurrentProject($this->params['projectType']);

        if (!$modelManager) {
            $modelManager = WikiIocModelManager::Instance($this->params['projectType']);
        }
        $this->setModelManager($modelManager);
    }

    protected function process() {
        $params=array(
            "id" => $this->params['id'],
            "ns" => str_replace("_", ":", $this->params['id']),
            "projectType" => $this->params["projectType"],
            "mode" => $this->params["mode"]
        );
        $action = $this->modelManager->getActionInstance("ProjectExportAction", $this->modelManager->getExporterManager());
        $action->init($params);
        $content = $action->process();
        $projectId = $action->getProjectID();
        return array('projectId' => $projectId, 'meta' => $content);
    }

    protected function getDefaultResponse($response, &$ret) {
        if ($response) {
            $response['projectType'] = $this->params['projectType'];
            $meta = $response["meta"];

            $pageId = $this->params['id'];
            $ret->addExtraMetadata($pageId, $pageId."_iocexportxhtml", WikiIocLangManager::getLang("metadata_export_title"), $meta);
        }else {
            $ret->addError(1000, "EXPORTACIÓ NO REALITZADA");
        }
    }

    public function getAuthorizationType() {
        return "save";
    }
}
