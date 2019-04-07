<?php
/**
 * upgrader_2: Transforma los datos del proyecto "ptfploe"
 *             desde la estructura de la versión 1 a la estructura de la versión 2
 * @author rafael
 */
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_LIB_IOC')) define('DOKU_LIB_IOC', DOKU_INC."lib/lib_ioc/");
require_once DOKU_LIB_IOC . "upgrader/CommonUpgrader.php";

class upgrader_5 extends CommonUpgrader {

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

                if ($filename===NULL) { //Ojo! Ahora se pasa por parámetro
                    $filename = $this->model->getProjectDocumentName();
                }
                $data = $this->model->getDataProject();
                $template_name = $this->model->getTemplateContentDocumentId($data);

                $file = $this->model->getTemplatePath($template_name, 'v6');
                $doc0  = io_readFile($file);

                $doc1 = $this->model->getRawProjectDocument($filename);
                $aTokSub = ["(::table:T11-\{\#\#itemUf\[unitat formativa\]\#\#\}\n)(.*\n)*(:::)"];

                $dataChanged = $this->updateTemplateBySubstitute($doc0, $doc1, $aTokSub);
                if (!empty($dataChanged)) {
                    $this->model->setRawProjectDocument($filename, $dataChanged, "Upgrade: version 4 to 5");
                }
                return !empty($dataChanged);
        }
    }

}
