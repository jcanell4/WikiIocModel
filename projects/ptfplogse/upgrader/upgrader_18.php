<?php
/**
 * upgrader_18: Transforma el archivo continguts.txt de los proyectos 'ptfplogse'
 *              desde la versión 17 a la versión 18 (templates)
 * @author rafael
 */
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_LIB_IOC')) define('DOKU_LIB_IOC', DOKU_INC."lib/lib_ioc/");
require_once DOKU_LIB_IOC . "upgrader/CommonUpgrader.php";

class upgrader_18 extends CommonUpgrader {

    protected $model;
    protected $metaDataSubSet;

    public function __construct($model) {
        $this->model = $model;
        $this->metaDataSubSet = $this->model->getMetaDataSubSet();
    }

    public function process($type, $filename=NULL) {

        switch ($type) {
            case "fields":
                $ret = TRUE;
                break;

            case "templates":
                if ($filename===NULL) {
                    $filename = $this->model->getProjectDocumentName();
                }
                $doc = $this->model->getRawProjectDocument($filename)."\n";

                //426: "<\/WIOCCL:IF>\s<\/WIOCCL:SET>\s+Per sumar"
                //436, 443: "{#_FIRST({##filteredPAF##}, ''FIRST[ponderació]'')_#}"
                //438: "(\{##item\[ponderació\]##\})%"
                $aTokRep = [
                            ["(<\/WIOCCL:IF>)(\s<\/WIOCCL:SET>)(\s\sPer sumar)",
                             "$1$3"
                            ],
                            ["(\{#_FIRST\(\{##filteredPAF##\}, ''FIRST\[ponderació\]''\)_#\})",
                             "{#_GET_PERCENT({##sum_ponderacio##}, $1)_#}"
                            ],
                            ["(\{##item\[ponderació\]##\})%",
                             "{#_GET_PERCENT({##sum_ponderacio##}, $1)_#}%"
                            ]
                           ];
                $doc = $this->updateTemplateByReplace($doc, $aTokRep);

                //443: "^\*\*Si la qualificació de la PAF.*a la PAF\*\*\.\s<\/WIOCCL:IF>"
                $aTokIns = [
                            ['regexp' => "\*\*Si la qualificació de la PAF.*a la PAF\*\*\.\s<\/WIOCCL:IF>",
                             'text' => "\n</WIOCCL:SET>",
                             'pos' => 1,
                             'modif' => "m"
                            ]
                           ];
                $doc = $this->updateTemplateByInsert($doc, $aTokIns);

                if (!empty($doc)) {
                    $this->model->setRawProjectDocument($filename, $doc, "Upgrade -templates- version 17 to 18");
                }
                $ret = !empty($doc);
        }
        return $ret;
    }

}
