<?php
/**
 * upgrader_1: Transforma los datos del proyecto "ptfplogse"
 *             desde la estructura de la versión 0 a la estructura de la versión 1
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
                break;

            case "templates":

                $doc = $this->model->getRawProjectDocument($filename);
                $aTokRep = [["\| \{##item\[id\]##\}  \|  \{##item\[unitat didàctica\]##\}  \|","| {##item[id]##} |  {##item[unitat didàctica]##}  |"]];
                $dataChanged = $this->updateTemplateByReplace($doc, $aTokRep);
                if (!empty($dataChanged)) {
                    $this->model->setRawProjectDocument($filename, $dataChanged, "Upgrade: version 1 to 2");
                }
                return !empty($dataChanged);
        }
    }

}
