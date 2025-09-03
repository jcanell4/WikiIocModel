<?php
if (!defined('DOKU_INC')) die();

class CreateProjectAction extends BasicCreateProjectAction{
    
    public function responseProcess() {
        $this->getModel()->setViewConfigKey(ProjectKeys::KEY_VIEW_FIRSTVIEW);
        $ret = parent::responseProcess();
//        $this->getModel()->createTemplateDocument($ret);
        return $ret;
    }    
}