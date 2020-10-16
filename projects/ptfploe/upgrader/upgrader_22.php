<?php
/**
 * upgrader_22: Transforma el archivo continguts.txt de los proyectos 'ptfploe'
 *             desde la versión 21 a la versión 22
 * @author rafael
 */
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_LIB_IOC')) define('DOKU_LIB_IOC', DOKU_INC."lib/lib_ioc/");
require_once DOKU_LIB_IOC . "upgrader/CommonUpgrader.php";

class upgrader_22 extends CommonUpgrader {

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
                // Força una copia del continguta al disc per tal que es desactivi l'edició parcial
                if ($filename===NULL) { //Ojo! Ahora se pasa por parámetro
                    $filename = $this->model->getProjectDocumentName();
                }
                $doc = $this->model->getRawProjectDocument($filename)."\n";

                if (($ret = !empty($doc))) {
                    $this->model->setRawProjectDocument($filename, $doc, "Upgrade templates: version ".($ver-1)." to $ver");
                }
                break;
        }
        return $ret;
    }

}
