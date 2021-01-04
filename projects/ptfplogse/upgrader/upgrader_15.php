<?php
/**
 * upgrader_15: Transforma el archivo continguts.txt de los proyectos 'ptfplogse'
 *             desde la versión 14 a la versión 15
 * @author rafael
 */
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_LIB_IOC')) define('DOKU_LIB_IOC', DOKU_INC."lib/lib_ioc/");
require_once DOKU_LIB_IOC . "upgrader/CommonUpgrader.php";

class upgrader_15 extends CommonUpgrader {

    protected $model;
    protected $metaDataSubSet;

    public function __construct($model) {
        $this->model = $model;
        $this->metaDataSubSet = $this->model->getMetaDataSubSet();
    }

    public function process($type, $ver, $filename=NULL) {
        switch ($type) {
            case "fields":
                $ret = TRUE;
                break;
            
            case "templates":
                if ($filename===NULL) { //Ojo! Ahora se pasa por parámetro
                    $filename = $this->model->getProjectDocumentName();
                }
                $doc = $this->model->getRawProjectDocument($filename)."\n";

                $aTokRep = [
                            ["(Aquest <WIOCCL:IF condition=\"''crèdit''!=\{##tipusBlocCredit##\}\">\{##tipusBlocCredit##\} del<\/WIOCCL:IF> crèdit )(\{##credit##\} \{##descripcio##\})",
                             "$1{##creditId##} $2"]
                           ];
                $doc = $this->updateTemplateByReplace($doc, $aTokRep);

                if (!empty($doc)) {
                    $this->model->setRawProjectDocument($filename, $doc, "Upgrade version 14 to 15");
                }
                $ret = !empty($doc);
        }
        return $ret;
    }

}
