<?php
if (!defined('DOKU_INC')) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC."lib/plugins/");
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_PLUGIN."wikiiocmodel/");
//require_once WIKI_IOC_MODEL . "actions/BasicCreateProjectMetaDataAction.php";

class SetProjectMetaDataAction extends BasicSetProjectMetaDataAction{

    public function responseProcess() {
//        $this->params['mostrarAutor']= isset($this->params['mostrarAutor'])?"true":"false";
        $ret = parent::responseProcess();
        return $ret;
    }
}