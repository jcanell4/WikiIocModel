<?php
/**
 * upgrader_3: Transforma el archivo continguts.txt de los proyectos 'prgfploe'
 *             desde la versión 2 a la versión 3
 * @author rafael <rclaver@xtec.cat>
*/
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_LIB_IOC')) define('DOKU_LIB_IOC', DOKU_INC."lib/lib_ioc/");
require_once DOKU_LIB_IOC . "upgrader/CommonUpgrader.php";

class upgrader_3 extends ProgramacionsCommonUpgrader {

    public function process($type, $ver, $filename=NULL) {
        switch ($type) {
            case "fields":
                //Transforma los datos de los proyectos
                $dataProject = $this->model->getCurrentDataProject($this->metaDataSubSet);
                if (!is_array($dataProject)) $dataProject = json_decode($dataProject, TRUE);

                //Actualitza els camps notaMinima??? a partir de les dades de la taula 'taulaInstrumentsAvaluacio'
                $this->updateNotaMinimaInProgramacions($dataProject);
                $ret = $this->model->setDataProject(json_encode($dataProject), "Upgrade fields: version ".($ver-1)." to $ver", '{"fields":'.$ver.'}');
                break;
                
            case "templates":
                // Sólo se debe actualizar la versión del documento si el coordinador de calidad lo indica!!!!!!
                if (FALSE) {
                    if (!$this->upgradeDocumentVersion($ver)) return false;
                }

                //Transforma el archivo continguts.txt de los proyectos
                if ($filename===NULL)
                    $filename = $this->model->getProjectDocumentName();
                $doc = $this->model->getRawProjectDocument($filename);

                $aTokRep = [["(\*\*QF)(= \{#_GET_PERCENT\(\{##sum_ponderacio##\},\{##ponderacioAC##\}\)_#\}% AC)( \+ )",
                             "$1 $2<WIOCCL:IF condition=\"{##length##}\\>0\">$3</WIOCCL:IF>"]
                           ];
                $dataChanged = $this->updateTemplateByReplace($doc, $aTokRep);

                $aTokIns = [['regexp' => "<WIOCCL:IF condition=\"\{##keyEAF##\}===false && \{##keyJT##\}===false\">",
                             'text' => "\n<WIOCCL:IF condition=\"{##keyPAF##}===false && {##keyPAFV##}===false\">".
                                       "\nNo hi ha prova d'avaluació final.".
                                       "\n</WIOCCL:IF>".
                                       "\n<WIOCCL:IF condition=\"{##keyPAF##}!==false || {##keyPAFV##}!==false\">",
                             'pos' => CommonUpgrader::DESPRES,
                             'modif' => "m"],
                            ['regexp' => "<WIOCCL:IF condition=\"\{##keyEAF##\}!==false \|\| \{##keyJT##\}!==false\">",
                             'text' => "</WIOCCL:IF>\n",
                             'pos' => CommonUpgrader::ABANS,
                             'modif' => "m"]
                           ];
                $dataChanged = $this->updateTemplateByInsert($dataChanged, $aTokIns);

                if (($ret = !empty($dataChanged))) {
                    $this->model->setRawProjectDocument($filename, $dataChanged, "Upgrade templates: version ".($ver-1)." to $ver", $ver);
                }
                break;
        }
        return $ret;
    }

}
