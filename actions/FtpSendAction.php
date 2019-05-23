<?php
/**
 * Description of FtpSendAction
 */
if (!defined("DOKU_INC")) die();
require_once DOKU_INC . "lib/lib_ioc/wikiiocmodel/FtpSender.php";

class FtpSendAction extends DokuAction{

    private $ftpSender;
    private $ftpResponse;

    public function __construct($params = NULL) {
        parent::__construct($params);
        $this->ftpSender = new FtpSender();
    }

    protected function startProcess() {
        //afegir a la llista d'objectes a enviar quins fitxers i sota quins paràmetres caldrà fer-ho
        $filename = str_replace(':', '_', $this->params[ProjectKeys::KEY_ID]).".zip";
        $dest = str_replace(':', '/', $this->params[ProjectKeys::KEY_ID]);
        $dest = dirname($dest)."/";
        $local = WikiGlobalConfig::getConf('mediadir')."/".$dest;

        $this->ftpSender->addObjectToSendList($filename, $local, "remoteBase/", $dest, [0,1]);
    }

    protected function responseProcess() {
        $id = $this->params[ProjectKeys::KEY_ID];
        if ($this->response) {
            $response['info'] = $this->generateInfo("info", WikiIocLangManager::getLang('ftp_send_success')." ($id)", $id);
        }else {
            $response['info'] = $this->generateInfo("error", WikiIocLangManager::getLang('ftp_send_error')." ($id)", $id);
            $response['alert'] = WikiIocLangManager::getLang('ftp_send_error')." ($id)";
        }
        return $response;
    }

    protected function runProcess() {
        $this->ftpResponse = $this->ftpSender->process();
    }
}
