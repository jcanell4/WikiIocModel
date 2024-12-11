<?php
/**
 * upgrader_2: Transforma la estructura de datos y el archivo continguts.txt de los proyectos 'guieseoianual'
 *             desde la versión 1 a la versión 2
 * @author rafael
 * @adapter marjose
 */
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_LIB_IOC')) define('DOKU_LIB_IOC', DOKU_INC."lib/lib_ioc/");
require_once DOKU_LIB_IOC . "upgrader/CommonUpgrader.php";

class upgrader_2 extends CommonUpgrader {

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
                
                
                $txt_v1 = $this->model->getRawProjectTemplate("continguts", 1);
                $txt_v2 = $this->model->getRawProjectTemplate("continguts", 2);
                $l49_l64_v1 =  $this->substringFromLineToLineAsPattern($txt_v1, 49, 64);
                $l49_l49_v2 = $this->substringFromLineToLine($txt_v2,49, 49);
   
                $aTokRep = [
                    [$l49_l64_v1, $l49_l49_v2]
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
