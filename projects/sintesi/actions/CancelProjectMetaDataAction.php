<?php
if (!defined('DOKU_INC')) die();
//if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . "lib/plugins/");
//if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_PLUGIN . "wikiiocmodel/");
//include_once WIKI_IOC_MODEL . "projects/sintesi/actions/ViewProjectMetaDataAction.php";
include_once dirname(__FILE__) . "/ViewProjectMetaDataAction.php";

class CancelProjectMetaDataAction extends ViewProjectMetaDataAction {

    protected function runAction() {
        $response = BasicCancelProjectMetaDataAction::sharedRunAction($this);

        if ($this->params[ProjectKeys::KEY_NO_RESPONSE] ) {
            $response[ProjectKeys::KEY_CODETYPE] = ProjectKeys::VAL_CODETYPE_OK;
        }else {
            $response = parent::runAction();
        }

        return $response;
    }

}
