<?php
if (!defined('DOKU_INC')) die();

class GetProjectAction extends BasicGetUpdatableProjectAction {

    function runAction() {
        $response = parent::runAction();

        $model = $this->getModel();
        
        if (! $model->isProjectGenerated()) {
            $model->setViewConfigName("firstView");
        }
        return $response;
    }
    
}
