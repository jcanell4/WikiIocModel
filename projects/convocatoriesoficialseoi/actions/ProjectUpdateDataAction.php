<?php
if (!defined('DOKU_INC')) die();

class ProjectUpdateDataAction extends ViewProjectMetaDataAction {
    protected function runAction() {

        $response = parent::runAction();

        $projectModel = $this->getModel();
        $projectModel->setTemplateDocuments($response['projectMetaData']['plantilla']['value'], 'update templates');
        $response[ProjectKeys::KEY_ACTIVA_UPDATE_BTN] = !$projectModel->validateTemplates()? 1 : 0;

        return $response;
    }

}