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
    public function process($type, $ver, $filename=NULL) {
        switch ($type) {
            case "fields":
                $dataProject = $this->model->getCurrentDataProject($this->metaDataSubSet);
                if (!is_array($dataProject)) {
                    $dataProject = json_decode($dataProject, TRUE);
                }
                //opcionalment seria el mateix fer: 
                //$dataProject = IocCommon::toArrayThroughArrayOrJson($dataProject)
                
                //marjose: EMPLENAR filedependences AL CONFIGMAIN
                //Convert document data from String in JSON format to array.
                $relatedDocs = IocCommon::toArrayThroughArrayOrJson($dataProject[documents]);
                $fileDepArr = array();

                foreach ($relatedDocs as $oneRelDoc) {
                    //get the document content  
                    $cadena = $this->model->getRawProjectDocument($oneRelDoc[nom]);
                    preg_match_all('/(^{{section>|^{{page>)(.*?):continguts/m', $cadena, $matches);
                    //Fills into fileDepArray the document name and related docs of file dependences                   
                    $fileDepArr[] = (object) ['nomDoc' => $oneRelDoc['nom'], 'relDocs' => array_unique($matches[2])];
                }
                
                //Save at dataProject - fileDependences the array with related file names. 
                $dataProject = $this->addNewField($dataProject, "file_dependences",$fileDepArr);
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
