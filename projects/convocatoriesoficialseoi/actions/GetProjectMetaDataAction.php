<?php
if (!defined('DOKU_INC')) die();
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_INC."lib/plugins/wikiiocmodel/");
include_once WIKI_IOC_MODEL."actions/ProjectMetadataAction.php";

class GetProjectMetaDataAction extends BasicGetProjectMetaDataAction{
    function runAction() {
        if (!$this->getModel()->isProjectGenerated()) {
            $this->getModel()->setViewConfigName("firstView");
        }           
        $response = parent::runAction();

        return $response;
    }
}