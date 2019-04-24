<?php
if (!defined('DOKU_INC')) die();

class GenerateProjectMetaDataAction extends BasicGenerateProjectMetaDataAction{
    function responseProcess() {
        $ret =  parent::responseProcess();
        $ret["sendData"] = $ret[ProjectKeys::KEY_GENERATED];
        return $ret;
    }
}