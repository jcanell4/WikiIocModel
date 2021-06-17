<?php
if (!defined('DOKU_INC')) die();

class ViewProjectAction extends BasicViewProjectAction{

    public function responseProcess() {
        $response = parent::responseProcess();
        $projectModel = $this->getModel();
        $response['generatedZipFiles'] = $projectModel->llistaDeEspaiDeNomsDeDocumentsDelProjecte();
        $response[AjaxKeys::KEY_ACTIVA_FTP_PROJECT_BTN] = $projectModel->haveFilesToExportList();
        return $response;
    }

}
