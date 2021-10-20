<?php
/**
 * upgrader_1: Transforma el archivo continguts.txt de los proyectos 'guiesges'
 *             desde la versión 0 a la versión 1
 * @author rafael
 */
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_LIB_IOC')) define('DOKU_LIB_IOC', DOKU_INC."lib/lib_ioc/");
require_once DOKU_LIB_IOC . "upgrader/CommonUpgrader.php";

class upgrader_1 extends CommonUpgrader {

    public function process($type, $ver, $filename=NULL) {
        switch ($type) {
            case "fields":
                $ret = TRUE;
                break;

            case "templates":
                // Força una copia del continguta al disc per tal que es desactivi l'edició parcial
//                if ($filename===NULL) { //Ojo! Ahora se pasa por parámetro
//                    $filename = $this->model->getProjectDocumentName();
//                }
                $doc = $this->model->getRawProjectDocument($filename)."\n";

                if (($ret = !empty($doc))) {
                    $this->model->setRawProjectDocument($filename, $doc, "Upgrade templates: version ".($ver-1)." to $ver", $ver);
                }
                break;
        }
        return $ret;
    }

}
