<?php
if (!defined('DOKU_INC')) die();

//Cal elininar aquest fitxer, ja no serveix
class SetProjectAction extends BasicNotGenerableProjectAction {

    protected function responseProcess(){
        $response = parent::responseProcess();
        return $response;
    }

}