<?php
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
include_once (DOKU_PLUGIN . 'wikiiocmodel/projects/documentation/actions/GetProjectMetaDataAction.php');

class CancelProjectMetaDataAction extends GetProjectMetaDataAction {

    public function responseProcess() {

        if (!$this->params[ProjectKeys::KEY_KEEP_DRAFT]) {
            $this->getModel()->removeDraft(); //Elimina els esborranys
        }

        // ALERTA[Xavi] Comentada perque no fa res i DISCARD_CHANGES no existeix
//        if (!$this->params[ProjectKeys::DISCARD_CHANGES]) {
//            //Descarta els canvis
//        }

        if ($this->params[ProjectKeys::KEY_NO_RESPONSE] ) {
            $response[ProjectKeys::KEY_CODETYPE] = 0;
            return $response;
        }

        //eSTO NO DEBE SUCEDER
//        if (isset($this->params[ProjectKeys::KEY_REV])) {
//            $response[ProjectKeys::KEY_ID] .= ProjectKeys::REVISION_SUFFIX;
//            if ($response['meta']) {
//                // Corregim els ids de les metas per indicar que és una revisió
//                $this->addRevisionSuffixIdToArray($response['meta']);
//            }
//        }

        $response = parent::responseProcess();
        return $response;
    }

}