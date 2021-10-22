<?php
/**
 * upgrader_28: Transforma el archivo continguts.tx del proyecto 'ptfploe'
 *              desde la versión 27 a la versión 28
 * @author rafael <rclaver@xtec.cat>
 */
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_LIB_IOC')) define('DOKU_LIB_IOC', DOKU_INC."lib/lib_ioc/");
require_once DOKU_LIB_IOC . "upgrader/CommonUpgrader.php";

class upgrader_28 extends CommonUpgrader {

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

                $aTokIns = [['regexp' => "^\|  1  \|  \{#_DATE\(\"\{##dataPaf11##\}\"\)_#\}.*\n",
                             'text' => "<WIOCCL:IF condition=\"!{#_IS_STR_EMPTY(''{##dataPaf21##}'')_#}\">\n",
                             'pos' => 1,
                             'modif' => "m"],
                            ['regexp' => "^\|  2  \|  \{#_DATE\(\"\{##dataPaf21##\}\"\)_#\}.*\n",
                             'text' => "</WIOCCL:IF>\n",
                             'pos' => 1,
                             'modif' => "m"]];

                $dataChanged = $this->updateTemplateByInsert($doc, $aTokIns);

                if (($ret = !empty($dataChanged))) {
                    $this->model->setRawProjectDocument($filename, $dataChanged, "Upgrade templates: version ".($ver-1)." to $ver", $ver);
                }
                break;
        }
        return $ret;
    }

}
