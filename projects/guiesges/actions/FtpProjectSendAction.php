<?php
/**
 * FtpProjectSendAction en el proyecto 'guiesges'
 */
if (!defined("DOKU_INC")) die();

class FtpProjectSendAction extends BasicFtpProjectSendAction{

    protected function responseProcess() {
        $this->getModel()->set_ftpsend_metadata();
        $response = parent::responseProcess();
        $response[ProjectKeys::KEY_FTPSEND_HTML] = $this->getModel()->get_ftpsend_metadata();
        return $response;
    }

}
