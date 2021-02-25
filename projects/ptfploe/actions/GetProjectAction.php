<?php
if (!defined('DOKU_INC')) die();

class GetProjectAction extends BasicGetUpdatableProjectAction {

    function runAction() {
        $model = $this->getModel();
        
        if (! $model->isProjectGenerated()) {
            $model->setViewConfigName("firstView");
        }
        return parent::runAction();
    }
    
}
