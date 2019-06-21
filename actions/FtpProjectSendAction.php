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

        // Obtenim la configuració i la passem al FtpSender // TODO
        $projectType = $this->getModel()->getProjectType();

        $connectionData = [
            'sendftp_host' => WikiGlobalConfig::getConf('sendftp_host', 'wikiiocmodel', $projectType),
            'sendftp_port' => WikiGlobalConfig::getConf('sendftp_port', 'wikiiocmodel', $projectType),
            'sendftp_u' => WikiGlobalConfig::getConf('sendftp_u', 'wikiiocmodel', $projectType),
            'sendftp_p' => WikiGlobalConfig::getConf('sendftp_p', 'wikiiocmodel', $projectType)
        ];

        $this->ftpSender->setConnectionData($connectionData);

        // ALERTA[Xavi] perque ho pasem independentment en lloc de configurar-lo? Ho hagafem del project:config?
        $remoteBase = WikiGlobalConfig::getConf('sendftp_remotebase', 'wikiiocmodel', $projectType);

        if ($filesToSend) {
            foreach ($filesToSend as $afile) {
//                $this->ftpSender->addObjectToSendList($afile['file'], $afile['local'], $afile['remoteBase'], $afile['remoteDir'], $afile['action']);
                $this->ftpSender->addObjectToSendList($afile['file'], $afile['local'], $remoteBase, $afile['remoteDir'], $afile['action']);
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
