<?php
/**
 * upgrader_1: Transforma la estructura de datos y el archivo continguts.txt de los proyectos 'ptce'
 *             desde la versión 0 a la versión 1
 * @author rafael
 * @adapter marjose
 */
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_LIB_IOC')) define('DOKU_LIB_IOC', DOKU_INC."lib/lib_ioc/");
require_once DOKU_LIB_IOC . "upgrader/CommonUpgrader.php";

class upgrader_1 extends CommonUpgrader {

    public function process($type, $ver, $filename=NULL) {
        switch ($type) {
            case "fields":
                //Transforma los datos del proyecto desde la estructura de la versión $ver a la versión $ver+1
                $ret = true;
                break;
          

            case "templates":
     
                //Transforma el archivo continguts.txt del proyecto desde la versión $ver a la versión $ver+1
                if ($filename===NULL) { 
                    $filename = $this->model->getProjectDocumentName();
                }
                $doc = $this->model->getRawProjectDocument($filename);
                
               
                $txt_v0 = $this->model->getRawProjectTemplate("continguts", 0);
                $txt_v1 = $this->model->getRawProjectTemplate("continguts", 1);
                $l11_l11_v0 =  $this->substringFromLineToLineAsPattern($txt_v0, 11, 11);
                $l11_l11_v1 = $this->substringFromLineToLine($txt_v1,11, 11);
                $l31_l31_v0 =  $this->substringFromLineToLineAsPattern($txt_v0, 31, 31);
                $l31_l31_v1 = $this->substringFromLineToLine($txt_v1,31, 31);
                $l66_l66_v0 =  $this->substringFromLineToLineAsPattern($txt_v0, 66, 66);//el que busca
                $l66_l66_v1 = $this->substringFromLineToLine($txt_v1, 66, 66);//on ho substitueix
                $l92_l92_v0 =  $this->substringFromLineToLineAsPattern($txt_v0, 92, 92);
                $l92_l92_v1 = $this->substringFromLineToLine($txt_v1, 92, 92);    
                $aTokRep = [
                    [$l11_l11_v0, $l11_l11_v1],
                    [$l31_l31_v0, $l31_l31_v1],
                    [$l66_l66_v0, $l66_l66_v1],
                    [$l92_l92_v0, $l92_l92_v1]
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
