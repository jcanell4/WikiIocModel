<?php
/**
 * AbstractWikiAction
 * @author josep
 */
if (!defined("DOKU_INC")) die();

abstract class AbstractWikiAction {

    protected $params;
    protected $modelManager;

    public function init($modelManager = NULL) {
        $this->modelManager = $modelManager;
    }

    public function get($paramsArr = array()){
        $this->triggerStartEvents();
        if (!empty($paramsArr))
            $this->setParams($paramsArr);
        $ret = $this->responseProcess();
        $this->triggerEndEvents();
        return $ret;
    }

    public function getModelManager() {
        return $this->modelManager;
    }

    public static function generateInfo($type, $message, $id='', $duration=-1) {
        return IocCommon::generateInfo($type, $message, $id, $duration);
    }

    protected function addInfoToInfo( $infoA, $infoB ) {
        return IocCommon::addInfoToInfo($infoA, $infoB);
    }

    protected function triggerStartEvents() {
        $tmp= array(); //NO DATA
        trigger_event( 'WIOC_AJAX_COMMAND_STARTED', $tmp);
        if(!empty($tmp)){
            $this->preResponseTmp[] = $tmp;
        }
    }

    protected function triggerEndEvents() {
        $tmp = array(); //NO DATA
        trigger_event( 'WIOC_AJAX_COMMAND_DONE', $tmp );
        if(!empty($tmp)){
            $this->postResponseTmp[] = $tmp;
        }
    }

    protected function setParams($paramsArr){
        $this->params = $paramsArr;
    }

    protected abstract function responseProcess();
}
