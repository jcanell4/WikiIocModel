<?php
/**
 * upgrader_1: Transforma el archivo continguts.txt de los proyectos 'prgfplogse'
 *             desde la versión 0 a la versión 1
 * @author rafael <rclaver@xtec.cat>
*/
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_LIB_IOC')) define('DOKU_LIB_IOC', DOKU_INC."lib/lib_ioc/");
require_once DOKU_LIB_IOC . "upgrader/CommonUpgrader.php";

class upgrader_1 extends CommonUpgrader {

    public function process($type, $ver, $filename=NULL) {
        switch ($type) {
            case "fields":
                //Transforma los datos del proyecto desde la estructura de la versión 0 a la versión 1
                // Actualiza la versión del documento establecido en el sistema de calidad del IOC (Visible en el pie del documento)
                // Sólo se debe actualizar si el coordinador de claidad lo indica!!!!!!
                $dataProject = $this->model->getCurrentDataProject($this->metaDataSubSet);
                if (!is_array($dataProject))
                    $dataProject = json_decode($dataProject, TRUE);
                $dataProject['documentVersion'] = 7;
                $ret = $this->model->setDataProject(json_encode($dataProject), "Upgrade fields: version ".($ver-1)." to $ver", '{"fields":'.$ver.'}');
                break;

            case "templates":
                // Sólo se debe actualizar la versión del documento si el coordinador de calidad lo indica!!!!!!
                if (TRUE) {
                    if (!$this->upgradeDocumentVersion($ver)) return false;
                }

                //Transforma el archivo continguts.txt del proyecto desde la versión 0 a la versión 1
                if ($filename===NULL)
                    $filename = $this->model->getProjectDocumentName();
                $doc = $this->model->getRawProjectDocument($filename);

                $aTokDel = ["<WIOCCL:SET var=\"keyPAF\".*?\n"];
                $dataChanged = $this->updateTemplateByDelete($doc, $aTokDel);

                $aTokIns = [['regexp' => "^  \* La qualificació de l'AC es té en compte.*?\n",
                             'text' => "<WIOCCL:SET var=\"keyPAF\" type=\"literal\" value=\"{#_SEARCH_KEY([''PAF''],{##taulaInstrumentsAvaluacio##}, [''tipus''])_#}\">\n"
                                      . "<WIOCCL:IF condition=\"{##keyPAF##}!=false\">\n",
                             'pos' => CommonUpgrader::ABANS,
                             'modif' => "m"],
                            ['regexp' => "^  \* La qualificació de l'AC es té en compte.*?\n",
                             'text' => "</WIOCCL:IF>\n",
                             'pos' => CommonUpgrader::DESPRES,
                             'modif' => "m"]
                           ];
                $dataChanged = $this->updateTemplateByInsert($dataChanged, $aTokIns);

                $aTokRep = [["(  \* Es concreta en \{##nombreEACs##\}) EAC.",
                             "$1 instruments d'avaluació."],
                            ["(\*\* )(Exercicis)( d'avaluació contínua) \(EAC\)",
                             "$1Activitats$3"],
                            ["(El sistema no permet lliurar cap )(EAC)( passades les)",
                             "$1activitat d'AC$3"],
                            ["(Els EAC)( que siguin còpia literal)",
                             "Les activitats d'AC$2"],
                            ["(  \* En )(els EAC)( s'estableixen els criteris d'avaluació corresponents)",
                             "$1les activitats d'AC$3"],
                            ["(  \* El professor)( ) (corregeix )(els EAC)( i emet una qualificació numèrica)",
                             "$1$3les activitats d'AC$5"]
                           ];
                $dataChanged = $this->updateTemplateByReplace($dataChanged, $aTokRep);

                if (($ret = !empty($dataChanged))) {
                    $this->model->setRawProjectDocument($filename, $dataChanged, "Upgrade templates: version ".($ver-1)." to $ver", $ver);
                }
                break;
        }
        return $ret;
    }

}
