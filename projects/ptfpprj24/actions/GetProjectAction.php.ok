<?php
if (!defined('DOKU_INC')) die();

class GetProjectAction extends BasicGetUpdatableProjectAction {

    function runAction() {
        $model = $this->getModel();
        
        if (! $model->isProjectGenerated()) {
            $model->setViewConfigKey(ProjectKeys::KEY_VIEW_FIRSTVIEW);
        }
        return parent::runAction();
    }

    protected function isUpdatedDate($metaDataSubSet) {
        return ViewProjectAction::stIsUpdatedDatePtfpprj24($this, $metaDataSubSet);
    }
    
}
