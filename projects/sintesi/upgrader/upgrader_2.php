<?php
/**
 * upgrader_2: Transforma el archivo _wikiIocSystem_.mdpr y continguts.txt de los proyectos 'sintesi'
 *             desde la versión 1 a la versión 2
 * @author rafael
 */
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_LIB_IOC')) define('DOKU_LIB_IOC', DOKU_INC."lib/lib_ioc/");
require_once DOKU_LIB_IOC . "upgrader/CommonUpgrader.php";

class upgrader_2 extends CommonUpgrader {

    public function process($type, $ver, $filename=NULL) {
        switch ($type) {
            case "fields":
                $dataProject = $this->model->getCurrentDataProject($this->metaDataSubSet);
                $matches=array();
                preg_match("/([MC]\d{2})? *-? *(.+)/", $dataProject["modul"], $matches);
                if (!empty($matches[1])){
                    $dataProject['modulId']=$matches[1];
                }
                $dataProject['modul'] = $matches[2];

                $ret = $this->model->setDataProject(json_encode($dataProject), "Upgrade fields: version ".($ver-1)." to $ver", '{"fields":'.$ver.'}');
                break;

            case "templates":
                if ($filename===NULL) {
                    $filename = $this->model->getProjectDocumentName();
                }
                $doc = $this->model->getRawProjectDocument($filename)."\n";

                $aTokRep = [
                            [" períodes, de  les ",
                             " {##nomPeriodePlur##}, de  les "]
                           ];
                $doc = $this->updateTemplateByReplace($doc, $aTokRep);

                if (($ret = !empty($doc))) {
                    $this->model->setRawProjectDocument($filename, $doc, "Upgrade templates: version ".($ver-1)." to $ver", $ver);
                }
                break;
        }
        return $ret;
    }

}
