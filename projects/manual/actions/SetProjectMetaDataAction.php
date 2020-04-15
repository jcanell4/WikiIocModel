<?php
if (!defined('DOKU_INC')) die();

class SetProjectMetaDataAction extends BasicSetProjectMetaDataAction{

    public function responseProcess() {
//        $this->params['mostrarAutor']= isset($this->params['mostrarAutor'])?"true":"false";
        $ret = parent::responseProcess();
        return $ret;
    }
}