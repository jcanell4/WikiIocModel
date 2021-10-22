<?php
/**
 * upgrader_27: Transforma el archivo continguts.tx del proyecto 'ptfploe'
 *              desde la versión 26 a la versión 27 (asociado a la actualización de 8 a 9 de los datos del proyecto)
 * @author rafael <rclaver@xtec.cat>
 */
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_LIB_IOC')) define('DOKU_LIB_IOC', DOKU_INC."lib/lib_ioc/");
require_once DOKU_LIB_IOC . "upgrader/CommonUpgrader.php";

class upgrader_27 extends CommonUpgrader {

    public function process($type, $ver, $filename=NULL) {
        switch ($type) {
            case "fields":
                $ret = TRUE;
                break;

            case "templates":
                if ($filename===NULL) {
                    $filename = $this->model->getProjectDocumentName();
                }
                $doc = $this->model->getRawProjectDocument($filename);

                $aTokRep = [[
                    "Té una durada d'..(\{##duradaPAF##\})\*\*",
                    "$1"
                ]];
                $dataChanged = $this->updateTemplateByReplace($doc, $aTokRep);

                if (($ret = !empty($dataChanged))) {
                    $this->model->setRawProjectDocument($filename, $dataChanged, "Upgrade templates: version ".($ver-1)." to $ver (simultànea a la actualització de 8 a 9 de fields)", $ver);
                }
                break;
        }
        return $ret;
    }

}
