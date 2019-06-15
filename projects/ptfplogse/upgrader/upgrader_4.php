<?php
/**
 * upgrader_3: Transforma los datos del proyecto "ptfplogse"
 *             desde la estructura de la versión 2 a la estructura de la versión 3
 * @author rafael
 */
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_LIB_IOC')) define('DOKU_LIB_IOC', DOKU_INC . "lib/lib_ioc/");
require_once DOKU_LIB_IOC . "upgrader/CommonUpgrader.php";

class upgrader_4 extends CommonUpgrader {

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
                $doc = $this->model->getRawProjectDocument($filename);


                //INSERT
                $aTokIns = [
                    [
                        'regexp' => "^::table:T09$",
                        'text' => "<WIOCCL:IF condition=\"{##hiHaRecuperacioPerJT##}==true\">\n",
                        'pos' => 0,
                        'modif' => "m"],
                    [
                        'regexp' => "::table:T09.*?:::",
                        'text' => "\n</WIOCCL:IF>",
                        'pos' => 1,
                        'modif' => "ms"],
                    [
                        'regexp' => "::table:T09.*?<WIOCCL:FOREACH var=\"item\" array=\"{##datesJT##}\">.*? \|$",
                        'text' => "\n</WIOCCL:IF>",
                        'pos' => 1,
                        'modif' => "ms"]
                ];
                $doc = $this->updateTemplateByInsert($doc, $aTokIns);

                // S'ha de fer després o no funciona
                $aTokIns = [
                    ['regexp' => "::table:T09.*?<WIOCCL:FOREACH var=\"item\" array=\"{##datesJT##}\">",
                        'text' => "<WIOCCL:IF condition=\"{##item[hiHaRecuperacio]##}==true\">",
                        'pos' => 1,
                        'modif' => "ms"]
                ];

                $doc = $this->updateTemplateByInsert($doc, $aTokIns);

                // Replace
                $aTokRep = [
                    // Date EAF
                    [
                        "(::table:T06.*?)(\^ data de publicació de la solució \^)",
                        "$1<WIOCCL:IF condition=\"{##hiHaSolucioPerEAF##}==true\">^ data de publicació de la solució </WIOCCL:IF>^",
                        "s"
                    ],
                    [
                        "(::table:T06.*?)(\| {#_DATE\(\"{##item\[solució\]##}\"\)_#} \|)",
                        "$1<WIOCCL:IF condition=\"{##hiHaSolucioPerEAF##}==true\">| <WIOCCL:IF condition=\"{##item[hiHaSolucio]##}==true\">{#_DATE(\"{##item[solució recuperació]##}\")_#}</WIOCCL:IF><WIOCCL:IF condition=\"{##item[hiHaSolucio]##}==false\">--</WIOCCL:IF> </WIOCCL:IF>|",
                        "s"
                    ],
                    [
                        "(::table:T07.*?)(\^ data de publicació de la solució \^)",
                        "$1<WIOCCL:IF condition=\"{##hiHaSolucioPerEAF##}==true\">^ data de publicació de la solució </WIOCCL:IF>^",
                        "s"
                    ],
                    [
                        "(::table:T07.*?)(\| {#_DATE\(\"{##item\[solució recuperació\]##}\"\)_#} \|)",
                        "$1<WIOCCL:IF condition=\"{##hiHaSolucioPerEAF##}==true\">| <WIOCCL:IF condition=\"{##item[hiHaSolucio]##}==true\">{#_DATE(\"{##item[solució recuperació]##}\")_#}</WIOCCL:IF><WIOCCL:IF condition=\"{##item[hiHaSolucio]##}==false\">--</WIOCCL:IF> </WIOCCL:IF>|",
                        "s"
                    ],
                    [
                        "(::table:T07.*?)(\^  data de publicació de l'enunciat  \^)",
                        "$1<WIOCCL:IF condition=\"{##hiHaEnunciatRecuperacioPerEAF##}==true\">^  data de publicació de l'enunciat  </WIOCCL:IF>^",
                        "s"
                    ],
                    [
                        "(::table:T07.*?)(\| {#_DATE\(\"{##item\[enunciat recuperació\]##}\"\)_#} \|)",
                        "$1<WIOCCL:IF condition=\"{##hiHaEnunciatRecuperacioPerEAF##}==true\">| <WIOCCL:IF condition=\"{##item[hiHaEnunciatRecuperacio]##}==true\">{#_DATE(\"{##item[enunciat recuperació]##}\")_#}</WIOCCL:IF><WIOCCL:IF condition=\"{##item[hiHaEnunciatRecuperacio]##}==false\">--</WIOCCL:IF> </WIOCCL:IF>|",
                        "s"
                    ],

                    // dateAC
                    [
                        "(::table:T05.*?)(\^ data de publicació de la solució \^)",
                        "$1<WIOCCL:IF condition=\"{##hiHaSolucioPerAC##}==true\">^ data de publicació de la solució </WIOCCL:IF>^",
                        "s"
                    ],
                    [
                        "(::table:T05.*?)(\| {#_DATE\(\"{##item\[solució\]##}\"\)_#} \|)",
                        "$1<WIOCCL:IF condition=\"{##hiHaSolucioPerAC##}==true\">| <WIOCCL:IF condition=\"{##item[hiHaSolucio]##}==true\">{#_DATE(\"{##item[solució]##}\")_#}</WIOCCL:IF><WIOCCL:IF condition=\"{##item[hiHaSolucio]##}==false\">--</WIOCCL:IF> </WIOCCL:IF>|",
                        "s"
                    ],

                ];
                $doc = $this->updateTemplateByReplace($doc, $aTokRep);


                if (!empty($doc)) {
                    $this->model->setRawProjectDocument($filename, $doc, "Upgrade: version 3 to 4");
                }
                $status = !empty($doc);
        }
        return $status;
    }

}
