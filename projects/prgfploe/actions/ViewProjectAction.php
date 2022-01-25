<?php
if (!defined('DOKU_INC')) die();

class ViewProjectAction extends BasicViewProjectAction{

    public function runAction()
    {
        if ($this->params[ProjectKeys::KEY_METADATA_SUBSET] === "management") {
            $this->getModel()->setViewConfigName("#blank#");
        }

        $ret = parent::runAction();

        return $ret;
    }


    public function responseProcess() {
        $response = parent::responseProcess();
        $response[AjaxKeys::KEY_FTPSEND_HTML] = $this->getModel()->get_ftpsend_metadata();
        return $response;
    }

}
