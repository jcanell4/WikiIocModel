<?php
/**
 * renderer : command que es dispara pels botons que generen HTML i PDF
 * @culpable Rafael Claver
 */
if (!defined('DOKU_INC')) die();

class command_plugin_wikiiocmodel_projects_documentation_renderer extends abstract_project_command_class{

    public function __construct() {
        parent::__construct();
        $this->types[AjaxKeys::PROJECT_TYPE] = self::T_STRING;
        $this->types['renderType'] = self::T_STRING;
        $this->types['specificRender'] = self::T_STRING;

        $defaultValues = [AjaxKeys::KEY_DO => 'wikiiocmodel_projects_documentation_renderer'];
        $this->setParameters($defaultValues);
    }

    protected function process() {
        $contentData = array();
        return $contentData;
    }

    protected function getDefaultResponse($response, &$ret) {
        if ($response) {
            $response[AjaxKeys::KEY_ID] = $this->params[AjaxKeys::KEY_ID];
            $response[AjaxKeys::PROJECT_TYPE] = $this->params[AjaxKeys::PROJECT_TYPE];
            $response['renderType'] = $this->params['renderType'];
            $response['specificRender'] = $this->params['specificRender'];
            $meta = action_plugin_iocexportl::getform_html_from_data($response);
            $pageId = str_replace( ":", "_", $this->params['id'] );
            $ret->addExtraMetadata($pageId,
                    array(
                        AjaxKeys::KEY_ID => $pageId."_iocexportl",
                        "content" => $meta)
                    );
        }else {
            $ret->addError(1000, "EXPORTACIÓ NO REALITZADA");
        }
    }
}
