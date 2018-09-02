<?php
if (!defined('DOKU_INC')) die();
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_INC."lib/plugins/wikiiocmodel/");
include_once WIKI_IOC_MODEL."actions/ProjectMetadataAction.php";

class CreateProjectMetaDataAction extends BasicCreateProjectMetaDataAction {

     protected function getDefaultValues(){
        $id = $this->params[ProjectKeys::KEY_ID];
        $metaDataValues = parent::getDefaultValues();
        $metaDataValues['nsproject'] = $id;
        $metaDataValues['fitxercontinguts'] = $id.":".array_pop(explode(":", $metaDataValues['plantilla']));
        return $metaDataValues;
     }
}