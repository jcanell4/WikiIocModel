<?php
if (!defined('DOKU_INC')) die();

class CreateProjectMetaDataAction extends BasicCreateProjectMetaDataAction {

     protected function getDefaultValues(){
        $metaDataValues = parent::getDefaultValues();
        $metaDataValues['nsproject'] = $this->params[ProjectKeys::KEY_ID];
        return $metaDataValues;
     }
}