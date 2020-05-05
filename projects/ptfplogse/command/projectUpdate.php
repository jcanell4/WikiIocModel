<?php
/**
 * projectUpdate : command que es dispara pel botó updateDataProject
 * @culpable Rafael Claver
 */
if (!defined('DOKU_INC')) die();

class command_plugin_wikiiocmodel_projects_ptfplogse_projectUpdate extends abstract_project_command_class {

    public function __construct() {
        parent::__construct();
        $this->types[ProjectKeys::KEY_ID] = self::T_STRING;
        $this->types[ProjectKeys::PROJECT_TYPE] = self::T_STRING;
    }

    protected function process() {
        $params = array(ProjectKeys::KEY_ID       => $this->params[ProjectKeys::KEY_ID],
                        ProjectKeys::KEY_NS       => $this->params[ProjectKeys::KEY_ID],
                        ProjectKeys::PROJECT_TYPE => $this->params[ProjectKeys::PROJECT_TYPE],
                        ProjectKeys::KEY_DO       => "view",
                        ProjectKeys::KEY_METADATA_SUBSET => $this->params[ProjectKeys::KEY_METADATA_SUBSET]
                  );
        $action = $this->getModelManager()->getActionInstance("ProjectUpdateDataAction");
        $projectMetaData = $action->get($params);
        if (!$projectMetaData)
            throw new UnknownProjectException();
        $this->_addExtraData($projectMetaData);
        return $projectMetaData;
    }

    public function getAuthorizationType() {
        return "saveProject";
    }

}
