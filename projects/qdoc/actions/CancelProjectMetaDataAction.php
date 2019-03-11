<?php
if (!defined('DOKU_INC')) die();

class CancelProjectMetaDataAction extends ViewProjectMetaDataAction {

    protected function runAction() {
        $response = BasicCancelProjectMetaDataAction::sharedRunAction($this);

        if ($this->params[ProjectKeys::KEY_NO_RESPONSE] ) {
            $response[ProjectKeys::KEY_CODETYPE] = 0;
        }else {
            $response = parent::runAction();
        }

        return $response;
    }

}
