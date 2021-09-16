<?php
if (!defined('DOKU_INC')) die();

class ViewProjectAction extends BasicViewProjectAction{

    public function responseProcess() {
        $response = parent::responseProcess();
        $model = $this->getModel();
        $response['generatedZipFiles'] = $model->llistaDeEspaiDeNomsDeDocumentsDelProjecte();
        $response[AjaxKeys::KEY_FTPSEND_HTML] = $model->get_ftpsend_metadata();
        return $response;
    }

}
