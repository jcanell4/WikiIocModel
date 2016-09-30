<?php

if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
require_once DOKU_PLUGIN . "wikiiocmodel/actions/AbstractWikiAction.php";
require_once DOKU_PLUGIN . "wikiiocmodel/projects/documentation/datamodel/ProjectModel.php";
require_once DOKU_PLUGIN . "ajaxcommand/requestparams/ProjectKeys.php";

class GetProjectMetaDataAction extends AbstractWikiAction {

    protected $projectModel;
    protected $persistenceEngine;

    public function __construct($persistenceEngine) {
        $this->persistenceEngine = $persistenceEngine;
        $this->projectModel = new ProjectModel($persistenceEngine);
    }

    public function get($paramsArr = array()) {
        $this->projectModel->init($paramsArr[ProjectKeys::KEY_ID], $paramsArr[ProjectKeys::KEY_PROJECT_TYPE]);
        return $this->projectModel->getData();
    }
}