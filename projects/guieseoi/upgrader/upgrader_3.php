<?php
/**
 * upgrader_3: Transforma la estructura de datos y el archivo continguts.txt de los proyectos 'guieseoi'
 *             desde la versión 2 a la versión 3
 * @author rafael
 * @adapter marjose
 */
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_LIB_IOC')) define('DOKU_LIB_IOC', DOKU_INC."lib/lib_ioc/");
require_once DOKU_LIB_IOC . "upgrader/CommonUpgrader.php";

class upgrader_3 extends CommonUpgrader {

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

                $txt_v2 = $this->model->getRawProjectTemplate("continguts", 2);
                $txt_v3 = $this->model->getRawProjectTemplate("continguts", 3);
                
                $l20_l22_v2 = $this->substringFromLineToLineAsPattern($txt_v2, 20, 22);
                $l20_l22_v3 = $this->substringFromLineToLine($txt_v3,20, 22);
                $l68_l81_v2 = $this->substringFromLineToLineAsPattern($txt_v2, 68, 81);
                $l68_l91_v3 = $this->substringFromLineToLine($txt_v3,68, 91);
   
                $aTokRep = [
                    [$l20_l22_v2, $l20_l22_v3],
                    [$l68_l81_v2, $l68_l91_v3]
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
