<?php
/**
 * upgrader_4: Transforma el archivo continguts.txt de los proyectos 'prgfploe'
 *             desde la versión 3 a la versión 4
 * @author rafael <rclaver@xtec.cat>
*/
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_LIB_IOC')) define('DOKU_LIB_IOC', DOKU_INC."lib/lib_ioc/");
require_once DOKU_LIB_IOC . "upgrader/CommonUpgrader.php";

class upgrader_4 extends ProgramacionsCommonUpgrader {

    public function process($type, $ver, $filename=NULL) {
        switch ($type) {
            case "fields":
                //Transforma la estructura de datos del archivo management.mdpr
                $id = $this->model->getId();
                $projectType = $this->model->getProjectType();
                $subSet = "management";
                $metaDataQuery = $this->model->getPersistenceEngine()->createProjectMetaDataQuery($id, $subSet, $projectType);
                $dataManagement = $metaDataQuery->getDataProject($id);
                if (empty($dataManagement['stateHistory'])) {
                    $ret = true;
                }else {
                    foreach ($dataManagement['stateHistory'] as $key => $value) {
                        $dataManagement['stateHistory'][$key]['remarks'] = $value['remarks'];
                    }
                    $newDataManagement['workflow'] = $dataManagement['workflow'];
                    $newDataManagement['workflow']['stateHistory'] = $dataManagement['stateHistory'];

                    $ret = $metaDataQuery->setMeta(json_encode($newDataManagement), $subSet, "canvi d'estructura", NULL);
                }
                break;
                
            case "templates":
                // Sólo se debe actualizar la versión del documento si el coordinador de calidad lo indica!!!!!!
                if (TRUE) {
                    if (!$this->upgradeDocumentVersion($ver)) return false;
                }

                //Transforma el archivo continguts.txt del proyecto desde la versión $ver a la versión $ver+1
                if ($filename===NULL)
                    $filename = $this->model->getProjectDocumentName();
                $doc = $this->model->getRawProjectDocument($filename);

                $aTokRep = [["(\{#_SEARCH_ROW\(\[\{##itemUf\[unitat formativa\]##\}, )(\{##itemRa\[ra\]##\})",
                             "$1\"$2\""]
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
