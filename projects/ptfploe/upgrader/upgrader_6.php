<?php
/**
 * upgrader_6: Transforma el archivo continguts.txt de los proyectos 'ptfploe'
 *             desde la versión 5 a la versión 6
 * @author rafael
 */
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_LIB_IOC')) define('DOKU_LIB_IOC', DOKU_INC."lib/lib_ioc/");
require_once DOKU_LIB_IOC . "upgrader/CommonUpgrader.php";

class upgrader_6 extends CommonUpgrader {

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
                $dataProject['moodleCourseId'] = 0;
                $dataProject['descripcio'] = "tracta de ".$dataProject['descripcio'];
                $ret = $this->model->setDataProject(json_encode($dataProject), "Upgrade fields: version ".($ver-1)." to $ver", "{'fields':".($ver-1)."}");
                break;

            case "templates":
                //Primera parte: modificación del fichero de proyecto (el .txt que está en data/pages/ y que, originalmente, proviene de una plantilla)
                /*
                    linea 4:
                    buscar
                    Aquest <WIOCCL:IF condition="''mòdul''!={##tipusBlocModul##}">{##tipusBlocModul##} del</WIOCCL:IF> mòdul {##modul##} tracta de {##descripcio##}
                    sustituir
                    Aquest <WIOCCL:IF condition="''mòdul''!={##tipusBlocModul##}">{##tipusBlocModul##} del</WIOCCL:IF> mòdul {##modul##} {##descripcio##}
                */
                if ($filename===NULL) { //Ojo! Ahora se pasa por parámetro
                    $filename = $this->model->getProjectDocumentName();
                }
                $doc1 = $this->model->getRawProjectDocument($filename);
                $aTokRep[] = ["(Aquest \<WIOCCL:IF condition.*tipusBlocModul.*tipusBlocModul.*del.*mòdul.*modul.*)( tracta de )(.*descripcio.*\n)",
                              "$1 $3"];
                $dataChanged = $this->updateTemplateByReplace($doc1, $aTokRep);

                if (($ret = !empty($dataChanged))) {
                    $this->model->setRawProjectDocument($filename, $dataChanged, "Upgrade templates: version ".($ver-1)." to $ver");
                }
                /* ANULADO
                //Segunda parte: modificación de los datos del proyecto (archivo .mdpr que está en data/mdprojects/)
                $dataProject = $this->model->getCurrentDataProject();
                $dataProject['descripcio'] = "tracta de ".$dataProject['descripcio'];
                $this->model->setDataProject(json_encode($dataProject), "Upgrade fields: version ".($ver-1)." to $ver", "{'fields':".($ver-1)."}");
                */
                break;
        }
        return $ret;
    }

}
