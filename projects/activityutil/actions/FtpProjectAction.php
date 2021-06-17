<?php
/**
 * FtpProjectAction en el projecte 'activityutil'
 */
if (!defined("DOKU_INC")) die();

class FtpProjectAction extends BasicFtpProjectAction {

    protected function responseProcess() {
        $model = $this->getModel();
        //Guarda a system la data del fitxer que s'envia a FTP
        $model->set_ftpsend_metadata();

        $response = parent::responseProcess();
        $response[AjaxKeys::KEY_FTPSEND_HTML] = $model->get_ftpsend_metadata();
        return $response;
    }
    
}
