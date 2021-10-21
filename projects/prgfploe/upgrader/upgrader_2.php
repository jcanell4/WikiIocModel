<?php
/**
 * upgrader_2: Transforma el archivo continguts.txt de los proyectos 'prgfploe'
 *             desde la versión 1 a la versión 2
 * @author rafael <rclaver@xtec.cat>
*/
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_LIB_IOC')) define('DOKU_LIB_IOC', DOKU_INC."lib/lib_ioc/");
require_once DOKU_LIB_IOC . "upgrader/CommonUpgrader.php";

class upgrader_2 extends CommonUpgrader {

    public function process($type, $ver, $filename=NULL) {
        switch ($type) {
            case "fields":
                //Transforma los datos del proyecto desde la estructura de la versión 1 a la versión 2
                $dataProject = $this->model->getCurrentDataProject($this->metaDataSubSet);
                if (!is_array($dataProject)) {
                    $dataProject = json_decode($dataProject, TRUE);
                }
                $dataProject['estrategiesMetodologiques'] = preg_replace("/<p>(\s*&(amp;)*lt;p&(amp;)*gt;)*\s*(.*?)(\s*&(amp;)*lt;\/p&(amp;)*gt;)*\s*<\/p>/s", "$4", $dataProject['estrategiesMetodologiques']);

                $ret = $this->model->setDataProject(json_encode($dataProject), "Upgrade fields: version ".($ver-1)." to $ver. Simultànea a l'actualització de 18 a 19 de templates", '{"fields":'.$ver.'}');
                break;

            case "templates":
                // Actualiza la versión del documento establecido en el sistema de calidad del IOC (Visible en el pie del documento)
                // Sólo se debe actualizar si el coordinador de calidad lo indica!!!!!!
                $dataProject = $this->model->getCurrentDataProject($this->metaDataSubSet);
                if (!is_array($dataProject))
                    $dataProject = json_decode($dataProject, TRUE);
                $dataProject['documentVersion'] = $dataProject['documentVersion']+1;
                $ret = $this->model->setDataProject(json_encode($dataProject), "Upgrade fields: version ".($ver-1)." to $ver", '{"fields":'.$ver.'}');

                //Transforma el archivo continguts.txt del proyecto desde la versión 1 a la versión 2
                if ($filename===NULL)
                    $filename = $this->model->getProjectDocumentName();
                $doc = $this->model->getRawProjectDocument($filename);

                $aTokDel = ["<WIOCCL:SET var=\"keyUfPAF\".*?\n",
                            "<WIOCCL:IF condition=\"\{##sizeActivitatsAprenentatge##\}==\{##posAa##\}\">\*<\/WIOCCL:IF>"
                           ];
                $dataChanged = $this->updateTemplateByDelete($doc, $aTokDel);

                $aTokIns = [['regexp' => "^  \* La qualificació de l'AC es té en compte.*?\n",
                             'text' => "<WIOCCL:SET var=\"keyUfPAF\" type=\"literal\" value=\"{#_SEARCH_KEY([{##itemUf[unitat formativa]##}, ''PAF''],{##taulaInstrumentsAvaluacio##}, [''unitat formativa'',''tipus''])_#}\">\n"
                                      . "<WIOCCL:IF condition=\"{##keyUfPAF##}!=false\">\n",
                             'pos' => CommonUpgrader::ABANS,
                             'modif' => "m"]
                           ];
                $dataChanged = $this->updateTemplateByInsert($dataChanged, $aTokIns);

                $aTokIns = [['regexp' => "^  \* La qualificació de l'AC es té en compte.*?\n",
                             'text' => "</WIOCCL:IF>\n",
                             'pos' => CommonUpgrader::DESPRES,
                             'modif' => "m"]
                           ];
                $dataChanged = $this->updateTemplateByInsert($dataChanged, $aTokIns);

                $aTokRep = [["(  \* Es concreta en \{##nombreEACs##\}) EAC.",
                             "$1 instruments d'AC."],
                            ["(\*\* )(Exercicis)( d'avaluació contínua) \(EAC\)",
                             "$1Activitats$3"],
                            ["(El sistema no permet lliurar cap )(EAC)( passades les)",
                             "$1activitat d'AC$3"],
                            ["(Els EAC)( que siguin còpia literal)",
                             "Les activitats d'AC$2"],
                            ["(  \* En )(els EAC)( s'estableixen els criteris d'avaluació corresponents)",
                             "$1les activitats d'AC$3"],
                            ["(  \* El professor )( )(corregeix )(els EAC)( i emet una qualificació numèrica)",
                             "$1$3les activitats d'AC$5"],
                            ["(CA\{##itemCa\[ca\]##\})( \{##itemCa\[descripcio\]##\})",
                             "$1<WIOCCL:IF condition=\"{##itemCa[contextualitzat]##}==true\">*</WIOCCL:IF>$2"],
                            ["(\{##itemCo\[cont\]##\})( \{##itemCo\[descripcio\]##\})",
                             "$1<WIOCCL:IF condition=\"{##itemCo[contextualitzat]##}==true\">*</WIOCCL:IF>$2"],
                            ["(se)s(se cap arrodoniment previ)",
                             "$1n$2"],
                            ["(No es requ)i(ereixen)",
                             "$1$2"],
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
