<?php
if (!defined('DOKU_INC')) die();

class GetProjectAction extends BasicGetProjectAction {
    
    function runAction() {
        if (!$this->getModel()->isProjectGenerated()) {
            $this->getModel()->setViewConfigKey(ProjectKeys::KEY_VIEW_FIRSTVIEW);
        }
        return parent::runAction();
    }
}