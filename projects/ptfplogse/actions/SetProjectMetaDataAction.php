<?php
if (!defined('DOKU_INC')) die();

//Cal elininar aquest fitxer, ja no serveix
class SetProjectMetaDataAction extends BasicNotGenerableProjectMetaDataAction {

    protected function responseProcess(){
        $response = parent::responseProcess();
        return $response;
    }
    
}