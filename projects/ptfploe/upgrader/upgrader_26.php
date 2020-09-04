<?php
/**
 * upgrader_26: Transforma el archivo continguts.tx del proyecto 'ptfploe'
 *              desde la versión 25 a la versión 26 (asociado a la actualización de 8 a 9 de los datos del proyecto)
 * @author rafael <rclaver@xtec.cat>
 */
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_LIB_IOC')) define('DOKU_LIB_IOC', DOKU_INC."lib/lib_ioc/");
require_once DOKU_LIB_IOC . "upgrader/CommonUpgrader.php";

class upgrader_26 extends CommonUpgrader {

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
                if ($filename===NULL) {
                    $filename = $this->model->getProjectDocumentName();
                }
                $doc = $this->model->getRawProjectDocument($filename);

                $aTokRep = [["^::table:T10(.*\s)*?:::$",
                             "::table:T10\n"
                            ."  :title:Dates PAFs\n"
                            ."  :type:pt_taula\n"
                            ."  :footer:La vostra data i hora de la PAF es comunicarà al Taulell de Tutoria.\n"
                            ."^  PAF  ^  Data  ^  Publicació qualificació  ^\n"
                            ."|  1  |  {#_DATE(\"{##dataPaf11##}\")_#} o {#_DATE(\"{##dataPaf12##}\")_#}  |  {#_DATE(\"{##dataQualificacioPaf1##}\")_#}  |\n"
                            ."|  2  |  {#_DATE(\"{##dataPaf21##}\")_#} o {#_DATE(\"{##dataPaf22##}\")_#}  |  {#_DATE(\"{##dataQualificacioPaf2##}\")_#}  |\n"
                            .":::"]];
                $dataChanged = $this->updateTemplateByReplace($doc, $aTokRep);

                if (!empty($dataChanged)) {
                    $this->model->setRawProjectDocument($filename, $dataChanged, "Upgrade: version 25 to 26");
                }
                $ret = !empty($dataChanged);
        }
        return $ret;
    }

}
