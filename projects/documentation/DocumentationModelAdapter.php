<?php
if (!defined('DOKU_INC')) die();
if (!defined('DOKU_PROJECTS')) define('DOKU_PROJECT', DOKU_PLUGIN . 'lib/plugins/wikiiocmodel/projects/');

require_once DOKU_PROJECTS . "defaultProject/DokuModelAdapter.php";
require_once DOKU_PROJECTS . "documentation/actions/SetProjectMetaDataAction.php";

class DocumentationModelAdapter extends DokuModelAdapter {

    public function getProjectMetaData($params) {
        $action = new GetProjectMetaDataAction($this->persistenceEngine);
        return $action->get($params);
    }

    public function setProjectMetaData($params) {
        $action = new SetProjectMetaDataAction($this->persistenceEngine);
        return $action->get($params);
    }
}
