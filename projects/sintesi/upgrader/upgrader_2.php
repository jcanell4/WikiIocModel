<?php
/**
 * upgrader_2: Transforma el archivo continguts.txt de los proyectos 'ptfploe'
 *             desde la versión 1 a la versión 2
 * @author rafael
 */
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_LIB_IOC')) define('DOKU_LIB_IOC', DOKU_INC."lib/lib_ioc/");
require_once DOKU_LIB_IOC . "upgrader/CommonUpgrader.php";

class upgrader_2 extends CommonUpgrader {

    protected $model;
    protected $metaDataSubSet;

    public function __construct($model) {
        $this->model = $model;
        $this->metaDataSubSet = $this->model->getMetaDataSubSet();
    }

    public function process($type, $filename=NULL) {
        switch ($type) {
            case "fields":
                $dataProject = $this->model->getMetaDataProject($this->metaDataSubSet);
                $matches=array();
                preg_match("/([MC]\d{2})? *-? *(.+)/", $dataProject["modul"], $matches); 
                if(!empty($matches[1])){
                    $dataProject['modulId']=$matches[1];
                }
                $dataProject['modul'] = $matches[2];

                $this->model->setDataProject(json_encode($dataProject), "Upgrade: version 1 to 2 (canvis en els camps)");

                $ret = TRUE;
                break;
            case "templates":
                if ($filename===NULL) { 
                    $filename = $this->model->getProjectDocumentName();
                }
                $doc = $this->model->getRawProjectDocument($filename)."\n";

                $aTokRep = [
                            [" períodes, de  les ",
                             " {##nomPeriodePlur##}, de  les "]
                           ];
                $doc = $this->updateTemplateByReplace($doc, $aTokRep);

                if (!empty($doc)) {
                    $this->model->setRawProjectDocument($filename, $doc, "Upgrade version 1 to 2");
                }
                $ret = !empty($doc);
        }
        return $ret;
    }

}
