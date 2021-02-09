<?php
if (!defined('DOKU_INC')) die();

class CreateProjectAction extends BasicCreateProjectAction{
    
    public function responseProcess() {
        $this->getModel()->setViewConfigName("firstView");
        $ret = parent::responseProcess();
//        $this->getModel()->createTemplateDocument($ret);
        return $ret;
    }    
}