<?php
/**
 * exportDocument: clase que renderiza grupos de elementos
 * @culpable Rafael Claver
*/
if (!defined('DOKU_INC')) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC."lib/plugins/");

class exportDocument extends MainRender {

    public function __construct($factory, $typedef, $renderdef, $params) {
        parent::__construct($factory, $typedef, $renderdef);
        $this->initParams($params);
    }

    public function initParams($params=NULL){
        parent::initParams();
    }

    public function cocinandoLaPlantillaConDatos($data) {
    }
}
