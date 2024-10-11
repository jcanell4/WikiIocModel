<?php
/**
 * upgrader_40: Transforma el archivo continguts.txt de los proyectos 'ptfploe'
 *             desde la versión 39 a la versión 40
 * @author rafael <rclaver@xtec.cat>
 * @adaptacio marjose
*/
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_LIB_IOC')) define('DOKU_LIB_IOC', DOKU_INC."lib/lib_ioc/");
require_once DOKU_LIB_IOC . "upgrader/CommonUpgrader.php";

class upgrader_40 extends ProgramacionsCommonUpgrader {

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

                //upg40
                /*
                substringFromLineToLineAsPattern donats uns continguts.txt.v? crea un patro a partir del que recupera de la linia inicial - segon parametre- a la linia final -tercer parametre- i crea un patro
                substringFromLineToLine fa el mateix, sense crear el patró
                 * Aplico les diferències que trobo. I les aplico al txt_v39. Es seleccionen blocs no editables.
                 *  */
                $txt_v39 = $this->model->getRawProjectTemplate("continguts", 39);
                $txt_v40 = $this->model->getRawProjectTemplate("continguts", 40);
                $l312_l312_v39 = $this->substringFromLineToLineAsPattern($txt_v39, 312, 312);//el que busca
                $l312_l312_v40 = $this->substringFromLineToLine($txt_v40,312, 312);//on ho substitueix
                $l475_l554_v39 =  $this->substringFromLineToLineAsPattern($txt_v39, 475, 554);
                $l475_l549_v40 = $this->substringFromLineToLine($txt_v40, 475, 549);
                $l625_l661_v39 =  $this->substringFromLineToLineAsPattern($txt_v39, 625, 661);
                $l620_l663_v40 = $this->substringFromLineToLine($txt_v40, 620, 663);
                
                $aTokRep = [
                    [$l312_l312_v39, $l312_l312_v40],
                    [$l475_l554_v39, $l475_l549_v40],
                    [$l625_l661_v39, $l620_l663_v40]
                ];

                $dataChanged = $this->updateTemplateByReplace($doc, $aTokRep);

                if (($ret = !empty($dataChanged))) {
                    $this->model->setRawProjectDocument("$filename", $dataChanged, "Upgrade templates: version ".($ver-1)." to $ver", $ver);
                }
                break;
        }
        return $ret;
    }

}