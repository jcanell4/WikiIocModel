<?php
/**
 * Description of BasicFtpSendAction
 */
if (!defined("DOKU_INC")) die();
require_once DOKU_INC . "lib/lib_ioc/wikiiocmodel/FtpSender.php";


class FtpProjectSendAction extends BasicFtpProjectSendAction{


    protected function responseProcess() {

        $this->getModel()->set_ftpsend_metadata();
        $response = parent::responseProcess();
        $response['ftpsend_html'] = $this->getModel()->get_ftpsend_metadata();

        return $response;

    }
}