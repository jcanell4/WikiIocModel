<?php
/**
 * upgrader_1: Transforma los datos del proyecto "platreballfp"
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

    public function process() {
        /***********************************************************
         * Canvis a la plantilla:
         * Línia 96.  Es canvia "  :title:Taula Unitats" per "  :title:Apartats"
         * Línia 102. Es canvia "{#_DATE("{##itemc[inici]##}", ".")_#}-{#_DATE("{##itemc[inici]##}" per "{#_DATE("{##itemc[inici]##}", ".")_#}-{#_DATE("{##itemc[final]##}"
         */

        //Versión basada en la comparación de plantillas a partir de su conversión en arrays por token
//        $base = WikiGlobalConfig::getConf('datadir');
//        $baset = "$base/plantilles/docum_ioc/pla_treball_fp/loe/continguts";
//        $t0 = @file_get_contents("$baset.v0");
//        $t1 = @file_get_contents("$baset.txt");
//        $docFile = $base."/".str_replace(":", "/", $this->model->getId()). "/continguts.txt";
//        $doc = @file_get_contents($docFile);
//        $dataChanged = $this->updateDocToNewTemplate($t0, $t1, $doc);
//        @file_put_contents($docFile, $dataChanged);

        //Versión basada en la función updateTemplateByReplace()
        $base = WikiGlobalConfig::getConf('datadir');
        $docFile = $base."/".str_replace(":", "/", $this->model->getId()). "/continguts.txt";
        $doc = @file_get_contents($docFile);
        $aTokRep = [["\s+:title:Taula Unitats",
                     "  :title:Apartats"],
                    ["{#_DATE\(\"{##itemc\[inici\]##}\", \"\.\"\)_#}-{#_DATE\(\"{##itemc\[inici\]##}",
                     "{#_DATE(\"{##itemc[inici]##}\", \".\")_#}-{#_DATE(\"{##itemc[final]##}"]];
        $dataChanged = $this->updateTemplateByReplace($doc, $aTokRep);
        @file_put_contents($docFile, $dataChanged);
    }

}
