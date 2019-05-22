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
                //Transforma los datos del proyecto "ptfplogse" desde la estructura de la versión 0 a la versión 1
                $dataProject = $this->model->getMetaDataProject($this->metaDataSubSet);
                if (!is_array($dataProject)) {
                    $dataProject = json_decode($dataProject, TRUE);
                }
                //Añade el campo 'hiHaRecuperacio' a la tabla 'datesJT'
                $dataProject = $this->addFieldInMultiRow($dataProject, "datesJT", "hiHaRecuperacio", TRUE);
                $this->model->setDataProject(json_encode($dataProject), "Upgrade: version 0 to 1");
                $status = TRUE;
                break;

            case "templates":
                //Primera parte: modificación del fichero de proyecto (el .txt que está en data/pages/ y que, originalmente, proviene de una plantilla)
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

                //Segunda parte: modificación de los datos del proyecto (archivo .mdpr que está en data/mdprojects/)
                $dataProject = $this->model->getDataProject();
                $dataProject['descripcio'] = "tracta de ".$dataProject['descripcio'];
                $this->model->setDataProject(json_encode($dataProject), "Upgrade: version 0 to 1");

                $status = !empty($dataChanged);
                break;
        }
        return $status;
    }

}
