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

                $aTokRep = [["El  coodinador del mòdul és", "La coordinació és a càrrec de"],
                            [", el professorat és ", " i la docència de  "],
                            ["(Es)( segueix una metodologia)", "Se$2"],
                            ["tipus\\s*\\^  eina\\s*\\^  opcionalitat\\s*\\^  puntuable", "Tipus  ^  Eina  ^  Opcionalitat  ^  Puntuable"],
                            ["Temporalització", "Temporització"],
                            ["(::table:T02)(\\n  :title:Unitats formatives)", "$1a$2"],
                            ["TÍTOL UNITAT FORMATIVA", "Títol unitat formativa"],
                            ["\\^BLOC \\{##ind##\\}\\^\\^  Durada  \\^", "^BLOC {##ind##}^^^"],
                            ["Cada unitat es divideix de la següent manera","Cada unitat es divideix en els apartats que s'indiquen a continuació"],
                            ["\\{##itemc\\[tipus període\\]##\\} \\{##itemc\\[període\\]##\\}:\\{##itemc\\[descripció període\\]##\\}","{##itemc[tipus període]##} {##itemc[període]##}: {##itemc[descripció període]##} "],
                            ["\\{##itemu\\[hores\\]##\\}h","{##itemu[hores]##} h"],
                            ["La vostra data i hora de la PAF es comunicarà al","La data i l'hora de la PAF es comunicarà des del"],
                            [" UNITAT FORMATIVA \\{"," Unitat formativa {"],
                            ["Només es podrà presentar a la PAF 2, l'alumnat que no s'hagi presentat a la PAF1, o que havent-s'hi presentat, no hagi superat la UF.","Només es poden presentar a la PAF2 els alumnes que no s'hagin presentat a la PAF1 o que, havent-s'hi presentat, no hagin superat la UF."],
                            ["<WIOCCL:IF .*  \\* Disposar d'una qualificació de la JT superior a \\{##notaMinimaJT##\\}\\..*<\\/WIOCCL:IF>", "Per cada UF, s'ofereixen dues convocatòries cada semestre: convocatòria PAF1 i convocatòria PAF2 (consulteu dates clau a la taula::table:T10:). Només es poden presentar a la PAF2 els alumnes que no s'hagin presentat a la PAF1 o que, havent-s'hi presentat, no hagin superat la UF.","s"]
                           ];

                $dataChanged = $this->updateTemplateByReplace($doc, $aTokRep);

                $aTokRep = [["PAF 1","PAF1"],
                            ["PAF 2","PAF2"]
                           ];

                $dataChanged = $this->updateTemplateByReplace($dataChanged, $aTokRep);

                if (($ret = !empty($dataChanged))) {
                    $this->model->setRawProjectDocument($filename, $dataChanged, "Upgrade templates: version ".($ver-1)." to $ver", $ver);
                }
                break;
        }
        return $ret;
    }

}
