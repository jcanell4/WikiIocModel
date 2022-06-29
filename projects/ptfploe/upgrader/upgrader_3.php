<?php
/**
 * upgrader_3: Transforma la estructura de datos (y el archivo continguts.txt) de los proyectos 'ptfploe'
 *             desde la versión 2 a la versión 3
 * @author rafael
 */
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_LIB_IOC')) define('DOKU_LIB_IOC', DOKU_INC."lib/lib_ioc/");
require_once DOKU_LIB_IOC . "upgrader/CommonUpgrader.php";

class upgrader_3 extends CommonUpgrader {

    public function process($type, $ver, $filename=NULL) {
        switch ($type) {
            case "fields":
                $dataProject = $this->model->getCurrentDataProject($this->metaDataSubSet);
                $dataProject = IocCommon::toArrayThroughArrayOrJson($dataProject);

                //Añade el campo 'hiHaEnunciatRecuperacio' a la tabla 'datesEAF'
                $dataProject = $this->addFieldInMultiRow($dataProject, "datesEAF", "hiHaEnunciatRecuperacio", TRUE);

                //Añade el campo 'hiHaSolucio' a la tabla 'datesAC'
                $dataProject = $this->addFieldInMultiRow($dataProject, "datesAC", "hiHaSolucio", TRUE);

                $ret = $this->model->setDataProject(json_encode($dataProject), "Upgrade fields: version ".($ver-1)." to $ver", '{"fields":'.$ver.'}');
                break;

            case "templates":
                /*
                  Línia 372. Es canvia "{##item_act[descripció]##} \ </WIOCCL:FOREACH>     ||"
                                   per "{##item_act[descripció]##} \\ </WIOCCL:FOREACH>     ||"
                */
                /*
                Ara ja no és necessari corregir aquest error donat que ja no es propdueix
                -------------------------------------------------------------------------
                if ($filename===NULL) {
                    $filename = $this->model->getProjectDocumentName();
                }
                $doc = $this->model->getRawProjectDocument($filename);
                $aTokRep = [["\{\#\#item_act\[descripció\]\#\#\} \\\\ \<\/WIOCCL:FOREACH\>",
                             "{##item_act[descripció]##} \\\\\\\\ </WIOCCL:FOREACH>"]];
                $dataChanged = $this->updateTemplateByReplace($doc, $aTokRep);

                if (!empty($dataChanged)) {
                    $this->model->setRawProjectDocument($filename, $dataChanged, "Upgrade templates: version ".($ver-1)." to $ver", $ver);
                }
                $ret = !empty($dataChanged);
                */
                $ret = TRUE;
                break;
        }
        return $ret;
    }

}
