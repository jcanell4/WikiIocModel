<?php
/**
 * FtpProjectSendAction en el proyecto 'guiesges'
 */
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . "lib/plugins/");
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_PLUGIN . "wikiiocmodel/");
include_once WIKI_IOC_MODEL . "actions/BasicFtpProjectSendAction.php";

class FtpProjectSendAction extends BasicFtpProjectSendAction{

    protected function responseProcess() {
        $this->getModel()->set_ftpsend_metadata();
        $response = parent::responseProcess();
        $response[ProjectKeys::KEY_FTPSEND_HTML] = $this->getModel()->get_ftpsend_metadata();
        return $response;
    }

}
