<?php
/**
 * upgrader_1: Transforma los datos del proyecto "manual"
 *             desde la estructura de la versión 0 a la estructura de la versión 1
 * @author rafael
 */
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_LIB_IOC')) define('DOKU_LIB_IOC', DOKU_INC."lib/lib_ioc/");
require_once DOKU_LIB_IOC . "upgrader/CommonUpgrader.php";

class upgrader_1 extends CommonUpgrader {

    public function process($type, $ver, $filename=NULL) {

        switch ($type) {
            case "fields":
                //Transforma los datos del proyecto "manual" desde la estructura de la versión 0 a la versión 1
                $dataProject = $this->model->getCurrentDataProject($this->metaDataSubSet);
                $dataProject = IocCommon::toArrayThroughArrayOrJson($dataProject);

                //Añade el campo 'amagarMenuInici' a la estructura
                $dataProject = $this->addNewField($dataProject, "amagarMenuInici", "false");
                $ret = $this->model->setDataProject(json_encode($dataProject), "Upgrade fields: version ".($ver-1)." to $ver", '{"fields":'.$ver.'}');
                break;

            case "templates":
                $ret = TRUE;
                break;
        }

        return $ret;
    }

}
