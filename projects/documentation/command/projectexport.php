<?php
/**
 * projectexport : command que es dispara pels botons que generen HTML i PDF
 * @culpable Rafael Claver
 */
if (!defined('DOKU_INC')) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
require_once(DOKU_PLUGIN . 'iocexportl/action.php');

class command_plugin_wikiiocmodel_projects_documentation_projectexport extends abstract_command_class {

    public function __construct() {
        parent::__construct();
        $this->types[AjaxKeys::KEY_ID] = self::T_STRING;
        $this->types[AjaxKeys::PROJECT_TYPE] = self::T_STRING;
        $this->types['mode'] = self::T_STRING;
        $this->types['renderType'] = self::T_STRING;
    }

    public function init( $modelManager=NULL ) {
        global $plugin_controller;
        $plugin_controller->setCurrentProject($this->params[AjaxKeys::PROJECT_TYPE]);
        if (!$modelManager) {
            $modelManager = WikiIocModelManager::Instance($this->params[AjaxKeys::PROJECT_TYPE]);
        }
        $this->setModelManager($modelManager);
    }

    protected function process() {
        $params = array(AjaxKeys::KEY_ID       => $this->params[AjaxKeys::KEY_ID],
                        AjaxKeys::KEY_NS       => str_replace("_", ":", $this->params[AjaxKeys::KEY_ID]),
                        AjaxKeys::PROJECT_TYPE => $this->params[AjaxKeys::PROJECT_TYPE],
                        "mode"     => $this->params['mode'],
                        "filetype" => $this->params['filetype']
                  );
        $action = $this->modelManager->getActionInstance("ProjectExportAction", $this->modelManager->getExporterManager());
        $action->init($params);
        $content = $action->get();
        $projectId = $action->getProjectID();
        return array('projectId' => $projectId, 'meta' => $content);
    }

    protected function getDefaultResponse($response, &$ret) {
        if ($response) {
            $response[AjaxKeys::PROJECT_TYPE] = $this->params[AjaxKeys::PROJECT_TYPE];
            $meta = $response["meta"];
            $pageId = $this->params[AjaxKeys::KEY_ID];
            $ret->addExtraMetadata($pageId, $pageId."_iocexport", WikiIocLangManager::getLang("metadata_export_title"), $meta);
        }else {
            $ret->addError(1000, "EXPORTACIÓ NO REALITZADA");
        }
    }

    public function getAuthorizationType() {
        return "save";
    }
}
