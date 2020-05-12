<?php
if (!defined('DOKU_INC')) die();

class GenerateProjectMetaDataAction extends BasicGenerateProjectMetaDataAction{

    function responseProcess() {
        $response = parent::responseProcess();
        $response["sendData"] = $response[ProjectKeys::KEY_GENERATED];
        $response[AjaxKeys::KEY_FTPSEND_HTML] = $this->getModel()->get_ftpsend_metadata();
        return $response;
    }

}
