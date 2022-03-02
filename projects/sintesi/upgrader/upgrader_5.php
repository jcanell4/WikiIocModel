<?php
/**
 * upgrader_5: Transforma el archivo continguts.tx del proyecto 'sintesi'
 *              desde la versión 4 a la versión 5
 * @author rafael <rclaver@xtec.cat>
 */
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_LIB_IOC')) define('DOKU_LIB_IOC', DOKU_INC."lib/lib_ioc/");
require_once DOKU_LIB_IOC . "upgrader/CommonUpgrader.php";

class upgrader_5 extends CommonUpgrader {

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

                $aTokIns = [['regexp' => "^<WIOCCL:FOREACH var=\"item\" array=\"\{##dadesAC##\}\">$",
                               'text' => "<WIOCCL:SET var=\"sortedDadesAC\" type=\"literal\" value=\"{#_ARRAY_SORT({##dadesAC##},''lliurament'')_#}\">\n",
                               'pos' => CommonUpgrader::ABANS,
                               'modif' => "m"],
                            ['regexp' => "^\|\s*\{##item\[període\]##\}.*\n<\/WIOCCL:FOREACH>",
                               'text' => "\n</WIOCCL:SET>",
                               'pos' => CommonUpgrader::DESPRES,
                               'modif' => "m"]
                           ];
                $dataChanged = $this->updateTemplateByInsert($doc, $aTokIns);

                $aTokRep = [["^(<WIOCCL:FOREACH var=\"item\" array=\"\{##)(dadesAC)(##\}\">)$",
                             "$1sortedDadesAC$3"]
                           ];
                $dataChanged = $this->updateTemplateByReplace($dataChanged, $aTokRep);

                if (($ret = !empty($dataChanged))) {
                    $this->model->setRawProjectDocument($filename, $dataChanged, "Upgrade templates: version ".($ver-1)." to $ver", $ver);
                }
                break;
        }
        return $ret;
    }

}
