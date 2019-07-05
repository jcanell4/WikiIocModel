<?php
/**
 * upgrader_5: Transforma los datos del proyecto "ptfplogse"
 *             desde la estructura de la versión 4 a la estructura de la versión 5
 * @culpable rafael 21-06-2019
 */
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_LIB_IOC')) define('DOKU_LIB_IOC', DOKU_INC . "lib/lib_ioc/");
require_once DOKU_LIB_IOC . "upgrader/CommonUpgrader.php";

class upgrader_6 extends CommonUpgrader {

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
                        "Es recomana cursar-lo el semestre <WIOCCL:IF condition=\"{##semestre##}==1\">{##itinerariRecomanatS1##}<\/WIOCCL:IF><WIOCCL:IF condition=\"{##semestre##}==2\">{##itinerariRecomanatS2##}<\/WIOCCL:IF> de l'itinerari formatiu i suposa una \*\*dedicació setmanal mínima de {##dedicacio##} h\.\*\*",
                        "<WIOCCL:CHOOSE id=\"itineraris\" lExpression=\"{#_ARRAY_LENGTH({##itinerarisRecomanats##})_#}\">
<WIOCCL:CASE forchoose=\"itineraris\" rExpression=\"0\">Suposa una **dedicació setmanal mínima de {##dedicacio##} h.**</WIOCCL:CASE>
<WIOCCL:CASE forchoose=\"itineraris\" rExpression=\"1\">
<WIOCCL:SET var=\"itinerari\" type=\"literal\" value=\"{##itinerarisRecomanats[0]##}\">
Es recomana cursar-lo el semestre <WIOCCL:IF condition=\"{##semestre##}==1\">{##itinerari[itinerariRecomanatS1]##}</WIOCCL:IF><WIOCCL:IF condition=\"{##semestre##}==2\">{##itinerari[itinerariRecomanatS2]##}</WIOCCL:IF> de l'itinerari formatiu i suposa una **dedicació setmanal mínima de {##dedicacio##} h.**
</WIOCCL:SET>
</WIOCCL:CASE>
<WIOCCL:DEFAULTCASE forchoose=\"itineraris\">
Es recomana cursar-lo:
<WIOCCL:FOREACH var=\"item\" array=\"{##itinerarisRecomanats##}\">
  * Semestre <WIOCCL:IF condition=\"{##semestre##}==1\">{##item[itinerariRecomanatS1]##}</WIOCCL:IF><WIOCCL:IF condition=\"{##semestre##}==2\">{##item[itinerariRecomanatS2]##}</WIOCCL:IF> del crèdit {##item[crèdit]##}.
</WIOCCL:FOREACH>

Suposa una **dedicació setmanal mínima de {##dedicacio##} h.**
</WIOCCL:CASE>
</WIOCCL:CHOOSE>"
                    ],
                ];

                $doc = $this->updateTemplateByReplace($doc, $aTokRep);

                if (!empty($doc)) {
                    $this->model->setRawProjectDocument($filename, $doc, "Upgrade: version 5 to 6");
                }
                $status = !empty($doc);
        }
        return $status;
    }

}
