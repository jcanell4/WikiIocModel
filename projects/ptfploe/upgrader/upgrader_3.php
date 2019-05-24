<?php
/**
 * upgrader_3: Transforma el archivo continguts.txt de los proyectos 'ptfploe'
 *             desde la versión 2 a la versión 3
 * @author rafael
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

    public function process($type, $filename=NULL) {
        switch ($type) {
            case "fields":
                $ret = TRUE;
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
                    $this->model->setRawProjectDocument($filename, $dataChanged, "Upgrade: version 2 to 3");
                }
                $ret = !empty($dataChanged);
                */
                $ret = TRUE;
        }
        return $ret;
    }

}
