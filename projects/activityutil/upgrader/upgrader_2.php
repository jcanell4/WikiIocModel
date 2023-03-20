<?php
/**
 * upgrader_2: Transforma los datos del proyecto "activityutil"
 *             desde la estructura de la versión 0 a la estructura de la versión 1
 * @author rafael
 */
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_LIB_IOC')) define('DOKU_LIB_IOC', DOKU_INC."lib/lib_ioc/");
require_once DOKU_LIB_IOC . "upgrader/CommonUpgrader.php";

class upgrader_2 extends CommonUpgrader {
    
  
    function extraerContenidoDelimitado2($cadena) {
        // Descartar la parte de texto delimitada por los patrones "[##" y "##]"
        /*El patrón utilizado es /\[\#\#.*?\#\#\]/s, 
         * que busca una cadena que comienza con "[##" y termina con "##]" 
         * y que tiene cualquier cantidad de caracteres entre ellos. 
         * El modificador /s indica que el patrón también debe coincidir con saltos de línea.
         * */
     
        $patron = '/\[\#\#.*?\#\#\]/s';
       
        $cadena = preg_replace($patron, '', $cadena);
        

        // Extraer el texto delimitado por los patrones "{{section>" y "#"
        /*El patrón utilizado es /\{\{section\>(.*?)\#\}/s, 
         * que busca una cadena que comienza con "{{section>" y termina con "#" 
         * y que tiene cualquier cantidad de caracteres entre ellos. 
         * El modificador /s indica que el patrón también debe coincidir con saltos de línea. 
         * 
         */
        $patron = '/\{\{section\>(.*?)\#/s';
        preg_match($patron, $cadena, $matches);
        if (isset($matches[1])) {
            return $matches[1];
        } else {
            return false;
        }
    }
    
    /*
     * Marjose. Codi alternatiu per extreure nom de document delimitat per patrons
     * 
        //Per cada contingut, optenir el nom del document relacionat
        //::include:
        //{{section>fp:das:m01:u1:a1:continguts#     }}
        //:::
        //El nom del document s'identifica per: fp:das:m01:u1:a1:continguts
        //
        //Extracts the file name identified by starting and ending patterns, avoiding comments. 
        $startPattern="{{section>";
        $endPattern="#";
        $commentFi="##]";
        //gets starting position of file name
        if(($commentFi_pos = strpos($cadena, $commentFi))=== FALSE){
            $start_pos = strpos($cadena, $startPattern);
        }else{
            $start_pos = strpos($cadena, $startPattern, $commentFi_pos+strlen($commentFi));
        }
        //gets ending position
        $end_pos = strpos($cadena, $endPattern, $start_pos);
        //extracts and return content
        if (($start_pos !== false) && ($end_pos !== false)) {
            $reldocNames[] = substr($cadena, $start_pos+strlen($startPattern), $end_pos-($start_pos+strlen($startPattern)));
        } 
     */

    public function process($type, $ver, $filename=NULL) {
        switch ($type) {
            case "fields":
                $dataProject = $this->model->getCurrentDataProject($this->metaDataSubSet);
                if (!is_array($dataProject)) {
                    $dataProject = json_decode($dataProject, TRUE);
                }
                //opcionalment seria el mateix fer: 
                //$dataProject = IocCommon::toArrayThroughArrayOrJson($dataProject)
                
                //marjose
                //EMPLENAR LA NOVA DADA DEFINIDA AL CONFIGMAIN - FILE_DEPENDENCES
                //Convert document data from String in JSON format to array.
                $relatedDocs = IocCommon::toArrayThroughArrayOrJson($dataProject[documents]);
                foreach ($relatedDocs as $oneRelDoc) {
                    //get the document information (long description)               
                    $cadena = $this->model->getRawProjectDocument($oneRelDoc[nom]);
                    $patro = '/\[\#\#.*?\#\#\]/s'; //whole text in-between [## i ##]
                    $cadena = preg_replace($patro, '', $cadena); //delete it
                    $patron = '/\{\{section\>(.*?)\#/s'; //text in-between {{section and #
                    preg_match($patron, $cadena, $matches);  //extract it to matches
                    $reldocNames[] = $matches[1];                  
                }
                //avoid repetitive names
                $reldocNames = array_unique($reldocNames);               
                //Save at dataProject - fileDependences the array with related file names. 
                $dataProject = $this->addNewField($dataProject, "file_dependences",$reldocNames);
                //Update version for fields
                $ret = $this->model->setDataProject(json_encode($dataProject), "Upgrade fields: version ".($ver-1)." to $ver", '{"fields":'.$ver.'}');             
                break;

            case "templates":
                $ret = TRUE;
                break;
        }

        return $ret;
    }

}
