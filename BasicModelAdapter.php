<?php
/**
 * @author Rafael Claver
 */
if (!defined('DOKU_INC')) die();
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_INC . 'lib/plugins/wikiiocmodel/');

require_once (WIKI_IOC_MODEL . 'WikiIocModel.php');
//require_once (WIKI_IOC_MODEL . 'WikiIocInfoManager.php');

class BasicModelAdapter implements WikiIocModel {

    protected $persistenceEngine;

    public function __construct() {}
    
    public function setParams($element, $value) {} //Esto no parece demasiado útil, sólo está para cumplir la interficie

    public function init($persistenceEngine) {
        $this->persistenceEngine = $persistenceEngine;
        return $this;
    }

    public function getPersistenceEngine() {
        return $this->persistenceEngine;
    }
}