<?php
/**
 * upgrader_1: Transforma el archivo continguts.txt de los proyectos 'ptfploe'
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

    public function process($type, $filename=NULL) {
        switch ($type) {
            case "fields":
                $dataProject = $this->model->getMetaDataProject($this->metaDataSubSet);
                if (!is_array($dataProject)) {
                    $dataProject = json_decode($dataProject, TRUE);
                }
                $dataProject['moodleCourseId'] = 0;


                $this->model->setDataProject(json_encode($dataProject), "Upgrade: version 0 to 1 (s'afegeix el camp 'moodleCourseId'");

                $ret = TRUE;
                break;
            case "templates":
                $ret = TRUE;
        }
        return $ret;
    }

}
