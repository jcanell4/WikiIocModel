<?php
/**
 * upgrader_1: Transforma los datos del proyecto "ptfplogse"
 *             desde la estructura de la versión 0 a la estructura de la versión 1
 * @author rafael
 */
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_LIB_IOC')) define('DOKU_LIB_IOC', DOKU_INC."lib/lib_ioc/");
require_once DOKU_LIB_IOC . "upgrader/CommonUpgrader.php";

class upgrader_1 extends CommonUpgrader {

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
                    linea 7:
                    buscar
                    Aquest <WIOCCL:IF condition="''crèdit''!={##tipusBlocCredit##}">{##tipusBlocCredit##} del</WIOCCL:IF> crèdit {##credit##} tracta de {##descripcio##}
                    sustituir
                    Aquest <WIOCCL:IF condition="''crèdit''!={##tipusBlocCredit##}">{##tipusBlocCredit##} del</WIOCCL:IF> crèdit {##credit##} {##descripcio##}
                */
                if ($filename===NULL) { //Ojo! Ahora se pasa por parámetro
                    $filename = $this->model->getProjectDocumentName();
                }
                $doc1 = $this->model->getRawProjectDocument($filename);
                $aTokRep[] = ["(Aquest \<WIOCCL:IF condition.*tipusBlocCredit.*tipusBlocCredit.*del.*crèdit.*credit.*)( tracta de )(.*descripcio.*\n)",
                              "$1 $3"];
                $dataChanged = $this->updateTemplateByReplace($doc1, $aTokRep);
                if (!empty($dataChanged)) {
                    $this->model->setRawProjectDocument($filename, $dataChanged, "Upgrade: version 0 to 1");
                }
                return !empty($dataChanged);
        }
    }

}
