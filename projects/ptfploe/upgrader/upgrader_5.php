<?php
/**
 * upgrader_5: Transforma el archivo continguts.txt de los proyectos 'ptfploe'
 *             desde la versión 4 a la versión 5
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
                $ret = TRUE;
                break;

            case "templates":

                if ($filename===NULL) {
                    $filename = $this->model->getProjectDocumentName();
                }
                $data = $this->model->getDataProject();
                $template_name = $this->model->getTemplateContentDocumentId($data);

                $file = $this->model->getTemplatePath($template_name, 'v5');
                $doc0 = io_readFile($file);
                $doc1 = $this->model->getRawProjectDocument($filename);

                $aTokSub = ["(::table:T11-\{\#\#itemUf\[unitat formativa\]\#\#\}\n)(.*\n)*(:::)"];

                $dataChanged = $this->updateTemplateBySubstitute($doc0, $doc1, $aTokSub);

                if (!empty($dataChanged)) {
                    $this->model->setRawProjectDocument($filename, $dataChanged, "Upgrade: version 4 to 5");
                }
                $ret = !empty($dataChanged);
        }
        return $ret;
    }

}