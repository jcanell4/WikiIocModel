<?php
/**
 * upgrader_20: Transforma el archivo continguts.tx del proyecto 'ptfplogse'
 *              desde la versión 19 a la versión 20
 * @author rafael <rclaver@xtec.cat>
 */
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_LIB_IOC')) define('DOKU_LIB_IOC', DOKU_INC."lib/lib_ioc/");
require_once DOKU_LIB_IOC . "upgrader/CommonUpgrader.php";

class upgrader_20 extends CommonUpgrader {

    protected $model;
    protected $metaDataSubSet;

    public function __construct($model) {
        $this->model = $model;
        $this->metaDataSubSet = $this->model->getMetaDataSubSet();
    }

    public function process($type, $ver, $filename=NULL) {
        switch ($type) {
            case "fields":
                $ret = TRUE;
                break;

            case "templates":
                if ($filename===NULL) {
                    $filename = $this->model->getProjectDocumentName();
                }
                $doc = $this->model->getRawProjectDocument($filename);

                $aTokRep = [[
                    "És ..no presencial.., es realitza a distància al llarg del .WIOCCL:IF condition=.''crèdit''!={##tipusBlocCredit##}.>{##tipusBlocCredit##}<.WIOCCL:IF> .consulteu dates clau a les taules :table:T06: i :table:T07:..",
                    "És **no presencial**, es realitza a distància al llarg del <WIOCCL:IF condition=\"''crèdit''!={##tipusBlocCredit##}\">{##tipusBlocCredit##} del </WIOCCL:IF>crèdit (consulteu dates clau a les taules :table:T06: i :table:T07:)."
                ]];
                $dataChanged = $this->updateTemplateByReplace($doc, $aTokRep);

                if (($ret = !empty($dataChanged))) {
                    $this->model->setRawProjectDocument($filename, $dataChanged, "Upgrade templates: version ".($ver-1)." to $ver", $ver);
                }
                break;
        }
        return $ret;
    }

}
