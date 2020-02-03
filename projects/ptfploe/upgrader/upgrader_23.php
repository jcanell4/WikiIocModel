<?php
/**
 * upgrader_23: Transforma el archivo continguts.txt de los proyectos 'ptfploe'
 *             desde la versión 22 a la versión 23
 * @author rafael
 */
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_LIB_IOC')) define('DOKU_LIB_IOC', DOKU_INC."lib/lib_ioc/");
require_once DOKU_LIB_IOC . "upgrader/CommonUpgrader.php";

class upgrader_23 extends CommonUpgrader {

    protected $model;
    protected $metaDataSubSet;

    public function __construct($model) {
        $this->model = $model;
        $this->metaDataSubSet = $this->model->getMetaDataSubSet();
    }

    public function process($type, $filename=NULL) {
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
                            ["(Aquest <WIOCCL:IF condition=\"''mòdul''!=\{##tipusBlocModul##\}\">\{##tipusBlocModul##\} del<\/WIOCCL:IF> mòdul )(\{##modul##\} \{##descripcio##\})",
                             "$1{##modulId##} $2"]
                           ];
                $doc = $this->updateTemplateByReplace($doc, $aTokRep);

                if (!empty($doc)) {
                    $this->model->setRawProjectDocument($filename, $doc, "Upgrade version 22 to 23");
                }
                $ret = !empty($doc);
        }
        return $ret;
    }

}