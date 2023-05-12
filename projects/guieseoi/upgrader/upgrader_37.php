<?php
/**
 * upgrader_37: Transforma el archivo continguts.txt de los proyectos 'guieseoi'
 *             desde la versión 36 a la versión 37
 * @author rafael <rclaver@xtec.cat>
*/
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_LIB_IOC')) define('DOKU_LIB_IOC', DOKU_INC."lib/lib_ioc/");
require_once DOKU_LIB_IOC . "upgrader/CommonUpgrader.php";

class upgrader_37 extends ProgramacionsCommonUpgrader {

    public function process($type, $ver, $filename=NULL) {
        switch ($type) {
            case "fields":
                //Transforma los datos del proyecto desde la estructura de la versión $ver a la versión $ver+1
                $ret = true;
                break;

            case "templates":
                // Sólo se debe actualizar la versión del documento si el coordinador de calidad lo indica!!!!!!
                if (FALSE) {
                    if (!$this->upgradeDocumentVersion($ver)) return false;
                }

                //Transforma el archivo continguts.txt del proyecto desde la versión $ver a la versión $ver+1
                if ($filename===NULL)
                    $filename = $this->model->getProjectDocumentName();
                $doc = $this->model->getRawProjectDocument($filename);

                $aTokRep = [["^(\|  {##item\[id\]##}  \|  )({##item\[inscripció\]##})(  \|  {#_DATE\(\"{##item\[llista provisional]##}\"\)_#}  \|  {#_DATE\(\"{##item\[llista definitiva]##}\"\)_#}  \|  {#_DATE\(\"{##item\[data JT]##}\"\)_#}  \|  {#_DATE\(\"{##item\[qualificació]##}\"\)_#}  \|)$",
                            "$1{#_DATE(\"$2\")_#}$3",
                            "s"],
                           ["^(\|  {##item\[id\]##}  \|  )({##item\[inscripció recuperació\]##})(  \|)",
                            "$1{#_DATE(\"$2\")_#}$3",
                            "s"]
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
