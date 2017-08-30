<?php
/**
 * renderer : command que es dispara pels botons que generen HTML i PDF
 * @culpable Rafael Claver
 */
if (!defined('DOKU_INC')) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
if (!defined('DOKU_COMMAND')) define('DOKU_COMMAND', DOKU_PLUGIN . "ajaxcommand/");

require_once(DOKU_COMMAND . 'AjaxCmdResponseGenerator.php');
require_once(DOKU_COMMAND . 'JsonGenerator.php');
require_once(DOKU_COMMAND . 'abstract_command_class.php');

class command_plugin_wikiiocmodel_projects_documentation_renderer extends abstract_command_class {

    public function __construct() {
        parent::__construct();
        $this->types['id'] = abstract_command_class::T_STRING;
        $this->types['projectType'] = abstract_command_class::T_STRING;
        $this->types['renderType'] = abstract_command_class::T_STRING;
        $this->types['specificRender'] = abstract_command_class::T_STRING;

        $defaultValues = ['do' => 'wikiiocmodel_projects_documentation_renderer'];
        $this->setParameters($defaultValues);
    }

    protected function process() {
        $contentData = $this->modelWrapper->getCodePage($this->params);
        return $contentData;
    }

    protected function getDefaultResponse($response, &$ret) {
        if ($response) {
            $response['id'] = $this->params['id'];
            $response['projectType'] = $this->params['projectType'];
            $response['renderType'] = $this->params['renderType'];
            $response['specificRender'] = $this->params['specificRender'];
            $meta = action_plugin_iocexportl::getform_html_from_data($response);
            $pageId = str_replace( ":", "_", $this->params['id'] );
            $ret->addExtraMetadata($pageId,
                    array(
                        "id" => $pageId."_iocexportl",
                        "content" => $meta)
                    );
        }else {
            $ret->addError(1000, "EXPORTACIÓ NO REALITZADA");
        }
    }
}
