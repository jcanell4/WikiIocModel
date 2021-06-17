<?php
if (!defined('DOKU_INC')) die();

class ViewProjectAction extends BasicViewProjectAction{

    public function responseProcess() {
        $response = parent::responseProcess();
        $response['generatedZipFiles'] = $this->getModel()->llistaDeEspaiDeNomsDeDocumentsDelProjecte();
        return $response;
    }

}
