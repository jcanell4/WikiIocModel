<?php
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
include_once (DOKU_PLUGIN . 'wikiiocmodel/projects/documentation/actions/GetProjectMetaDataAction.php');

class CancelProjectMetaDataAction extends GetProjectMetaDataAction {

    public function responseProcess() {

        if (!$this->params[ProjectKeys::KEY_KEEP_DRAFT]) {
            $this->getModel()->removeDraft(); //Elimina els esborranys
        }

        if ($this->params[ProjectKeys::KEY_NO_RESPONSE] ) {
            $response[ProjectKeys::KEY_CODETYPE] = 0;
            return $response;
        }

        $response = parent::responseProcess();
        //Eliminamos los parámetros que NO son datos del formulario  del proyecto
        unset($response['projectMetaData']['values']['cancel']);
        unset($response['projectMetaData']['values']['close']);
        unset($response['projectMetaData']['values']['keep_draft']);
        unset($response['projectMetaData']['values']['no_response']);
        unset($response['projectMetaData']['structure']['cancel']);
        unset($response['projectMetaData']['structure']['close']);
        unset($response['projectMetaData']['structure']['keep_draft']);
        unset($response['projectMetaData']['structure']['no_response']);

        $response['info'] = $this->generateInfo("info", WikiIocLangManager::getLang('project_canceled'), $this->params[ProjectKeys::KEY_ID]);

        return $response;
    }

}