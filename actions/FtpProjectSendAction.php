<?php
/**
 * Description of BasicFtpSendAction
 */
if (!defined("DOKU_INC")) die();
require_once DOKU_INC . "lib/lib_ioc/wikiiocmodel/FtpSender.php";

class FtpProjectSendAction extends ProjectMetadataAction{

    private $ftpSender;

    public function __construct($params = NULL) {
        parent::__construct($params);
        $this->ftpSender = new FtpSender();
    }

    private function addFilesToSend() {
        // $filesToSend es un array de n arrays con el formato ['file', 'local', 'action', 'remoteBase', 'remoteDir']
        $filesToSend = $this->getModel()->filesToExportList(); //crear la funció filesToExportList a cada projectModel amb les dades a tractar
        if ($filesToSend) {
            foreach ($filesToSend as $afile) {
                $this->ftpSender->addObjectToSendList($afile['file'], $afile['local'], $afile['remoteBase'], $afile['remoteDir'], $afile['action']);
            }
        }
    }

    protected function responseProcess() {
        $this->addFilesToSend();
        $ftpResponse = $this->ftpSender->process();

        $id = $this->params[ProjectKeys::KEY_ID];
        if ($ftpResponse) {
            $response['info'] = $this->generateInfo("info", WikiIocLangManager::getLang('ftp_send_success')." ($id)", $id);
        }else {
            $response['info'] = $this->generateInfo("error", WikiIocLangManager::getLang('ftp_send_error')." ($id)", $id);
            $response['alert'] = WikiIocLangManager::getLang('ftp_send_error')." ($id)";
        }

        return $response;
    }
}
