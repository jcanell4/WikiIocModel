<?php
if (!defined('DOKU_INC')) die();

class CreateProjectAction extends BasicCreateProjectAction {

     protected function getDefaultValues(){
        $metaDataValues = parent::getDefaultValues();
        $metaDataValues['nsproject'] = $this->params[ProjectKeys::KEY_ID];
        return $metaDataValues;
     }
}