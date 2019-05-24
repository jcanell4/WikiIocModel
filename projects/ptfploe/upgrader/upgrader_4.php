<?php
/**
 * upgrader_4: Transforma el archivo continguts.txt de los proyectos 'ptfploe'
 *             desde la versión 3 a la versión 4
 * @author rafael
 */
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_LIB_IOC')) define('DOKU_LIB_IOC', DOKU_INC."lib/lib_ioc/");
require_once DOKU_LIB_IOC . "upgrader/CommonUpgrader.php";

class upgrader_4 extends CommonUpgrader {

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
                  Buscar y reemplazar texto (Línea 337)
                  buscar    : filter="{##itemsub[unitat formativa]##}=={##ind##}
                  reemplazar: filter="{##itemsub[unitat formativa]##}=={##itemUf[unitat formativa]##}
                */
                if ($filename===NULL) {
                    $filename = $this->model->getProjectDocumentName();
                }
                $doc = $this->model->getRawProjectDocument($filename);
                $aTokRep = [["filter\=\"\{\#\#itemsub\[unitat formativa\]\#\#\}\=\=\{\#\#ind\#\#\}",
                             "filter=\"{##itemsub[unitat formativa]##}=={##itemUf[unitat formativa]##}"]];
                $dataChanged = $this->updateTemplateByReplace($doc, $aTokRep);

                if (!empty($dataChanged)) {
                    $this->model->setRawProjectDocument($filename, $dataChanged, "Upgrade: version 3 to 4");
                }
                $ret = !empty($dataChanged);
        }
        return $ret;
    }

}
