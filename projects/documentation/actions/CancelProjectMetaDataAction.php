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
        return $response;
    }

}