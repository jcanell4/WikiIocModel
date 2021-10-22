<?php
/**
 * upgrader_8: Transforma la estructura de datos y el archivo continguts.txt de los proyectos 'ptfploe'
 *             desde la versión 7 a la versión 8
 * @author rafael
 */
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_LIB_IOC')) define('DOKU_LIB_IOC', DOKU_INC."lib/lib_ioc/");
require_once DOKU_LIB_IOC . "upgrader/CommonUpgrader.php";

class upgrader_8 extends CommonUpgrader {

    public function process($type, $ver, $filename=NULL) {
        switch ($type) {
            case "fields":
                $dataProject = $this->model->getCurrentDataProject($this->metaDataSubSet);
                if (!is_array($dataProject)) {
                    $dataProject = json_decode($dataProject, TRUE);
                }
                //Añade un campo en el primer nivel de la estructura de datos
                $name = "treballEquipEAF";
                $value = false;
                $dataProject = $this->addNewField($dataProject, $name, $value);

                $status = $this->model->setDataProject(json_encode($dataProject), "Upgrade fields: version ".($ver-1)." to $ver (simultànea a l'actualització de 23 a 24 de templates)", '{"fields":'.$ver.'}');
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

                if (($status = !empty($doc))) {
                    $this->model->setRawProjectDocument($filename, $doc, "Upgrade templates: version ".($ver-1)." to $ver", $ver);
                }
                break;
        }
        return $status;
    }

}
