<?php
/**
 * upgrader_1: Transforma el archivo continguts.txt de los proyectos 'ptfploe24'
 *             desde la versión 40 a la versión 41
 * @author rafael <rclaver@xtec.cat>
 * @adaptacio marjose
*/
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_LIB_IOC')) define('DOKU_LIB_IOC', DOKU_INC."lib/lib_ioc/");
require_once DOKU_LIB_IOC . "upgrader/CommonUpgrader.php";

class upgrader_43 extends ProgramacionsCommonUpgrader {

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

                //upg41
                /*
                substringFromLineToLineAsPattern donats uns continguts.txt.v? crea un patro a partir del que recupera de la linia inicial - segon parametre- a la linia final -tercer parametre- i crea un patro
                substringFromLineToLine fa el mateix, sense crear el patró
                 * Aplico les diferències que trobo. I les aplico al txt_v40. Es seleccionen blocs no editables.
                 *  */
                $txt_v42 = $this->model->getRawProjectTemplate("continguts", 42);
                $txt_v43 = $this->model->getRawProjectTemplate("continguts", 43);
                
                $l161_l163_v42 = $this->substringFromLineToLineAsPattern($txt_v42, 161, 163);//el que busca
                $l161_l164_v43 = $this->substringFromLineToLine($txt_v43,161, 164);//on ho substitueix
             
                $l196_l197_v42 = $this->substringFromLineToLineAsPattern($txt_v42, 196, 197);//el que busca
                $l197_l199_v43 = $this->substringFromLineToLine($txt_v43,197, 199);//on ho substitueix
                 
                
                $aTokRep = [
                    [$l161_l163_v42, $l161_l164_v43],
                    [$l196_l197_v42, $l197_l199_v43]
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