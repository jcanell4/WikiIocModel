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
            
                //Transforma los datos del proyecto "ptce" desde la estructura de la versión 0 a la versión 1
                /*$dataProject = $this->model->getCurrentDataProject($this->metaDataSubSet);
                if (!is_array($dataProject)) {
                    $dataProject = json_decode($dataProject, TRUE);
                }*/
                //Añade el campo 'hiHaRecuperacio' a la tabla 'datesJT'
                //$dataProject = $this->addFieldInMultiRow($dataProject, "datesJT", "hiHaRecuperacio", TRUE);
                
                //Cambia el nombre del campo
                /*
                $dataProject = $this->changeFieldName($dataProject, "dataPaf11", "dataPv1");
                $dataProject = $this->changeFieldName($dataProject, "dataPaf12", "dataPv2");
                $dataProject = $this->changeFieldName($dataProject, "dataPaf21", "dataPaf1");
                $dataProject = $this->changeFieldName($dataProject, "dataPaf22", "dataPaf2");
                $dataProject = $this->changeFieldName($dataProject, "dataQualificacioPaf1", "dataQualificacioPv");
                $dataProject = $this->changeFieldName($dataProject, "dataQualificacioPaf2", "dataQualificacioPaf");
                
                $ret = $this->model->setDataProject(json_encode($dataProject), "Upgrade fields: version ".($ver-1)." to $ver", '{"fields":'.$ver.'}');
                break;
                
                */
                
                
                
                /*
                 * 
                //Transforma los datos del proyecto "ptfploe" desde la estructura de la versión 8 a la versión 9
                $dataProject = $this->model->getCurrentDataProject($this->metaDataSubSet);
                if (!is_array($dataProject)) {
                    $dataProject = json_decode($dataProject, TRUE);
                }
                //Cambia el nombre del campo
                $dataProject = $this->changeFieldName($dataProject, "dataPaf1", "dataPaf11");
                $dataProject = $this->changeFieldName($dataProject, "dataPaf2", "dataPaf21");

                //Añade un campo en el primer nivel de la estructura de datos
                $dataProject = $this->addNewField($dataProject, "dataPaf12", $dataProject['dataPaf11']);
                $dataProject = $this->addNewField($dataProject, "dataPaf22", $dataProject['dataPaf21']);

                $status = $this->model->setDataProject(json_encode($dataProject), "Upgrade fields: version ".($ver-1)." to $ver (simultànea a la actualització de 25 a 26 de templates)", '{"fields":'.$ver.'}');
                break;
                 */

            case "templates":
     
                //Transforma el archivo continguts.txt del proyecto desde la versión $ver a la versión $ver+1
                if ($filename===NULL) { 
                    $filename = $this->model->getProjectDocumentName();
                }
                $doc = $this->model->getRawProjectDocument($filename);

                //Línies 57-59. Es canviaen per Línies 57-59.
                
                $txt_v0 = $this->model->getRawProjectTemplate("continguts", 0);
                $txt_v1 = $this->model->getRawProjectTemplate("continguts", 1);
                $l57_l59_v0 =  $this->substringFromLineToLineAsPattern($txt_v0, 57, 59);
                $l57_l59_v1 = $this->substringFromLineToLine($txt_v1,57, 59);
                $l74_l136_v0 =  $this->substringFromLineToLineAsPattern($txt_v0, 74, 136);//el que busca
                $l74_l103_v1 = $this->substringFromLineToLine($txt_v1, 74, 103);//on ho substitueix
                $l336_l415_v0 =  $this->substringFromLineToLineAsPattern($txt_v0, 336, 415);
                $l303_l305_v1 = $this->substringFromLineToLine($txt_v1, 303, 305);    
                $l431_l468_v0 =  $this->substringFromLineToLineAsPattern($txt_v0, 431, 468);
                $l321_l359_v1 = $this->substringFromLineToLine($txt_v1, 321, 359); 
                $aTokRep = [
                    [$l57_l59_v0, $l57_l59_v1],
                    [$l74_l136_v0, $l74_l103_v1],
                    [$l336_l415_v0, $l303_l305_v1],
                    [$l431_l468_v0, $l321_l359_v1]
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
