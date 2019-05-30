<?php
/**
 * Description of FtpSendAction
 */
if (!defined("DOKU_INC")) die();
require_once DOKU_INC . "lib/lib_ioc/wikiiocmodel/FtpSender.php";

class FtpSendAction extends DokuAction{
    private $dokuPageModel;
    private $ftpSender;
    private $ftpResponse;

    public function __construct($params = NULL) {
        parent::__construct($params);
        $this->ftpSender = new FtpSender();
    }

    public function init($modelManager) {
        parent::init($modelManager);
        $this->dokuPageModel = new DokuPageModel($modelManager->getPersistenceEngine());
    }
    
    protected function getModel() {
        return $this->dokuPageModel;
    }

    protected function startProcess() {
        $reCodi = '/\* \*\*creditcodi\*\*.*?:(.*)\/.*\n/m';
        $reCC = '/\* \*\*copylink\*\*.*?:.*http:\/\/creativecommons.*\n/m';
        $this->getModel()->init($this->params[ProjectKeys::KEY_ID]);
        $pageStruct = $this->getModel()->getRawData();
        
        preg_match($reCodi, $pageStruct['content'], $matches);
        
        $docCode = trim($matches[1]);
        $isProtected = preg_match($reCC, $pageStruct['content'])!=1;
        if($isProtected){
            $docCode .= "_protected";
        }
        $remoteFilename ="web";
        
        //afegir a la llista d'objectes a enviar quins fitxers i sota quins paràmetres caldrà fer-ho
        $filename = str_replace(':', '_', $this->params[ProjectKeys::KEY_ID]).".zip";
        $dest = str_replace(':', '/', $this->params[ProjectKeys::KEY_ID]);
        $dest = dirname($dest)."/";
        $local = WikiGlobalConfig::getConf('mediadir')."/".$dest;

        $this->ftpSender->addObjectToSendList($filename, $local, "remoteBase/", "$docCode/", [0], "$remoteFilename.zip");
        $this->ftpSender->addObjectToSendList($filename, $local, "remoteBase/", "$docCode/$remoteFilename/", [1]);
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
