<?php
/**
 * upgrader_12: Transforma el archivo continguts.txt de los proyectos 'prgfploe'
 *             desde la versión 11 a la versión 12
 * @author rafael <rclaver@xtec.cat>
*/
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_LIB_IOC')) define('DOKU_LIB_IOC', DOKU_INC."lib/lib_ioc/");
require_once DOKU_LIB_IOC . "upgrader/CommonUpgrader.php";

class upgrader_12 extends ProgramacionsCommonUpgrader {

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

                $aTokRep = [["(<WIOCCL:FOREACH var=\")(.*?)(\" array=\"\{##)(taulaDadesUF)(##\}\">)",
                             "$1$2$3sortedTaulaDadesUF$5"],
                            ["(:###\n<\/WIOCCL:FOREACH>\n)(###:\n)",
                             "$1</WIOCCL:SET>\n$2"]
                           ];
                $dataChanged = $this->updateTemplateByReplace($doc, $aTokRep);

                $aTokIns = [['regexp' => "^<WIOCCL:FOREACH var=\".*?\".*?array=\"\{##sortedTaulaDadesUF##\}\">",
                               'text' => "<WIOCCL:SET var=\"sortedTaulaDadesUF\" type=\"literal\" value=\"{#_ARRAY_SORT({##taulaDadesUF##},''ordreImparticio'')_#}\">\n",
                               'pos' => CommonUpgrader::ABANS,
                               'modif' => "m"],

                            ['regexp' => "\|  \{##item\[unitat formativa\]##\}.*\{##item\[hores\]##\}  \|\n<\/WIOCCL:FOREACH>",
                               'text' => "\n</WIOCCL:SET>",
                               'pos' => CommonUpgrader::DESPRES,
                               'modif' => "m"],
                            ['regexp' => "<\/WIOCCL:IF>\n<\/WIOCCL:FOREACH>\n<\/WIOCCL:SET>",
                               'text' => "\n</WIOCCL:SET>",
                               'pos' => CommonUpgrader::DESPRES,
                               'modif' => "m"],
                            ['regexp' => "<\/WIOCCL:FOREACH>\n<\/WIOCCL:FOREACH>\n<\/WIOCCL:SET>",
                               'text' => "\n</WIOCCL:SET>",
                               'pos' => CommonUpgrader::DESPRES,
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
