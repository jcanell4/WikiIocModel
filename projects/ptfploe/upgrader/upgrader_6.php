<?php
/**
 * upgrader_6: Transforma los datos del proyecto "ptfploe"
 *             desde la estructura de la versión 5 a la estructura de la versión 6
 * @author rafael
 */
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_LIB_IOC')) define('DOKU_LIB_IOC', DOKU_INC."lib/lib_ioc/");
require_once DOKU_LIB_IOC . "upgrader/CommonUpgrader.php";

class upgrader_6 extends CommonUpgrader {

    protected $model;
    protected $metaDataSubSet;

    public function __construct($model) {
        $this->model = $model;
        $this->metaDataSubSet = $this->model->getMetaDataSubSet();
    }

    public function process($type, $filename=NULL) {
        switch ($type) {
            case "fields":
                break;

            case "templates":
                /*
                    linea 4:
                    buscar
                    Aquest <WIOCCL:IF condition="''mòdul''!={##tipusBlocModul##}">{##tipusBlocModul##} del</WIOCCL:IF> mòdul {##modul##} tracta de {##descripcio##}
                    sustituir
                    Aquest <WIOCCL:IF condition="''mòdul''!={##tipusBlocModul##}">{##tipusBlocModul##} del</WIOCCL:IF> mòdul {##modul##} {##descripcio##}
                */
                if ($filename===NULL) { //Ojo! Ahora se pasa por parámetro
                    $filename = $this->model->getProjectDocumentName();
                }
                $doc1 = $this->model->getRawProjectDocument($filename);
                $aTokRep[] = ["(Aquest \<WIOCCL:IF condition.*tipusBlocModul.*tipusBlocModul.*del.*mòdul.*modul.*)( tracta de )(.*descripcio.*\n)",
                              "$1 $3"];
                $dataChanged = $this->updateTemplateByReplace($doc1, $aTokRep);
                if (!empty($dataChanged)) {
                    $this->model->setRawProjectDocument($filename, $dataChanged, "Upgrade: version 5 to 6");
                }
                return !empty($dataChanged);
        }
    }

}
