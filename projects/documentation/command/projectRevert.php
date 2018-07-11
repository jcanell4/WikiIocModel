<?php
/**
 * projectRevert: command del botó REVERTIR un projecte a una versió anterior
 * @culpable Rafael Claver
 */
if (!defined('DOKU_INC')) die();

class command_plugin_wikiiocmodel_projects_documentation_projectRevert extends abstract_project_command_class {

    public function __construct() {
        parent::__construct();
        $this->types[ProjectKeys::PROJECT_TYPE] = self::T_STRING;
        $this->types[ProjectKeys::KEY_MODE] = self::T_STRING;
        $this->types[ProjectKeys::KEY_RENDER_TYPE] = self::T_STRING; //CLAVE NO UTILIZADA
    }

    protected function process() {
        $action = $this->getModelManager()->getActionInstance("RevertProjectMetaDataAction");
        $projectMetaData = $action->get($this->params);
        if (!$projectMetaData) throw new UnknownProjectException();
        return $projectMetaData;
    }

}
