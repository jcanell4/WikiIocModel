<?php
/**
 * upgrader_6: Transforma el archivo continguts.txt de los proyectos 'prgfploe'
 *             desde la versión 5 a la versión 6
 * @author rafael <rclaver@xtec.cat>
*/
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_LIB_IOC')) define('DOKU_LIB_IOC', DOKU_INC."lib/lib_ioc/");
require_once DOKU_LIB_IOC . "upgrader/CommonUpgrader.php";

class upgrader_6 extends ProgramacionsCommonUpgrader {

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

                $aTokRep = [["(\*\*QFU\{##itemUf\[unitat formativa\]##\})(= \{#_GET_PERCENT\(\{##sum_ponderacio##\},\{##ponderacioAC##\}\)_#\}% AC)( \+ )",
                             "$1 $2<WIOCCL:IF condition=\"{##length##}\\>0\">$3</WIOCCL:IF>"]
                           ];
                $dataChanged = $this->updateTemplateByReplace($doc, $aTokRep);

                $aTokIns = [['regexp' => "<WIOCCL:IF condition=\"\{##keyUfEAF##\}===false && \{##keyUfJT##\}===false\">",
                             'text' => "\n<WIOCCL:IF condition=\"{##keyUfPAF##}===false && {##keyUfPAFV##}===false\">".
                                       "\nNo hi ha prova d'avaluació final.".
                                       "\n</WIOCCL:IF>".
                                       "\n<WIOCCL:IF condition=\"{##keyUfPAF##}!==false || {##keyUfPAFV##}!==false\">",
                             'pos' => CommonUpgrader::DESPRES,
                             'modif' => "m"],
                            ['regexp' => "<WIOCCL:IF condition=\"\{##keyUfEAF##\}!==false \|\| \{##keyUfJT##\}!==false\">",
                             'text' => "</WIOCCL:IF>\n",
                             'pos' => CommonUpgrader::ABANS,
                             'modif' => "m"]
                           ];
                $dataChanged = $this->updateTemplateByInsert($dataChanged, $aTokIns);

                if (($ret = !empty($dataChanged))) {
                    $this->model->setRawProjectDocument($filename, $dataChanged, "Upgrade templates: version ".($ver-1)." to $ver", $ver);
                }
                break;
        }
        return $ret;
    }

}
