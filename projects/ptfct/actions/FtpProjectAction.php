<?php
/**
 * Description of FtpProjectAction en el projecte 'ptfct'
 */
if (!defined("DOKU_INC")) die();

class FtpProjectAction extends BasicFtpProjectAction{

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
