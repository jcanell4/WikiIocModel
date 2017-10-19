<?php
/**
 * AbstractRenderer: clases de procesos, establecidas en el fichero de configuración,
 *                  correspondientes a los tipos de datos del proyecto
 * @culpable Rafael Claver
 */
if (!defined('DOKU_INC')) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', realpath(DOKU_INC."lib/plugins/"));
require_once DOKU_PLUGIN."wikiiocmodel/projects/documentation/renderer/renderClasses.php"; //[JOSEP] TODO: incorporarla càrrega automàtica des de inc_ioc/ioc_project_load.php
//require_once DOKU_PLUGIN."iocexportl/lib/renderlib.php";

class MainRender extends renderObject {
    public function process($data) {
        parent::process($data);
        //...
    }
}

class renderField extends AbstractRenderer {
    public function process($data) {}

}

class renderFile extends AbstractRenderer {
    public function process($data) {}
}
