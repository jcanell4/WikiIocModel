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

class command_plugin_wikiiocmodel_projects_documentation_projectrender extends abstract_command_class {
//class projectrenderer extends abstract_command_class {

    public function __construct() {
        parent::__construct();
        $this->types['id'] = abstract_command_class::T_STRING;
        $this->types['projectType'] = abstract_command_class::T_STRING;
        $this->types['renderType'] = abstract_command_class::T_STRING;
        $this->types['specificRender'] = abstract_command_class::T_STRING;

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
        $action = new ProjectRendererAction(str_replace("_", ":", $this->params['id']));
        $content = $action->init();
        $projectId = $action->getProjectID();
        return array('projectId' => $projectId, 'content' => $content);
    }

    protected function getDefaultResponse($response, &$ret) {
        if ($response) {
            $response['projectType'] = $this->params['projectType'];
            $response['renderType'] = $this->params['renderType'];
            $response['specificRender'] = $this->params['specificRender'];

            $response[action_plugin_iocexportl::DATA_PAGEID] = $this->params['id'];
            $response[action_plugin_iocexportl::DATA_IOCLANGUAGE] = $this->params['ioclanguage'];
            $response[action_plugin_iocexportl::DATA_IS_ZIP_RADIO_CHECKED] = $this->params['mode']==="zip";
            $meta = action_plugin_iocexportl::getform_html_from_data($response);

            $pageId = $this->params['id'];
            $ret->addExtraMetadata($pageId, $pageId."_iocexportxhtml", WikiIocLangManager::getLang("metadata_export_title"),$meta);
        }else {
            $ret->addError(1000, "EXPORTACIÓ NO REALITZADA");
        }
    }

    public function getAuthorizationType() {
        return "save";
    }
}
