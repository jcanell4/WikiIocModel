<?php
/**
 * upgrader_1: Transforma el archivo continguts.txt de los proyectos 'convocatoriesoficialseoi'
 *             desde la versión 0 a la versión 1
 * @author rafael
 */
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_LIB_IOC')) define('DOKU_LIB_IOC', DOKU_INC."lib/lib_ioc/");
require_once DOKU_LIB_IOC . "upgrader/CommonUpgrader.php";

class upgrader_2 extends CommonUpgrader {

    protected $model;
    protected $metaDataSubSet;

    public function __construct($model) {
        $this->model = $model;
        $this->metaDataSubSet = $this->model->getMetaDataSubSet();
    }

    public function process($type, $ver, $filename=NULL) {
        switch ($type) {
            case "fields":
                
                $ret = TRUE;
                break;

            case "templates":
                // Es copia totl
                if ($filename===NULL) { //Ojo! Ahora se pasa por parámetro
                    $filename = $this->model->getProjectDocumentName();
                }
                
                $plantilla = $this->model->getRawProjectTemplate($filename, $ver);
                

                if (($ret = !empty($plantilla))) {
                    $this->model->setRawProjectDocument($filename, $plantilla, "Upgrade templates: version ".($ver-1)." to $ver");
                }
                break;
        }
        return $ret;
    }

}
