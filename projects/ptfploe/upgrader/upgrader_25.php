<?php
/**
 * upgrader_25: Transforma el archivo continguts.txt de los proyectos 'ptfploe'
 *              desde la versión 24 a la versión 25 (templates)
 * @author rafael
 */
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_LIB_IOC')) define('DOKU_LIB_IOC', DOKU_INC."lib/lib_ioc/");
require_once DOKU_LIB_IOC . "upgrader/CommonUpgrader.php";

class upgrader_25 extends CommonUpgrader {

    public function process($type, $ver, $filename=NULL) {

        switch ($type) {
            case "fields":
                $ret = TRUE;
                break;

            case "templates":
                if ($filename===NULL) {
                    $filename = $this->model->getProjectDocumentName();
                }
                $doc = $this->model->getRawProjectDocument($filename)."\n";

                //458: "^<WIOCCL:IF condition=\"\{#_COUNTINARRAY\(\{##dadesQualificacioUFs##\},''tipus qualificació'', \[''EAF'',''JT''\]\)_#}==0\">$"
                //487: "^<WIOCCL:SUBSET subsetvar=\"filteredDadesQualificacioUFs\".*formativa\]##\}\">$"
                $aTokIns = [
                            ['regexp' => "^<WIOCCL:IF condition=\"\{#_COUNTINARRAY\(\{##dadesQualificacioUFs##\},''tipus qualificació'', \[''EAF'',''JT''\]\)_#}==0\">$",
                             'text' => "<WIOCCL:SET var=\"sum_ponderacio\" type=\"literal\" value=\"{#_ARRAY_GET_SUM({##dadesQualificacioUFs##},''ponderació'')_#}\">\n",
                             'pos' => 0,
                             'modif' => "m"
                            ],
                            ['regexp' => "^<WIOCCL:SUBSET subsetvar=\"filteredDadesQualificacioUFs\".*formativa\]##\}\">$",
                             'text' => "\n<WIOCCL:SET var=\"sum_ponderacio\" type=\"literal\" value=\"{#_ARRAY_GET_SUM({##filteredDadesQualificacioUFs##},''ponderació'')_#}\">",
                             'pos' => 1,
                             'modif' => "m"
                            ]
                           ];
                $doc = $this->updateTemplateByInsert($doc, $aTokIns);

                //430, 471, 501: "(\{##item\[ponderació\]##\})%"
                //459, 468, 490, 498: "{#_FIRST({##filtered.*##}, ''FIRST[ponderació]'')_#}"
                $aTokRep = [
                            ["(\{##item\[ponderació\]##\})%",
                             "{#_GET_PERCENT({##sum_ponderacio##},$1)_#}%"
                            ],
                            ["(\{#_FIRST\(\{##filtered.*##\}, ''FIRST\[ponderació\]''\)_#\})",
                             "{#_GET_PERCENT({##sum_ponderacio##},$1)_#}"
                            ]
                           ];
                $doc = $this->updateTemplateByReplace($doc, $aTokRep);

                //430: "^(\*\*QF = <WIOCCL:FOREACH var=\"item\".*WIOCCL:FOREACH>\*\*)$"
                //474: "(<\/WIOCCL:FOREACH>\s:::\s<\/WIOCCL:IF>\s)(<\/WIOCCL:IF>\s<WIOCCL:IF condition=)"
                //505: "(<\/WIOCCL:FOREACH>\s:::\s<\/WIOCCL:IF>\s<\/WIOCCL:SUBSET>\s)"
                //527: "^\*\*QFM= <WIOCCL:FOREACH.*FOREACH> \*\*$"
                $aTokRep = [
                            ["^(\*\*QF = <WIOCCL:FOREACH var=\"item\".*WIOCCL:FOREACH>\*\*)$",
                             "<WIOCCL:SET var=\"sum_ponderacio\" type=\"literal\" value=\"{#_ARRAY_GET_SUM({##dadesQualificacioUFs##},''ponderació'')_#}\">\n".
                             "$1\n".
                             "</WIOCCL:SET>"
                            ],
                            ["(<\/WIOCCL:FOREACH>\s:::\s<\/WIOCCL:IF>\s)(<\/WIOCCL:IF>\s<WIOCCL:IF condition=)",
                             "$1</WIOCCL:SET>\n$2"
                            ],
                            ["(<\/WIOCCL:FOREACH>\s:::\s<\/WIOCCL:IF>\s<\/WIOCCL:SUBSET>\s)",
                             "$1</WIOCCL:SET>\n"
                            ],
                            ["^(\*\*QFM= <WIOCCL:FOREACH.*FOREACH> \*\*)$",
                             "<WIOCCL:SET var=\"sum_ponderacio\" type=\"literal\" value=\"{#_ARRAY_GET_SUM({##taulaDadesUF##},''ponderació'')_#}\">\n".
                             "$1\n".
                             "</WIOCCL:SET>"
                            ]
                           ];
                $doc = $this->updateTemplateByReplace($doc, $aTokRep);

                if (($ret = !empty($doc))) {
                    $this->model->setRawProjectDocument($filename, $doc, "Upgrade templates: version ".($ver-1)." to $ver", $ver);
                }
                break;
        }
        return $ret;
    }

}
