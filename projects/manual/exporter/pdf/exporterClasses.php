<?php
/**
 * MainRender: clases de procesos render para export
 */
if (!defined('DOKU_INC')) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', realpath(DOKU_INC."lib/plugins/"));
require_once(DOKU_PLUGIN.'iocexportl/lib/renderlib.php');

class MainRender extends renderObject {


    public function __construct($factory, $typedef, $renderdef) {
        parent::__construct($factory, $typedef, $renderdef);
    }

    public function initParams(){
    }
}

class renderField extends AbstractRenderer {
    public function process($data) {
    }
}

class render_title extends renderField {
    public function process($data) {
        $ret = parent::process($data);
        return $ret;
    }
}

class renderFile extends AbstractRenderer {
    public function process($data) {
    }
}
