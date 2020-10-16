<?php
/**
 * upgrader_4: Transforma la estructura de datos y el archivo continguts.txt de los proyectos 'ptfploe'
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

    public function process($type, $ver, $filename=NULL) {
        switch ($type) {
            case "fields":

                $dataProject = $this->model->getCurrentDataProject($this->metaDataSubSet);
                if (!is_array($dataProject)) {
                    $dataProject = json_decode($dataProject, TRUE);
                }

                // cerquem les dades de la paf1 i paf 2 i les qualificacions son de l'any 2019 i canviar-les per la mateixa data però 2020

                $dataProject['dataPaf1'] = str_replace("2019", "2020", $dataProject['dataPaf1']);
                $dataProject['dataPaf2'] = str_replace("2019", "2020", $dataProject['dataPaf2']);
                $dataProject['dataQualificacioPaf1'] = str_replace("2019", "2020", $dataProject['dataQualificacioPaf1']);
                $dataProject['dataQualificacioPaf2'] = str_replace("2019", "2020", $dataProject['dataQualificacioPaf2']);

                $ret = $this->model->setDataProject(json_encode($dataProject), "Upgrade fields: version ".($ver-1)." to $ver");
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

                if (($ret = !empty($dataChanged))) {
                    $this->model->setRawProjectDocument($filename, $dataChanged, "Upgrade templates: version ".($ver-1)." to $ver");
                }
                break;
        }
        return $ret;
    }

}
