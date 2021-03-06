<?php
/**
 * @author Rafael Claver
 */
if (!defined('DOKU_INC')) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . "lib/plugins/");
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_PLUGIN . "wikiiocmodel/");
require_once (WIKI_IOC_MODEL . "WikiIocModel.php");

class BasicModelAdapter implements WikiIocModel {

    protected $persistenceEngine;

    public function __construct() {}

    public function init($persistenceEngine) {
        $this->persistenceEngine = $persistenceEngine;
        return $this;
    }

    public function getPersistenceEngine() {
        return $this->persistenceEngine;
    }

    public function setParams($element, $value) {}
}