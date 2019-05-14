<?php

/**
 * Description of BasicFtpSendAction
 *
 * @author professor
 */
if (!defined("DOKU_INC")) die();
require_once DOKU_INC . "lib/lib_ioc/wikiiocmodel/FtpSender.php";

class FtpProjectSendAction extends ProjectMetadataAction{
    private $ftpSender;
    
    public function __construct($params = NULL) {
        parent::__construct($params);
        $this->ftpSender  = new FtpSender();
    }
    
    private function addFilesToSend(){
        $filesToSend = $this->getModel()->filesToExportList(); //crear la funció filesToExportList a cada projectModel amb les dades atrcatr
        if($filesToSend){
            //Per cada item fer
            //      $this->ftpSender->addObjectToSendList($item[file], $item[remoteBase], $item[remoteDir], $item[action]);
        }
    }
    
    protected function responseProcess() {
        $response;
        
        $this->addFilesToSend();
        
        $ftpResponse = $this->ftpSender->process();
        //tractar $response per tal d'informar a l'usuri com ha anat la connexió;
        
        return $response;
    }
}
