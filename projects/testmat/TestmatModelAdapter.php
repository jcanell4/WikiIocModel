<?php
if (!defined('DOKU_INC')) die();
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_INC . "lib/plugins/wikiiocmodel/");
if (!defined('DOKU_IOC_MODEL')) define('DOKU_IOC_MODEL', DOKU_INC . "lib/plugins/wikiiocmodel/projects/defaultProject/");

require_once(DOKU_IOC_MODEL . 'DokuModelAdapter.php');
require_once(DOKU_IOC_MODEL . 'DokuModelExceptions.php');

class TestmatModelAdapter extends DokuModelAdapter {

    public function getProjectMetaData($params) {

        $action = new GetProjectMetaDataAction($this->persistenceEngine);
        return $action->get($params);

    }

    public function setProjectMetaData($params) {

        $action = new SetProjectMetaDataAction($this->persistenceEngine);
        return $action->get($params);

    }

}
