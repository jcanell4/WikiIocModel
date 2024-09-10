<?php
/**
 * upgrader_38: Transforma el archivo continguts.txt de los proyectos 'ptfploe'
 *             desde la versión 37 a la versión 38
 * @author rafael <rclaver@xtec.cat>
 * @adaptacio marjose
*/
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_LIB_IOC')) define('DOKU_LIB_IOC', DOKU_INC."lib/lib_ioc/");
require_once DOKU_LIB_IOC . "upgrader/CommonUpgrader.php";

class upgrader_38 extends ProgramacionsCommonUpgrader {

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

                //upg35
                /*
                $aTokRep = [["alumn[ea]([,\.:s\s])",
                            "estudiant$1"],
                            ["###:\n  \* És de caràcter.+?((?:\n  \* .*?)+?)?(\n  \* )(:###)(Té una ponderació en la \*\*qualificació final\*\*.+?.)\n###:",
                             "\n  * És de caràcter <WIOCCL:IF condition=\"{##treballEquipEAF##}==true\">grupal</WIOCCL:IF><WIOCCL:IF condition=\"{##treballEquipEAF##}!=true\">individual</WIOCCL:IF>.$2$4###:$1"],
                            ["  \* La UF no té assignada cap EAF o si en té alguna, es disposa d'una qualificació d'aquesta, superior a \{##notaMinimaEAF##\}\.",
                             "  * La UF no té assignada cap EAF o si en té, la qualificació de l'EAF és superior a {##notaMinimaEAF##}."]
                           ];
                               
                $aTokRep = [":###====== Temporalització de continguts ======###:"];
                $aTokRep = [["^(\|  {##item\[id\]##}  \|  )({##item\[inscripció\]##})(  \|  {#_DATE\(\"{##item\[llista provisional]##}\"\)_#}  \|  {#_DATE\(\"{##item\[llista definitiva]##}\"\)_#}  \|  {#_DATE\(\"{##item\[data JT]##}\"\)_#}  \|  {#_DATE\(\"{##item\[qualificació]##}\"\)_#}  \|)$",
                            "$1{#_DATE(\"$2\")_#}$3",
                            "s"],
                           ["^(\|  {##item\[id\]##}  \|  )({##item\[inscripció recuperació\]##})(  \|)",
                            "$1{#_DATE(\"$2\")_#}$3",
                            "s"]
                           ];
                 
                 */
                 $aTokRep = [["Temporalització",
                            "Temporització"],
                            ["El  coodinador del mòdul és",
                             "La coordinació és a càrrec de"],
                     [", el professorat és ",
                         " i la docència de  "],
                     ["Es segueix una metodologia",
                         "Se segueix una metodologia"],
                     ["tipus	\\^  eina	 \\^  opcionalitat	 \\^  puntuable",
                         "Tipus	^  Eina	 ^  Opcionalitat	 ^  Puntuable"],
                     [" Temporalització ",
                     " Temporització "],
                     ["Cada unitat es divideix de la següent manera","Cada unitat es divideix en els apartats que s'indiquen a continuació"],
                     ["\\{##itemc\\[tipus període\\]##\\} \\{##itemc\\[període\\]##\\}:\\{##itemc\\[descripció període\\]##\\}","{##itemc[tipus període]##} {##itemc[període]##}: {##itemc[descripció període]##} "],
                     ["\\{##itemu\\[hores\\]##\\}h","{##itemu[hores]##} h"],
                     ["La vostra data i hora de la PAF es comunicarà al","La data i l'hora de la PAF es comunicarà des del"],
                     [" UNITAT FORMATIVA \\{"," Unitat formativa {"],
                     ["PAF 1","PAF1"],
                     ["PAF 2","PAF2"],
                     /*["Només es podrà presentar a la PAF 2, l'alumnat que no s'hagi presentat a la PAF1, o que havent-s'hi presentat, no hagi superat la UF.","Només es poden presentar a la PAF2 els alumnes que no s'hagin presentat a la PAF1 o que, havent-s'hi presentat, no hagin superat la UF."]*/
                     ];

                
                
                $dataChanged = $this->updateTemplateByReplace($doc, $aTokRep);

                if (($ret = !empty($dataChanged))) {
                    $this->model->setRawProjectDocument($filename, $dataChanged, "Upgrade templates: version ".($ver-1)." to $ver", $ver);
                }
                break;
        }
        return $ret;
    }

}
