<?php
if (!defined('DOKU_INC')) die();

class CreateProjectMetaDataAction extends BasicCreateProjectMetaDataAction{
    
    public function responseProcess() {
        $ret = parent::responseProcess();
        $this->getModel()->createTemplateDocument($ret);
        return $ret;
    }    
}