<?php
if (!defined('DOKU_INC')) die();

class CancelProjectMetaDataAction extends ViewProjectMetaDataAction {

    protected function runAction() {
        $response = BasicCancelProjectMetaDataAction::sharedRunAction($this);

        if ($this->params[ProjectKeys::KEY_NO_RESPONSE] ) {
            $response[ProjectKeys::KEY_CODETYPE] = ProjectKeys::VAL_CODETYPE_OK;
        }else {
            $response = parent::runAction();
            $exportOk = $this->getModel()->validateProjectDates()? 1 : 0;
            $response[AjaxKeys::KEY_EXTRA_STATE] = [AjaxKeys::KEY_EXTRA_STATE_ID => "exportOk", AjaxKeys::KEY_EXTRA_STATE_VALUE => $exportOk];
        }        
        
        return $response;
    }

}
