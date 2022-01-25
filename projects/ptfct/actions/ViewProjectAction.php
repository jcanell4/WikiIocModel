<?php
if (!defined('DOKU_INC')) die();

class ViewProjectAction extends BasicViewUpdatableProjectAction{

    protected function runAction() {
        if (!$this->getModel()->isProjectGenerated()) {
            $this->getModel()->setViewConfigKey(ProjectKeys::KEY_VIEW_FIRSTVIEW);
        }
        $response = parent::runAction();
        return $response;
    }

    public function responseProcess() {
        $response = parent::responseProcess();
        $response[AjaxKeys::KEY_FTPSEND_HTML] = $this->getModel()->get_ftpsend_metadata();
        return $response;
    }

}
