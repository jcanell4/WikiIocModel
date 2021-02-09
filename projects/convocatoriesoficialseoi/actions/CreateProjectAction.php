<?php
if (!defined('DOKU_INC')) die();

class CreateProjectAction extends BasicCreateProjectAction{
    
    public function responseProcess() {
        $ret = parent::responseProcess();
//        $this->getModel()->createTemplateDocument($ret);
        return $ret;
    }    
}