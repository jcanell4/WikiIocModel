<?php
/**
 * upgrader_17: Transforma el archivo continguts.txt de los proyectos "ptfplogse"
 *              desde la versión 16 a la versión 17
 * @culpable Rafael (empresonat) 25-05-2020
 */
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_LIB_IOC')) define('DOKU_LIB_IOC', DOKU_INC . "lib/lib_ioc/");
require_once DOKU_LIB_IOC . "upgrader/CommonUpgrader.php";

class upgrader_17 extends CommonUpgrader {

    protected $model;
    protected $metaDataSubSet;

    public function __construct($model) {
        $this->model = $model;
        $this->metaDataSubSet = $this->model->getMetaDataSubSet();
    }

    public function process($type, $filename = NULL) {
        switch ($type) {
            case "fields":
                $status = TRUE;
                break;

            case "templates":
                if ($filename===NULL) {
                    $filename = $this->model->getProjectDocumentName();
                }
                $doc = $this->model->getRawProjectDocument($filename)."\n";

                $aTokIns = [
                            ['regexp' => "^En el cas dels EAF establerts amb metodologia de treball en equip,",
                             'text' => "<WIOCCL:IF condition=\"{##treballEquipEAF##}==true\">\n",
                             'pos' => 0,
                             'modif' => "m"
                            ],
                            ['regexp' => "^\s+\* La recuperació individual fa referència al contingut.*novament en grup\.\n",
                             'text' => "</WIOCCL:IF>\n",
                             'pos' => 1,
                             'modif' => "m"
                            ]
                           ];
                $doc = $this->updateTemplateByInsert($doc, $aTokIns);

                $aTokIns = [
                            ['regexp' => "^La qualificació final del .*fórmula següent:$",
                             'text' => "\n<WIOCCL:SET var=\"sum_ponderacio\" type=\"literal\" value=\"{#_ARRAY_GET_SUM({##dadesQualificacio##},''ponderació'')_#}\">",
                             'pos' => 1,
                             'modif' => "m"
                            ],
                            ['regexp' => "^Per sumar l'AC s’ha d’obtenir una qualificació mínima de",
                             'text' => "</WIOCCL:SET>\n",
                             'pos' => 0,
                             'modif' => "m"
                            ]
                           ];
                $doc = $this->updateTemplateByInsert($doc, $aTokIns);

                $aTokRep = [
                            ["(\{##item\[abreviació qualificació\]##\} \* )(\{##item\[ponderació\]##\})(%)",
                             "$1{#_GET_PERCENT({##sum_ponderacio##}, {##item[ponderació]##})_#}$3"
                            ]
                           ];
                $doc = $this->updateTemplateByReplace($doc, $aTokRep);

                if (!empty($doc)) {
                    $this->model->setRawProjectDocument($filename, $doc, "Upgrade: version 16 to 17 (Simultànea a la actualització de 8 a 9 de _wikiIocSystem_.mdpr)");
                }
                $status = !empty($doc);
        }
        return $status;
    }

}
