<?php
/**
 * Description of FtpProjectSendAction
 */
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_LIB_IOC')) define('DOKU_LIB_IOC', DOKU_INC."lib/lib_ioc/");
require_once DOKU_LIB_IOC . "wikiiocmodel/FtpSender.php";

class FtpProjectSendAction extends BasicFtpProjectSendAction{

    protected function responseProcess() {
        $this->getModel()->set_ftpsend_metadata();
        $response = parent::responseProcess();
        $response[ProjectKeys::KEY_FTPSEND_HTML] = $this->getModel()->get_ftpsend_metadata();

        $action = $this->getActionInstance("ProjectSendMoodleEventsAction");
        $resp = $action->get($this->params);
        $response['info']= IocCommon::addInfoToInfo($resp['info'], $response['info']);

        return $response;
    }
}
