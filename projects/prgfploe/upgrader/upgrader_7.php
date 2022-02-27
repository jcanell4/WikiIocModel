<?php
/**
 * upgrader_7: Transforma el archivo continguts.txt de los proyectos 'prgfploe'
 *             desde la versión 6 a la versión 7
 * @author rafael <rclaver@xtec.cat>
*/
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_LIB_IOC')) define('DOKU_LIB_IOC', DOKU_INC."lib/lib_ioc/");
require_once DOKU_LIB_IOC . "upgrader/CommonUpgrader.php";

class upgrader_7 extends ProgramacionsCommonUpgrader {

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

                $aTokRep = [['(\{##taulaDadesBlocs##\}\)_#\}.>)(0)',
                             '${1}1'],
                            ['(filter="\\{##itemUf\\[unitat formativa\\]##\\}==\\{##itemCa\\[uf\\]##\\}&&\\{##itemRa\\[ra\\]##\\}==)(\\{.*<WIOCCL:IF condition="\\{##itemRa\\[ra\\]##\\}==)(1">.*<WIOCCL:IF condition="\\{##itemRa\\[ra\\]##\\})\\\\>(1">)',
                             '${1}=${2}=${3}!==${4}'],
                            ['(\\{#_SUBS\\(\\{#_COUNTINARRAY\\(\\{##activitatsAprenentatge##\\}, \\[\'\'unitat formativa\'\', \'\'nucli formatiu\'\'\\], \\[\\{##itemUf\\[unitat formativa\\]##\\},\\{##itemNf\\[nucli formatiu\\]##\\}\\])(\\)_#\\},1\\)_#\\})',
                             '${1},1${2}'],
                            ['(\\| \\/\\/descripció:\\/\\/\\\\\\\\ \\\\\\\\ \\{##itemAa\\[descripcio\\]##\\}.*\\n<\\/WIOCCL:IF>\\n<\\/WIOCCL:SET>\\n<\\/WIOCCL:FOREACH>\\n<\\/WIOCCL:SET>\\n)(:::)',
                             '${1}'."\n".'${2}']
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
