<?php
/**
 * upgrader_26: Transforma el archivo continguts.txt de los proyectos 'ptfploe'
 *             desde la versión 25 a la versión 26
 * @author rafael <rclaver@xtec.cat>
*/
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_LIB_IOC')) define('DOKU_LIB_IOC', DOKU_INC."lib/lib_ioc/");
require_once DOKU_LIB_IOC . "upgrader/CommonUpgrader.php";

class upgrader_26 extends ProgramacionsCommonUpgrader {

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

                /* Este modelo no sirve dado que aplica la primera aparición de $2 ("item") a todas las sustituciones
                 * $aTokRep = [["^(<WIOCCL:FOREACH var=\")(.*?)(\" array=\"\{##)(taulaDadesUD)(##\}\")",
                 *              "$1$2$3sortedTaulaDadesUD$5"],
                 */
                $aTokRep = [["^(<WIOCCL:FOREACH var=\"item\" array=\"\{##)(taulaDadesUD)(##\}\")",
                             "$1sortedTaulaDadesUD$3"],
                            ["^(<WIOCCL:FOREACH var=\"itemUD\" array=\"\{##)(taulaDadesUD)(##\}\")",
                             "$1sortedTaulaDadesUD$3"]
                           ];
                $dataChanged = $this->updateTemplateByReplace($doc, $aTokRep);

                $aTokIns = [['regexp' => "^<WIOCCL:FOREACH var=\"item\" array=\"\{##sortedTaulaDadesUD##\}\".*",
                               'text' => "<WIOCCL:SET var=\"sortedTaulaDadesUD\" type=\"literal\" value=\"{#_ARRAY_SORT({##taulaDadesUD##},''ordreImparticio'')_#}\">\n",
                               'pos' => CommonUpgrader::ABANS,
                               'modif' => "m"],
                            ['regexp' => "^<WIOCCL:FOREACH var=\"itemUD\" array=\"\{##sortedTaulaDadesUD##\}\".*",
                               'text' => "<WIOCCL:SET var=\"sortedTaulaDadesUD\" type=\"literal\" value=\"{#_ARRAY_SORT({##taulaDadesUD##},''ordreImparticio'')_#}\">\n",
                               'pos' => CommonUpgrader::ABANS,
                               'modif' => "m"],

                            ['regexp' => "\|\s*UD\{##item\[unitat didàctica\]##\}.*\{##item\[hores\]##}.*?\|\n<\/WIOCCL:FOREACH>",
                               'text' => "\n</WIOCCL:SET>",
                               'pos' => CommonUpgrader::DESPRES,
                               'modif' => "m"],

                            ['regexp' => "\(\{##item\[id\]##\}\)\n<\/WIOCCL:FOREACH>\n<\/WIOCCL:FOREACH>",
                               'text' => "\n</WIOCCL:SET>",
                               'pos' => CommonUpgrader::DESPRES,
                               'modif' => "m"],
                    
                            ['regexp' => ":::\n\n<\/WIOCCL:FOREACH>\n<\/WIOCCL:SET>",
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
