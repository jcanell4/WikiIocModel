<?php
/**
 * upgrader_10: Transforma el archivo continguts.txt de los proyectos 'prgfploe'
 *             desde la versión 9 a la versión 10
 * @author rafael <rclaver@xtec.cat>
*/
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_LIB_IOC')) define('DOKU_LIB_IOC', DOKU_INC."lib/lib_ioc/");
require_once DOKU_LIB_IOC . "upgrader/CommonUpgrader.php";

class upgrader_10 extends CommonUpgrader {

    public function process($type, $ver, $filename=NULL) {
        switch ($type) {
            case "fields":
                //Transforma los datos del proyecto desde la estructura de la versión $ver a la versión $ver+1
                $ret = true;
                break;
            case "templates":
                // Actualiza la versión del documento establecido en el sistema de calidad del IOC (Visible en el pie del documento)
                // Sólo se debe actualizar si el coordinador de calidad lo indica!!!!!!
                /*
                $dataProject = $this->model->getCurrentDataProject($this->metaDataSubSet);
                if (!is_array($dataProject))
                    $dataProject = json_decode($dataProject, TRUE);
                $dataProject['documentVersion'] = $dataProject['documentVersion']+1;
                $ret = $this->model->setDataProject(json_encode($dataProject), "Upgrade fields: version ".($ver-1)." to $ver", '{"fields":'.$ver.'}');
                 */

                //Transforma el archivo continguts.txt del proyecto desde la versión $ver a la versión $ver+1
                if ($filename===NULL)
                    $filename = $this->model->getProjectDocumentName();
                $doc = $this->model->getRawProjectDocument($filename);

                $aTokMov = [['regexp0' => "<WIOCCL:SET var=\"sum_ponderacio\".*\{##itemUf\[unitat formativa\]##\}\)_#\}\">\n",
                             'regexp1' => "<WIOCCL:IF condition=\"\{##keyUfJT##\}===false && .*",
                             'pos' => CommonUpgrader::ABANS,
                             'modif' => ""]
                           ];
                $dataChanged = $this->updateTemplateByMove($doc, $aTokMov);

                $aTokRep = [["<WIOCCL:SET var=\"length\".*\n<WIOCCL:SET var=\"ponderacioAC\".*\n\*\*QFU.*\n",
                             "<WIOCCL:SUBSET subsetvar=\"subtaulaIA\" array=\"{##taulaInstrumentsAvaluacio##}\" arrayitem=\"itemsub\" filter=\"{##itemsub[unitat formativa]##}=={##itemUf[unitat formativa]##}\">\n".
                             "<WIOCCL:SET var=\"sum_ponderacio_ia\" type=\"literal\" value=\"{#_ARRAY_GET_SUM({##subtaulaIA##},''ponderacio'')_#}\">\n".
                             "<WIOCCL:SET var=\"len\" value=\"{#_SUBS({#_ARRAY_LENGTH({##subtaulaIA##})_#},1)_#}\">\n".
                             "**QFU{##itemUf[unitat formativa]##} = <WIOCCL:FOREACH var=\"item\" array=\"{##subtaulaIA##}\" counter=\"contador\">{#_GET_PERCENT({##sum_ponderacio_ia##},{##item[ponderacio]##})_#}% {##item[id]##}<WIOCCL:IF condition=\"{##contador##}\<{##len##}\"> + </WIOCCL:IF></WIOCCL:FOREACH>**\n"]
                           ];
                $dataChanged = $this->updateTemplateByReplace($dataChanged, $aTokRep);

                $aTokIns = [['regexp' => "^###:\n\[##TODO: Si cal matisar la formula.*\n",
                             'text' => "</WIOCCL:SUBSET>\n",
                             'pos' => CommonUpgrader::ABANS,
                             'modif' => "m"]
                            ];
                $dataChanged = $this->updateTemplateByInsert($dataChanged, $aTokIns);

                $aTokRep = [["formula",
                             "fórmula"]
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
