<?php
/**
 * upgrader_1: Transforma la estructura de datos y el archivo continguts.txt de los proyectos 'ptfploe'
 *             desde la versión 0 a la versión 1
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

    public function process($type, $ver, $filename=NULL) {
        switch ($type) {
            case "fields":
                //Transforma los datos del proyecto "ptfploe" desde la estructura de la versión 0 a la versión 1
                $dataProject = $this->model->getCurrentDataProject($this->metaDataSubSet);
                if (!is_array($dataProject)) {
                    $dataProject = json_decode($dataProject, TRUE);
                }
                //Añade el campo 'hiHaRecuperacio' a la tabla 'datesJT'
                $dataProject = $this->addFieldInMultiRow($dataProject, "datesJT", "hiHaRecuperacio", TRUE);

                $ret = $this->model->setDataProject(json_encode($dataProject), "Upgrade fields: version ".($ver-1)." to $ver", "{'fields':".($ver-1)."}");
                break;

            case "templates":
                // Línia 96.  Es canvia "  :title:Taula Unitats" per "  :title:Apartats"
                // Línia 102. Es canvia "{#_DATE("{##itemc[inici]##}", ".")_#}-{#_DATE("{##itemc[inici]##}" per "{#_DATE("{##itemc[inici]##}", ".")_#}-{#_DATE("{##itemc[final]##}"

                if ($filename===NULL) { //Ojo! Ahora se pasa por parámetro
                    $filename = $this->model->getProjectDocumentName();
                }
                $doc = $this->model->getRawProjectDocument($filename);

                $aTokRep = [[" +:title:Taula Unitats",
                             "  :title:Apartats"],
                            ["{#_DATE\(\"{##itemc\[inici\]##}\", \"\.\"\)_#}-{#_DATE\(\"{##itemc\[inici\]##}",
                             "{#_DATE(\"{##itemc[inici]##}\", \".\")_#}-{#_DATE(\"{##itemc[final]##}"]];
                $dataChanged = $this->updateTemplateByReplace($doc, $aTokRep);

                if (($ret = !empty($dataChanged))) {
                    $this->model->setRawProjectDocument($filename, $dataChanged, "Upgrade templates: version ".($ver-1)." to $ver");
                }
                break;
        }
        return $ret;
    }

}
