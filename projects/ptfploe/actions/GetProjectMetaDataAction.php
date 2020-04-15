<?php
if (!defined('DOKU_INC')) die();

class GetProjectMetaDataAction extends BasicGetProjectMetaDataAction {

    function runAction() {
        if (!$this->getModel()->isProjectGenerated()) {
            $this->getModel()->setViewConfigName("firstView");
        }
        return parent::runAction();
    }
}