<?php
if (!defined('DOKU_INC')) die();
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_INC . "lib/plugins/wikiiocmodel/");
if (!defined('DOKU_IOC_MODEL')) define('DOKU_IOC_MODEL', DOKU_INC . "lib/plugins/wikiiocmodel/projects/defaultProject/");

require_once(DOKU_IOC_MODEL . 'DokuModelAdapter.php');
require_once(DOKU_IOC_MODEL . 'DokuModelExceptions.php');

class TestmatModelAdapter extends DokuModelAdapter {
    // TODO [XAVI] crear una funció que generi el formulai
    public function getProjectMetaData($params) {

        $action = new ProjectMetaDataAction($this->persistenceEngine);
        return $action->get($params);

    }

}