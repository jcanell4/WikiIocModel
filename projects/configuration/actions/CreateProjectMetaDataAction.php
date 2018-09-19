<?php
if (!defined('DOKU_INC')) die();
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_INC."lib/plugins/wikiiocmodel/");
include_once WIKI_IOC_MODEL."actions/ProjectMetadataAction.php";

class CreateProjectMetaDataAction extends BasicCreateProjectMetaDataAction {

     protected function getDefaultValues(){
        $metaDataValues = parent::getDefaultValues();
        $metaDataValues['nsproject'] = $this->params[ProjectKeys::KEY_ID];
        return $metaDataValues;
     }
}