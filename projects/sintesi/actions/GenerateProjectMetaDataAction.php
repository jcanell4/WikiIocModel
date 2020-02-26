<?php
if (!defined('DOKU_INC')) die();

class GenerateProjectMetaDataAction extends BasicGenerateProjectMetaDataAction {
    
    function responseProcess() {
        $ret =  parent::responseProcess();
        $ret["sendData"] = $ret[ProjectKeys::KEY_GENERATED];
        $ret[ProjectKeys::KEY_FTPSEND_HTML] = $this->getModel()->get_ftpsend_metadata();
        return $ret;
    }
}