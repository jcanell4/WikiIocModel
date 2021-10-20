<?php
/**
 * upgrader_1: Transforma el archivo continguts.txt de los proyectos 'ptfct'
 *             desde la versión 0 a la versión 1
 * @author rafael
 */
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_LIB_IOC')) define('DOKU_LIB_IOC', DOKU_INC."lib/lib_ioc/");
require_once DOKU_LIB_IOC . "upgrader/CommonUpgrader.php";

class upgrader_1 extends CommonUpgrader {

    public function process($type, $ver, $filename=NULL) {
        switch ($type) {
            case "fields":
                $dataProject = $this->model->getCurrentDataProject($this->metaDataSubSet);
                if (!is_array($dataProject)) {
                    $dataProject = json_decode($dataProject, TRUE);
                }
                $dataProject['moodleCourseId'] = 0;

                $ret = $this->model->setDataProject(json_encode($dataProject), "Upgrade fields: version ".($ver-1)." to $ver", '{"fields":'.$ver.'}');
                break;

            case "templates":
                // Línia 96.  Es canvia "  :title:Taula Unitats" per "  :title:Apartats"
                // Línia 102. Es canvia "{#_DATE("{##itemc[inici]##}", ".")_#}-{#_DATE("{##itemc[inici]##}" per "{#_DATE("{##itemc[inici]##}", ".")_#}-{#_DATE("{##itemc[final]##}"

                if ($filename===NULL) { //Ojo! Ahora se pasa por parámetro
                    $filename = $this->model->getProjectDocumentName();
                }
                $doc = $this->model->getRawProjectDocument($filename);

                $aTokRep = [["~~USE:WIOCCL~~",
                             ":###\n~~USE:WIOCCL~~"],
                             ["Drets i obligacions de l'alumne (nou)",
                             "Drets i obligacions de l'alumne"],
                             ["\\^  Data màxima per obtenir apte a secretaria  \\^",
                             "^  1a convocatòria per obtenir apte a secretaria  ^  2a convocatòria per obtenir apte a secretaria  ^"],
                             ["\\|  \\{\\\#\\_DATE\\(\"\\{\\#\\#dataMaxApteFCT\\#\\#\\}\"\\)\\_\\#\\}  \\|",
                             "|  {#_DATE(\"{##dataApteFCT##}\")_#}  |  {#_DATE(\"{##dataMaxApteFCT##}\")_#}  |"]];
                $dataChanged = $this->updateTemplateByReplace($doc, $aTokRep);

                if (!empty($dataChanged)) {
                    $this->model->setRawProjectDocument($filename, $dataChanged, "Upgrade templates: version ".($ver-1)." to $ver", $ver);
                }
                $ret = !empty($dataChanged);
        }
        return $ret;
    }

}
