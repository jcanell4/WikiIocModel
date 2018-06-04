<?php
/**
 * projectRevert: command del botó REVERTIR un projecte a una versió anterior
 * @culpable Rafael Claver
 */
if (!defined('DOKU_INC')) die();

class command_plugin_wikiiocmodel_projects_iocdocum_projectRevert extends abstract_project_command_class {

    public function __construct() {
        parent::__construct();
        $this->types[ProjectKeys::KEY_ID] = self::T_STRING;
        $this->types[ProjectKeys::PROJECT_TYPE] = self::T_STRING;
        $this->types['mode'] = self::T_STRING;
        $this->types['renderType'] = self::T_STRING;
    }

    protected function process() {
        $action = $this->getModelManager()->getActionInstance("RevertProjectMetaDataAction");
        $projectMetaData = $action->get($this->params);
        if (!$projectMetaData) throw new UnknownProjectException();
        return $projectMetaData;
    }

    protected function getDefaultResponse($response, &$ret) {}

}
