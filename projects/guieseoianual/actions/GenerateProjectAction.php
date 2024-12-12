<?php
defined('DOKU_INC') || die();

class GenerateProjectAction extends BasicGenerateProjectAction{
    function responseProcess() {
        $ret =  parent::responseProcess();
        $ret["sendData"] = $ret[ProjectKeys::KEY_GENERATED];
        $ret[AjaxKeys::KEY_FTPSEND_HTML] = $this->getModel()->get_ftpsend_metadata();
        return $ret;
    }
}