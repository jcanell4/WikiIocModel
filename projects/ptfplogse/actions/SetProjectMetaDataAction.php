<?php
if (!defined('DOKU_INC')) die();

class SetProjectMetaDataAction extends BasicNotGenerableProjectMetaDataAction {

    protected function responseProcess(){
        $response = parent::responseProcess();
        return $response;
    }
    
}