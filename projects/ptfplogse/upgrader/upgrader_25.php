<?php
/**
 * upgrader_25: Transforma el archivo continguts.tx del proyecto 'ptfplogse'
 *              desde la versión 24 a la versión 25
 * @author rafael <rclaver@xtec.cat>
 */
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_LIB_IOC')) define('DOKU_LIB_IOC', DOKU_INC."lib/lib_ioc/");
require_once DOKU_LIB_IOC . "upgrader/CommonUpgrader.php";

class upgrader_25 extends CommonUpgrader {

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

                $aTokIns = [['regexp' => "^<WIOCCL:SET var=\"previousEAC\" type=\"literal\" value=\"\">$",
                               'text' => "\n<WIOCCL:SET var=\"sortedDatesAC\" type=\"literal\" value=\"{#_ARRAY_SORT({##datesAC##},''lliurament'')_#}\">",
                               'pos' => CommonUpgrader::DESPRES,
                               'modif' => "m"],
                            ['regexp' => "<WIOCCL:RESET var=\"previousEAC\".*>\n<\/WIOCCL:CHOOSE>\n<\/WIOCCL:FOREACH>",
                               'text' => "\n</WIOCCL:SET>",
                               'pos' => CommonUpgrader::DESPRES,
                               'modif' => "m"],
                            ['regexp' => "::table:T06.*?<WIOCCL:SET var=\"previousEAF\" type=\"literal\" value=\"\">",
                               'text' => "\n<WIOCCL:SET var=\"sortedDatesEAF\" type=\"literal\" value=\"{#_ARRAY_SORT({##datesEAF##},''lliurament'')_#}\">",
                               'pos' => CommonUpgrader::DESPRES,
                               'modif' => "s"],
                            ['regexp' => "::table:T07.*?<WIOCCL:SET var=\"previousEAF\" type=\"literal\" value=\"\">",
                               'text' => "\n<WIOCCL:SET var=\"sortedDatesEAF\" type=\"literal\" value=\"{#_ARRAY_SORT({##datesEAF##},''lliurament recuperació'')_#}\">",
                               'pos' => CommonUpgrader::DESPRES,
                               'modif' => "s"],
                            ['regexp' => "<WIOCCL:RESET var=\"previousEAF\".*>\n<\/WIOCCL:CHOOSE>\n<\/WIOCCL:FOREACH>",
                               'text' => "\n</WIOCCL:SET>",
                               'pos' => CommonUpgrader::DESPRES,
                               'modif' => "m"],
                            ['regexp' => "^<WIOCCL:FOREACH var=\"item\" array=\"\{##datesJT##\}\">$",
                               'text' => "<WIOCCL:SET var=\"sortedDatesJT\" type=\"literal\" value=\"{#_ARRAY_SORT({##datesJT##},''llista provisional'')_#}\">\n",
                               'pos' => CommonUpgrader::ABANS,
                               'modif' => "m"],
                            ['regexp' => "^\|\s+\{##item\[id\]##\}\s+\|\s+\{##item\[inscripció\]##\}.*\n<\/WIOCCL:FOREACH>",
                               'text' => "\n</WIOCCL:SET>",
                               'pos' => CommonUpgrader::DESPRES,
                               'modif' => "m"],
                            ['regexp' => "^<WIOCCL:FOREACH var=\"item\" array=\"\{##datesJT##\}\"><WIOCCL:IF.*>$",
                               'text' => "<WIOCCL:SET var=\"sortedDatesJT\" type=\"literal\" value=\"{#_ARRAY_SORT({##datesJT##},''llista provisional recuperació'')_#}\">\n",
                               'pos' => CommonUpgrader::ABANS,
                               'modif' => "m"],
                            ['regexp' => "^\|\s+\{##item\[id\]##\}\s+\|\s+\{##item\[inscripció recuperació\]##\}.*\n<\/WIOCCL:IF>\n<\/WIOCCL:FOREACH>",
                               'text' => "\n</WIOCCL:SET>",
                               'pos' => CommonUpgrader::DESPRES,
                               'modif' => "m"],
                           ];
                $dataChanged = $this->updateTemplateByInsert($doc, $aTokIns);

                $aTokRep = [["^(<WIOCCL:FOREACH var=\"item\" array=\"\{##)(datesAC)(##\}\".*\{##unitsFromBlock##\}\">)$",
                             "$1sortedDatesAC$3"],
                            ["^(<WIOCCL:FOREACH var=\"item\" array=\"\{##)(datesEAF)(##\}\".*\{##unitsFromBlock##\}\">)$",
                             "$1sortedDatesEAF$3"],
                            ["^(<WIOCCL:FOREACH var=\"item\" array=\"\{##)(datesJT)(##\}\">)",
                             "$1sortedDatesJT$3"],
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
