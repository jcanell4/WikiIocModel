<?php

/**
 * Description of FtpSendAction
 *
 * @author professor
 */

if (!defined("DOKU_INC")) die();
require_once DOKU_INC . "lib/lib_ioc/wikiiocmodel/FtpSender.php";

class FtpSendAction extends DokuAction{
    private $ftpSender;
    private $ftpResponse;
    private $response;
    
    public function __construct($params = NULL) {
        parent::__construct($params);
        $this->ftpSender  = new FtpSender();
    }

    protected function startProcess() {
        //afegir a la llista d'objectes a enviar quins fitxers i sota quins paràmetres caldrà fer-ho
    }
    
    public function addObjectToSendList($file, $remoteBase, $remoteDir, $action=0){
        $this->ftpSender->addObjectToSendList($file, $remoteBase, $remoteDir, $action);
    }
    
    protected function responseProcess() {
        //tractar $this->response amb la resposta emmagatzemada a $this->ftpResponse;
        return $this->response;
    }

    protected function runProcess() {
        $this->ftpResponse = $this->ftpSender->process();
    }
}
