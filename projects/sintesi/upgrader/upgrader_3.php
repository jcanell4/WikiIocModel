<?php
/**
 * upgrader_3: Transforma el archivo continguts.txt de los proyectos 'ptfploe'
 *             desde la versión 2 a la versión 3
 * @author josep
 */
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_LIB_IOC')) define('DOKU_LIB_IOC', DOKU_INC."lib/lib_ioc/");
require_once DOKU_LIB_IOC . "upgrader/CommonUpgrader.php";

class upgrader_3 extends CommonUpgrader {

    protected $model;
    protected $metaDataSubSet;

    public function __construct($model) {
        $this->model = $model;
        $this->metaDataSubSet = $this->model->getMetaDataSubSet();
    }

    public function process($type, $ver, $filename=NULL) {
        switch ($type) {
            case "fields":
                $dataProject = $this->model->getCurrentDataProject($this->metaDataSubSet);
                $l1 = strtotime("2020-02-14");
                $l2 = strtotime("2020-05-15");
                $now = time();
                if($l1 < $now && $now < $l2){
                    $dataProject["semestre"] = 2;
                }
                $ret = $this->model->setDataProject(json_encode($dataProject), "Upgrade fields: version ".($ver-1)." to $ver", "{'fields':".($ver-1)."}");
                
                break;
            case "templates":
                if ($filename===NULL) { 
                    $filename = $this->model->getProjectDocumentName();
                }
                $doc = $this->model->getRawProjectDocument($filename)."\n";

                $aTokRep = [
                            ["condition=\"\{#_ARRAY_LENGTH\(\{##dadesAC##\}\)_#\}",
                             "condition=\"{#_ARRAY_LENGTH({##dadesCompetencies##})_#}"]
                           ];
                $doc = $this->updateTemplateByReplace($doc, $aTokRep);

                if (!empty($doc)) {
                    $this->model->setRawProjectDocument($filename, $doc, "Upgrade version 2 to 3");
                }
                $ret = !empty($doc);
        }
        return $ret;
    }

}
