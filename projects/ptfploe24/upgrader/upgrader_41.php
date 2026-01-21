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

                //upg41
                /*
                substringFromLineToLineAsPattern donats uns continguts.txt.v? crea un patro a partir del que recupera de la linia inicial - segon parametre- a la linia final -tercer parametre- i crea un patro
                substringFromLineToLine fa el mateix, sense crear el patró
                 * Aplico les diferències que trobo. I les aplico al txt_v40. Es seleccionen blocs no editables.
                 *  */
                $txt_v40 = $this->model->getRawProjectTemplate("continguts", 40);
                $txt_v41 = $this->model->getRawProjectTemplate("continguts", 41);
                
                $l10_l10_v40 = $this->substringFromLineToLineAsPattern($txt_v40, 10, 10);//el que busca
                $l10_l11_v41 = $this->substringFromLineToLine($txt_v41,10, 11);//on ho substitueix
                $l40_l40_v40 = $this->substringFromLineToLineAsPattern($txt_v40, 40, 40);//el que busca
                $l41_l41_v41 = $this->substringFromLineToLine($txt_v41,41, 41);//on ho substitueix
                
                $l61_l92_v40 = $this->substringFromLineToLineAsPattern($txt_v40, 61, 92);//el que busca
                $l62_l75_v41 = $this->substringFromLineToLine($txt_v41,62, 75);//on ho substitueix
                
                $l130_l133_v40 = $this->substringFromLineToLineAsPattern($txt_v40, 130, 133);//el que busca
                $l113_l125_v41 = $this->substringFromLineToLine($txt_v41,113, 125);//on ho substitueix
                
                $l165_l165_v40 = $this->substringFromLineToLineAsPattern($txt_v40, 165, 165);//el que busca
                $l157_l157_v41 = $this->substringFromLineToLine($txt_v41,157, 157);//on ho substitueix
                
                $l176_l176_v40 = $this->substringFromLineToLineAsPattern($txt_v40, 176, 176);//el que busca
                $l168_l168_v41 = $this->substringFromLineToLine($txt_v41,168, 168);//on ho substitueix
                              
                $l202_l202_v40 = $this->substringFromLineToLineAsPattern($txt_v40, 202, 202);//el que busca
                $l194_l194_v41 = $this->substringFromLineToLine($txt_v41,194, 194);//on ho substitueix
               
                $l212_l212_v40 = $this->substringFromLineToLineAsPattern($txt_v40, 212, 212);//el que busca
                $l204_l208_v41 = $this->substringFromLineToLine($txt_v41,204, 208);//on ho substitueix    
                
                $l218_l220_v40 = $this->substringFromLineToLineAsPattern($txt_v40, 218, 220);//el que busca
                $l214_l214_v41 = $this->substringFromLineToLine($txt_v41,214, 214);//on ho substitueix
                
                $l227_l237_v40 = $this->substringFromLineToLineAsPattern($txt_v40, 227, 237);//el que busca
                $l221_l231_v41 = $this->substringFromLineToLine($txt_v41,221, 231);//on ho substitueix                
                
                $l246_l337_v40 = $this->substringFromLineToLineAsPattern($txt_v40, 246, 337);//el que busca
                $l240_l495_v41 = $this->substringFromLineToLine($txt_v41,240, 495);//on ho substitueix      
                
                $aTokRep = [
                    [$l10_l10_v40, $l10_l11_v41],
                    [$l40_l40_v40, $l41_l41_v41],
                    [$l61_l92_v40, $l62_l75_v41],
                    [$l130_l133_v40, $l113_l125_v41],                
                    [$l165_l165_v40, $l157_l157_v41],  
                    [$l176_l176_v40, $l168_l168_v41],                      
                    [$l202_l202_v40, $l194_l194_v41],               
                    [$l212_l212_v40, $l204_l208_v41],                   
                    [$l218_l220_v40, $l214_l214_v41],                
                    [$l227_l237_v40, $l221_l231_v41],                                
                    [$l246_l337_v40, $l240_l495_v41] 
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