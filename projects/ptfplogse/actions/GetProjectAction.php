<?php
if (!defined('DOKU_INC')) die();

class GetProjectAction extends BasicGetProjectAction {
    
    function runAction() {
        if (!$this->getModel()->isProjectGenerated()) {
            $this->getModel()->setViewConfigName("firstView");
        }
        return parent::runAction();
    }
}