<?php
/**
 * upgrader_30: Transforma el archivo continguts.tx del proyecto 'ptfploe'
 *              desde la versión 29 a la versión 30
 * @author rafael <rclaver@xtec.cat>
 */
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_LIB_IOC')) define('DOKU_LIB_IOC', DOKU_INC."lib/lib_ioc/");
require_once DOKU_LIB_IOC . "upgrader/CommonUpgrader.php";

class upgrader_30 extends CommonUpgrader {

    public function process($type, $ver, $filename=NULL) {
        switch ($type) {
            case "fields":
                //Transforma los datos del proyecto desde la estructura de la versión $ver a la versión $ver+1
                $ret = true;
                break;
            case "templates":
                // Actualiza la versión del documento establecido en el sistema de calidad del IOC (Visible en el pie del documento)
                // Sólo se debe actualizar si el coordinador de calidad lo indica!!!!!!
                $dataProject = $this->model->getCurrentDataProject($this->metaDataSubSet);
                if (!is_array($dataProject))
                    $dataProject = json_decode($dataProject, TRUE);
                $dataProject['documentVersion'] = $dataProject['documentVersion']+1;
                $ret = $this->model->setDataProject(json_encode($dataProject), "Upgrade fields: version ".($ver-1)." to $ver", '{"fields":'.$ver.'}');

                //Transforma el archivo continguts.txt del proyecto desde la versión $ver a la versión $ver+1
                if ($filename===NULL)
                    $filename = $this->model->getProjectDocumentName();
                $doc = $this->model->getRawProjectDocument($filename);

                $aTokRep = [["competències( relatives)? de(l)? treball en equip",
                             "competències$1 de la capacitat clau de$2 treball en equip"]
                           ];
                $dataChanged = $this->updateTemplateByReplace($doc, $aTokRep);

                if (($ret = !empty($dataChanged))) {
                    $this->model->setRawProjectDocument($filename, $dataChanged, "Upgrade templates: version ".($ver-1)." to $ver", $ver);
                }
                break;
        }
        return $ret;
    }

}
