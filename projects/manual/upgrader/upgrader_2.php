<?php
/**
 * upgrader_1: Transforma los datos del proyecto "manual"
 *             desde la estructura de la versión 1 a la estructura de la versión 2
 * @author rafael
 */
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_LIB_IOC')) define('DOKU_LIB_IOC', DOKU_INC."lib/lib_ioc/");
require_once DOKU_LIB_IOC . "upgrader/CommonUpgrader.php";

class upgrader_2 extends CommonUpgrader {

    public function process($type, $ver, $filename=NULL) {

        switch ($type) {
            case "fields":
                //Transforma los datos del proyecto "manual" desde la estructura de la versión 1 a la versión 2
                $dataProject = $this->model->getCurrentDataProject($this->metaDataSubSet);

                $dataProject = IocCommon::toArrayThroughArrayOrJson($dataProject);

                //Añade los campos 'id' y 'descripcio' a la estructura de la tabla 'documents'
                $dataProject = $this->addFieldAutoIncrementInMultiRow($dataProject, 'documents', 'id');
                $dataProject = $this->addFieldInMultiRow($dataProject, 'documents', 'descripcio', "");
                $ret = $this->model->setDataProject(json_encode($dataProject), "Upgrade fields: version ".($ver-1)." to $ver", '{"fields":'.$ver.'}');
                break;

            case "templates":
                $ret = TRUE;
                break;
        }

        return $ret;
    }

}
