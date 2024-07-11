<?php
/**
 * Description of FtpProjectAction al projecte 'ptce'
 */
if (!defined("DOKU_INC")) die();

class FtpProjectAction extends BasicFtpProjectAction{

    protected function responseProcess() {

        $this->getModel()->set_ftpsend_metadata();
        $response = parent::responseProcess();
        $response[AjaxKeys::KEY_FTPSEND_HTML] = $this->getModel()->get_ftpsend_metadata();

        $action = $this->getActionInstance("ProjectSendMoodleEventsAction");
        $resp = $action->get($this->params);
        $response['info']= IocCommon::addInfoToInfo($resp['info'], $response['info']);
        return $response;

    }
}
