<?php
/**
 * upgrader_5: Transforma los datos del proyecto "ptfplogse"
 *             desde la estructura de la versión 4 a la estructura de la versión 5
 * @culpable rafael 21-06-2019
 */
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_LIB_IOC')) define('DOKU_LIB_IOC', DOKU_INC . "lib/lib_ioc/");
require_once DOKU_LIB_IOC . "upgrader/CommonUpgrader.php";

class upgrader_7 extends CommonUpgrader {

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

                $aTokRep = [
                    [
                        "<WIOCCL:FOREACH var=\"item\" array=\"{##datesAC##}\" filter=\"{##item\[unitat didàctica\]##} in {##unitsFromBlock##}\">\n\| {##item\[id\]##} \|  {##item\[unitat didàctica\]##}  \| {#_DATE\(\"{##item\[enunciat\]##}\"\)_#} \| {#_DATE\(\"{##item\[lliurament\]##}\"\)_#} <WIOCCL:IF condition=\"{##hiHaSolucioPerAC##}==true\">\| <WIOCCL:IF condition=\"{##item\[hiHaSolucio\]##}==true\">{#_DATE\(\"{##item\[solució\]##}\"\)_#}<\/WIOCCL:IF><WIOCCL:IF condition=\"{##item\[hiHaSolucio\]##}==false\">--<\/WIOCCL:IF> <\/WIOCCL:IF>\| {#_DATE\(\"{##item\[qualificació]##}\"\)_#} \|\n<\/WIOCCL:FOREACH>",
                        "<WIOCCL:SET var=\"previousEAC\" type=\"literal\" value=\"\">
<WIOCCL:FOREACH var=\"item\" array=\"{##datesAC##}\" filter=\"{##item[unitat didàctica]##} in {##unitsFromBlock##}\">
<WIOCCL:CHOOSE id=\"selector\" lExpression=\"{##previousEAC##}\" rExpression=\"{##item[id]##}\">
<WIOCCL:CASE forchoose=\"selector\" relation=\"==\">
| ::: |  {##item[unitat didàctica]##}  | ::: | ::: <WIOCCL:IF condition=\"{##hiHaSolucioPerAC##}==true\">| ::: </WIOCCL:IF>| ::: |
</WIOCCL:CASE>
<WIOCCL:DEFAULTCASE forchoose=\"selector\">
| {##item[id]##} |  {##item[unitat didàctica]##}  | {#_DATE(\"{##item[enunciat]##}\")_#} | {#_DATE(\"{##item[lliurament]##}\")_#} <WIOCCL:IF condition=\"{##hiHaSolucioPerAC##}==true\">| <WIOCCL:IF condition=\"{##item[hiHaSolucio]##}==true\">{#_DATE(\"{##item[solució]##}\")_#}</WIOCCL:IF><WIOCCL:IF condition=\"{##item[hiHaSolucio]##}==false\">--</WIOCCL:IF> </WIOCCL:IF>| {#_DATE(\"{##item[qualificació]##}\")_#} |
</WIOCCL:DEFAULTCASE>
<WIOCCL:RESET var=\"previousEAC\" type=\"literal\" value=\"{##item[id]##}\"></WIOCCL:RESET>
</WIOCCL:CHOOSE>
</WIOCCL:FOREACH>
</WIOCCL:SET>"
                    ],
                    [
                        "<WIOCCL:FOREACH var=\"item\" array=\"{##datesEAF##}\" filter=\"{##item\[unitat didàctica\]##} in {##unitsFromBlock##}\">\n\| {##item\[id\]##} \|  {##item\[unitat didàctica\]##}  \| {#_DATE\(\"{##item\[enunciat\]##}\"\)_#} \| {#_DATE\(\"{##item\[lliurament]##}\"\)_#} <WIOCCL:IF condition=\"{##hiHaSolucioPerEAF##}==true\">\| <WIOCCL:IF condition=\"{##item\[hiHaSolucio\]##}==true\">{#_DATE\(\"{##item\[solució recuperació\]##}\"\)_#}<\/WIOCCL:IF><WIOCCL:IF condition=\"{##item\[hiHaSolucio\]##}==false\">--<\/WIOCCL:IF> <\/WIOCCL:IF>\| {#_DATE\(\"{##item\[qualificació\]##}\"\)_#} \|\n<\/WIOCCL:FOREACH>",
                        "<WIOCCL:SET var=\"previousEAF\" type=\"literal\" value=\"\">
<WIOCCL:FOREACH var=\"item\" array=\"{##datesEAF##}\" filter=\"{##item[unitat didàctica]##} in {##unitsFromBlock##}\">
<WIOCCL:CHOOSE id=\"selector\" lExpression=\"{##previousEAF##}\" rExpression=\"{##item[id]##}\">
<WIOCCL:CASE forchoose=\"selector\" relation=\"==\">
| ::: |  {##item[unitat didàctica]##}  | ::: | ::: <WIOCCL:IF condition=\"{##hiHaSolucioPerEAF##}==true\">| ::: </WIOCCL:IF>| ::: |
</WIOCCL:CASE>
<WIOCCL:DEFAULTCASE forchoose=\"selector\">
| {##item[id]##} |  {##item[unitat didàctica]##}  | {#_DATE(\"{##item[enunciat]##}\")_#} | {#_DATE(\"{##item[lliurament]##}\")_#} <WIOCCL:IF condition=\"{##hiHaSolucioPerEAF##}==true\">| <WIOCCL:IF condition=\"{##item[hiHaSolucio]##}==true\">{#_DATE(\"{##item[solució recuperació]##}\")_#}</WIOCCL:IF><WIOCCL:IF condition=\"{##item[hiHaSolucio]##}==false\">--</WIOCCL:IF> </WIOCCL:IF>| {#_DATE(\"{##item[qualificació]##}\")_#} |
</WIOCCL:DEFAULTCASE>
<WIOCCL:RESET var=\"previousEAF\" type=\"literal\" value=\"{##item[id]##}\"></WIOCCL:RESET>
</WIOCCL:CHOOSE>
</WIOCCL:FOREACH>
</WIOCCL:SET>"
                    ],
                    [
                        "<WIOCCL:FOREACH var=\"item\" array=\"{##datesEAF##}\" filter=\"{##item\[unitat didàctica\]##} in {##unitsFromBlock##}\">\n\| {##item\[id\]##} \|  {##item\[unitat didàctica\]##}  <WIOCCL:IF condition=\"{##hiHaEnunciatRecuperacioPerEAF##}==true\">\| <WIOCCL:IF condition=\"{##item\[hiHaEnunciatRecuperacio\]##}==true\">{#_DATE\(\"{##item\[enunciat recuperació\]##}\"\)_#}<\/WIOCCL:IF><WIOCCL:IF condition=\"{##item\[hiHaEnunciatRecuperacio\]##}==false\">--<\/WIOCCL:IF> <\/WIOCCL:IF>\| {#_DATE\(\"{##item\[lliurament recuperació\]##}\"\)_#} <WIOCCL:IF condition=\"{##hiHaSolucioPerEAF##}==true\">\| <WIOCCL:IF condition=\"{##item\[hiHaSolucio\]##}==true\">{#_DATE\(\"{##item\[solució recuperació\]##}\"\)_#}<\/WIOCCL:IF><WIOCCL:IF condition=\"{##item\[hiHaSolucio\]##}==false\">--<\/WIOCCL:IF> <\/WIOCCL:IF>\| {#_DATE\(\"{##item\[qualificació recuperació\]##}\"\)_#} \|\n<\/WIOCCL:FOREACH>",
                        "<WIOCCL:SET var=\"previousEAF\" type=\"literal\" value=\"\">
<WIOCCL:FOREACH var=\"item\" array=\"{##datesEAF##}\" filter=\"{##item[unitat didàctica]##} in {##unitsFromBlock##}\">
<WIOCCL:CHOOSE id=\"selector\" lExpression=\"{##previousEAF##}\" rExpression=\"{##item[id]##}\">
<WIOCCL:CASE forchoose=\"selector\" relation=\"==\">
| ::: |  {##item[unitat didàctica]##}  | ::: | ::: <WIOCCL:IF condition=\"{##hiHaSolucioPerEAF##}==true\">| ::: </WIOCCL:IF>| ::: |
</WIOCCL:CASE>
<WIOCCL:DEFAULTCASE forchoose=\"selector\">
| {##item[id]##} |  {##item[unitat didàctica]##}  <WIOCCL:IF condition=\"{##hiHaEnunciatRecuperacioPerEAF##}==true\">| <WIOCCL:IF condition=\"{##item[hiHaEnunciatRecuperacio]##}==true\">{#_DATE(\"{##item[enunciat recuperació]##}\")_#}</WIOCCL:IF><WIOCCL:IF condition=\"{##item[hiHaEnunciatRecuperacio]##}==false\">--</WIOCCL:IF> </WIOCCL:IF>| {#_DATE(\"{##item[lliurament recuperació]##}\")_#} <WIOCCL:IF condition=\"{##hiHaSolucioPerEAF##}==true\">| <WIOCCL:IF condition=\"{##item[hiHaSolucio]##}==true\">{#_DATE(\"{##item[solució recuperació]##}\")_#}</WIOCCL:IF><WIOCCL:IF condition=\"{##item[hiHaSolucio]##}==false\">--</WIOCCL:IF> </WIOCCL:IF>| {#_DATE(\"{##item[qualificació recuperació]##}\")_#} |
</WIOCCL:DEFAULTCASE>
<WIOCCL:RESET var=\"previousEAF\" type=\"literal\" value=\"{##item[id]##}\"></WIOCCL:RESET>
</WIOCCL:CHOOSE>
</WIOCCL:FOREACH>
</WIOCCL:SET>"
                    ]
                ];

                $doc = $this->updateTemplateByReplace($doc, $aTokRep);

                if (!empty($doc)) {
                    $this->model->setRawProjectDocument($filename, $doc, "Upgrade: version 6 to 7");
                }
                $status = !empty($doc);
        }
        return $status;
    }

}
