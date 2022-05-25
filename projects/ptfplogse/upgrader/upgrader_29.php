<?php
/**
 * upgrader_26: Transforma el archivo continguts.txt de los proyectos 'ptfploe'
 *             desde la versión 25 a la versión 26
 * @author rafael <rclaver@xtec.cat>
*/
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_LIB_IOC')) define('DOKU_LIB_IOC', DOKU_INC."lib/lib_ioc/");
require_once DOKU_LIB_IOC . "upgrader/CommonUpgrader.php";

class upgrader_29 extends ProgramacionsCommonUpgrader {

    public function process($type, $ver, $filename=NULL) {
        switch ($type) {
            case "fields":
                //Transforma los datos del proyecto desde la estructura de la versión $ver a la versión $ver+1
                $ret = true;
                break;

            case "templates":
                // Sólo se debe actualizar la versión del documento si el coordinador de calidad lo indica!!!!!!
                if (FALSE) {
                    if (!$this->upgradeDocumentVersion($ver)) return false;
                }

                //Transforma el archivo continguts.txt del proyecto desde la versión $ver a la versión $ver+1
                if ($filename===NULL)
                    $filename = $this->model->getProjectDocumentName();
                $doc = $this->model->getRawProjectDocument($filename);

                $aTokRep = [["alumn[ea]([,\.\:s\s])",
                            "estudiant$1"],
                            ["###:\n  \* És de caràcter.+?((?:\n  \* .*?)+?)?(\n  \* )(:###)(Té una ponderació en la \*\*qualificació final\*\*.)\n###:",
                             "\n  * És de caràcter <WIOCCL:IF condition=\"{##treballEquipEAF##}==true\">grupal</WIOCCL:IF><WIOCCL:IF condition=\"{##treballEquipEAF##}!=true\">individual</WIOCCL:IF>.$2$4###:$1"]
                           ];
                $dataChanged = $this->updateTemplateByReplace($doc, $aTokRep);

                if (($ret = !empty($dataChanged))) {
                    $this->model->setRawProjectDocument($filename, $dataChanged, "Upgrade templates: version ".($ver-1)." to $ver", $ver);
                }
                break;
        }
        return $ret;
    }

}
