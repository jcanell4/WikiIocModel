<?php
/**
 * AbstractWikiAction
 * @author josep
 */
if (!defined("DOKU_INC")) die();

abstract class AbstractWikiAction {

    static protected $flagMainAction = NULL; //control de Actions en cascada
    protected $params;
    protected $modelManager;

    public function __destruct() {
        if (get_class($this) === self::$flagMainAction) {
            self::$flagMainAction = NULL;
        }
    }

    public function init($modelManager = NULL) {
        $this->modelManager = $modelManager;
        self::$flagMainAction = (!self::$flagMainAction) ? get_class($this) : self::$flagMainAction;
    }

    public function get($paramsArr = array()){
        //previene que un Action llamado por otro Action vuelva a ejecutar los trigers
        $jomateix = (get_class($this) === self::$flagMainAction);

        if ($jomateix)
            $this->triggerStartEvents();
        if (!empty($paramsArr))
            $this->setParams($paramsArr);
        $ret = $this->responseProcess();
        if ($jomateix)
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
