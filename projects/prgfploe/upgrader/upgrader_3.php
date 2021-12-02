<?php
/**
 * upgrader_3: Transforma el archivo continguts.txt de los proyectos 'prgfploe'
 *             desde la versión 2 a la versión 3
 * @author rafael <rclaver@xtec.cat>
*/
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_LIB_IOC')) define('DOKU_LIB_IOC', DOKU_INC."lib/lib_ioc/");
require_once DOKU_LIB_IOC . "upgrader/CommonUpgrader.php";

class upgrader_3 extends CommonUpgrader {

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
                /*
                // Actualiza la versión del documento establecido en el sistema de calidad del IOC (Visible en el pie del documento)
                // Sólo se debe actualizar si el coordinador de claidad lo indica!!!!!!
                $dataProject = $this->model->getCurrentDataProject($this->metaDataSubSet);
                if (!is_array($dataProject))
                    $dataProject = json_decode($dataProject, TRUE);
                $dataProject['documentVersion'] = $dataProject['documentVersion']+1;
                $ret = $this->model->setDataProject(json_encode($dataProject), "Upgrade fields: version ".($ver-1)." to $ver", '{"fields":'.$ver.'}');
                */

                //Transforma el archivo continguts.txt de los proyectos
                if ($filename===NULL)
                    $filename = $this->model->getProjectDocumentName();
                $doc = $this->model->getRawProjectDocument($filename);

                $aTokDel = ["<WIOCCL:SET var=\"keyUfPAF\".*?\n"];
                $dataChanged = $this->updateTemplateByDelete($doc, $aTokDel);

                $aTokIns = [['regexp' => "^<WIOCCL:SET var=\"nombreEACs\".*?\n",
                             'text' => "<WIOCCL:SET var=\"keyUfPAF\" type=\"literal\" value=\"{#_SEARCH_KEY([{##itemUf[unitat formativa]##}, ''PAF''],{##taulaInstrumentsAvaluacio##}, [''unitat formativa'',''tipus''])_#}\">\n",
                             'pos' => CommonUpgrader::ABANS,
                             'modif' => "m"]
                           ];
                $dataChanged = $this->updateTemplateByInsert($dataChanged, $aTokIns);

                $aTokRep = [["(  \* El )(professor)(corregeix)",
                             "$1$2 $3"],
                            ["(<WIOCCL:IF condition=\"\{##keyUfPAF##\}!=false\">\n)<WIOCCL:IF condition=\"\{##keyUfPAF##\}!=false\">\n",
                             "$1"],
                            ["(  \* La qualificació de l'AC es té en compte.*\n)(<\/WIOCCL:IF>\n)<\/WIOCCL:IF>\n",
                             "$1$2"]
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
