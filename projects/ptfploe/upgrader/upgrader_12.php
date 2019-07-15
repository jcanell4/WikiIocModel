<?php
/**
 * upgrader_12: Transforma el archivo continguts.txt del proyecto "ptfploe" desde la versión 11 a la versión 12
 *              sustituye, en el doc del usuario, el contenido incluido entre los tags protected
 *              por el contenido de los tags protected de la nueva plantilla
 * @culpable rafael 09-07-2019
 */
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_LIB_IOC')) define('DOKU_LIB_IOC', DOKU_INC . "lib/lib_ioc/");
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_INC . "lib/plugins/wikiiocmodel/");
define('WIKI_IOC_PROJECT', WIKI_IOC_MODEL."projects/ptfploe/");
require_once DOKU_LIB_IOC . "upgrader/CommonUpgrader.php";

class upgrader_12 extends CommonUpgrader {

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
                $doc = $this->model->getRawProjectDocument($filename);

                $plantilla = @file_get_contents(WIKI_IOC_PROJECT."metadata/plantilles/continguts.txt.v12");
//
                //actualiza el doc del usuario en base a la plantilla
                $doc = $this->updateDocFromTemplateUsingProtectecTags($plantilla, $doc);


                $aTokRep = [
                    [
                        "Es recomana cursar-lo el semestre <WIOCCL:IF condition=\"{##semestre##}==1\">{##itinerariRecomanatS1##}<\/WIOCCL:IF><WIOCCL:IF condition=\"{##semestre##}==2\">{##itinerariRecomanatS2##}<\/WIOCCL:IF> de l'itinerari formatiu i suposa una \*\*dedicació setmanal mínima de {##dedicacio##} h\.\*\*",
                        "###:\n<WIOCCL:CHOOSE id=\"itineraris\" lExpression=\"{#_ARRAY_LENGTH({##itinerarisRecomanats##})_#}\">
<WIOCCL:CASE forchoose=\"itineraris\" rExpression=\"0\">Suposa una **dedicació setmanal mínima de {##dedicacio##} h.**</WIOCCL:CASE>
<WIOCCL:CASE forchoose=\"itineraris\" rExpression=\"1\">
<WIOCCL:SET var=\"itinerari\" type=\"literal\" value=\"{##itinerarisRecomanats[0]##}\">
Es recomana cursar-lo el semestre <WIOCCL:IF condition=\"{##semestre##}==1\">{##itinerari[itinerariRecomanatS1]##}</WIOCCL:IF><WIOCCL:IF condition=\"{##semestre##}==2\">{##itinerari[itinerariRecomanatS2]##}</WIOCCL:IF> de l'itinerari formatiu i suposa una **dedicació setmanal mínima de {##dedicacio##} h.**
</WIOCCL:SET>
</WIOCCL:CASE>
<WIOCCL:DEFAULTCASE forchoose=\"itineraris\">
Es recomana cursar-lo:
<WIOCCL:FOREACH var=\"item\" array=\"{##itinerarisRecomanats##}\">
  * Semestre <WIOCCL:IF condition=\"{##semestre##}==1\">{##item[itinerariRecomanatS1]##}</WIOCCL:IF><WIOCCL:IF condition=\"{##semestre##}==2\">{##item[itinerariRecomanatS2]##}</WIOCCL:IF> del crèdit {##item[mòdul]##}.
</WIOCCL:FOREACH>

Suposa una **dedicació setmanal mínima de {##dedicacio##} h.**
</WIOCCL:CASE>
</WIOCCL:CHOOSE>\n:###"
                    ]
                ];
                $doc = $this->updateTemplateByReplace($doc, $aTokRep);

                if (!empty($doc)) {
                    $this->model->setRawProjectDocument($filename, $doc, "Upgrade: version 11 to 12");
                }
                $status = !empty($doc);
        }
        return $status;
    }

}
