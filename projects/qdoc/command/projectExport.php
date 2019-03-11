<?php
/**
 * projectExport : command que es dispara pels botons que generen HTML i PDF
 * @culpable Rafael Claver
 */
if (!defined('DOKU_INC')) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
require_once(DOKU_PLUGIN . 'iocexportl/action.php');

class command_plugin_wikiiocmodel_projects_qdoc_projectExport extends abstract_project_command_class {

    public function __construct() {
        parent::__construct();
        $this->types[ProjectKeys::KEY_ID] = self::T_STRING;
        $this->types[ProjectKeys::PROJECT_TYPE] = self::T_STRING;
        $this->types[ProjectKeys::KEY_MODE] = self::T_STRING;
        $this->types[ProjectKeys::KEY_RENDER_TYPE] = self::T_STRING;
    }

    protected function process() {
        $params = array(ProjectKeys::KEY_ID        => $this->params[ProjectKeys::KEY_ID],
                        ProjectKeys::PROJECT_TYPE  => $this->params[ProjectKeys::PROJECT_TYPE],
                        ProjectKeys::KEY_MODE      => $this->params[ProjectKeys::KEY_MODE],
                        ProjectKeys::KEY_FILE_TYPE => $this->params[ProjectKeys::KEY_FILE_TYPE],
                        ProjectKeys::KEY_METADATA_SUBSET => $this->params[ProjectKeys::KEY_METADATA_SUBSET]
                  );
        $modelManager = $this->getModelManager();
        $action = $modelManager->getActionInstance("ProjectExportAction", $modelManager->getExporterManager());
        $content = $action->get($params);
        return $content;
    }

    protected function getDefaultResponse($response, &$ret) {
        if ($response) {
            $response[ProjectKeys::PROJECT_TYPE] = $this->params[ProjectKeys::PROJECT_TYPE];
            $title = WikiIocLangManager::getLang("metadata_export_title");
            $pageId = $response[ProjectKeys::KEY_ID];
            $ret->addExtraMetadata($pageId, $pageId."_iocexport", $title, $response["meta"]);
        }else {
            $ret->addError(1000, "EXPORTACIÓ NO REALITZADA");
        }
    }

    public function getAuthorizationType() {
        return "editProject";
    }

     public function isEmptyText() {
         return FALSE;
     }
}
