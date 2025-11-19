<?php
/**
 * upgrader_1: Transforma el archivo continguts.txt de los proyectos 'ptfploe'
 *             desde la versión 0 a la versión 1
 * @author rafael <rclaver@xtec.cat>
 * @adaptacio marjose
*/
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_LIB_IOC')) define('DOKU_LIB_IOC', DOKU_INC."lib/lib_ioc/");
require_once DOKU_LIB_IOC . "upgrader/CommonUpgrader.php";

class upgrader_41 extends ProgramacionsCommonUpgrader {

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


                $txt_v40 = $this->model->getRawProjectTemplate("continguts", 40);
                $txt_v41 = $this->model->getRawProjectTemplate("continguts", 41);
                $l40_l41_v40 = $this->substringFromLineToLineAsPattern($txt_v40, 40, 41);//el que busca
                $l40_l41_v41 = $this->substringFromLineToLine($txt_v41,40, 41);//on ho substitueix
                $l212_l220_v40 = $this->substringFromLineToLineAsPattern($txt_v40, 212, 220);//el que busca
                $l212_l222_v41 = $this->substringFromLineToLine($txt_v41,212, 222);//on ho substitueix
                
                $aTokRep = [
                    [$l40_l41_v40, $l40_l41_v41],
                    [$l212_l220_v40, $l212_l222_v41]
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