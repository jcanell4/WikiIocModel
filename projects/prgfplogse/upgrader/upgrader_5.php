<?php
/**
 * upgrader_5: Transforma los datos de los campos y el archivo continguts.txt de los proyectos 'prgfplogse'
 *             desde la versión 4 a la versión 5
 * @author rafael <rclaver@xtec.cat>
*/
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_LIB_IOC')) define('DOKU_LIB_IOC', DOKU_INC."lib/lib_ioc/");
require_once DOKU_LIB_IOC . "upgrader/CommonUpgrader.php";

class upgrader_5 extends ProgramacionsCommonUpgrader {

    public function process($type, $ver, $filename=NULL) {
        switch ($type) {
            case "fields":
                //Transforma los datos del proyecto
                $dataProject = $this->model->getCurrentDataProject($this->metaDataSubSet);
                $dataProject = IocCommon::toArrayThroughArrayOrJson($dataProject);

                //Omple a 0 el camp bloc de la taula 'taulaInstrumentsAvaluacio'
                $this->updateBlocInProgramacions($dataProject);
                $ret = $this->model->setDataProject(json_encode($dataProject), "Upgrade fields: version ".($ver-1)." to $ver", '{"fields":'.$ver.'}');
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

                $aTokMov = [['regexp0' => "<WIOCCL:SET var=\"nombreEACs\".*\n",
                             'regexp1' => "===== Avaluació contínua \(AC\) =====",
                             'pos' => CommonUpgrader::ABANS,
                             'modif' => ""]
                           ];
                $dataChanged = $this->updateTemplateByMove($doc, $aTokMov);

                $aTokIns = [['regexp' => "^===== Avaluació contínua \(AC\) =====$",
                             'text' => "<WIOCCL:IF condition=\"{##nombreEACs##}\>0\">\n",
                             'pos' => CommonUpgrader::ABANS,
                             'modif' => "m"]
                            ];
                $dataChanged = $this->updateTemplateByInsert($dataChanged, $aTokIns);

                $aTokRep = [["(^  \* La qualificació de l'AC.*\n<\/WIOCCL:IF>)(\n<\/WIOCCL:SET>)",
                             '$1']
                           ];
                $dataChanged = $this->updateTemplateByReplace($dataChanged, $aTokRep);

                $aTokIns = [['regexp' => "^  \* Inclou la publicació de la solució,.*$",
                             'text' => "\n</WIOCCL:IF>\n</WIOCCL:SET>",
                             'pos' => CommonUpgrader::DESPRES,
                             'modif' => "m"]
                            ];
                $dataChanged = $this->updateTemplateByInsert($dataChanged, $aTokIns);

                $aTokRep = [["(^La Qualificació Final del crèdit.*?següent)( sempre.*?)(:)",
                             '$1<WIOCCL:IF condition="{#_SUMA({#_SUMA({##notaMinimaEAF##},{##notaMinimaJT##})_#},{##notaMinimaPAF##})_#}\\>0">$2</WIOCCL:IF>$3']
                           ];
                $dataChanged = $this->updateTemplateByReplace($dataChanged, $aTokRep);

                $aTokRep = [["(^<WIOCCL:IF condition=\"\{##keyJT##\}===false && \{##keyEAF##\}===false)(\">)",
                             "$1 && {##notaMinimaPAF##}\\>0$2"]
                           ];
                $dataChanged = $this->updateTemplateByReplace($dataChanged, $aTokRep);

                $aTokRep = [["(^<WIOCCL:IF condition=\")(\{##keyJT##\}!==false)( \|\| )(\{##keyEAF##\}!==false )(\">)$",
                             "$1($2 && {##notaMinimaJT##}\\>0)$3($4&& {##notaMinimaEAF##}\\>0)$5"]
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
