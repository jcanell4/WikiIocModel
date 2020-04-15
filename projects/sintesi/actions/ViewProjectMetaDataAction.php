<?php
if (!defined('DOKU_INC')) die();

class ViewProjectMetaDataAction extends BasicViewUpdatableProjectMetaDataAction{

    protected function runAction() {

        if (!$this->getModel()->isProjectGenerated()) {
            $this->getModel()->setViewConfigName("firstView");
        }
        $response = parent::runAction();

        return $response;
    }

    public function responseProcess() {

        $response = parent::responseProcess();
        $response[ProjectKeys::KEY_FTPSEND_HTML] = $this->getModel()->get_ftpsend_metadata();

        return $response;
    }

}
