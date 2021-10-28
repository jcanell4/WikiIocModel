<?php
/**
 * upgrader_6: Transforma el archivo continguts.txt de los proyectos 'prgfploe'
 *             desde la versión 5 a la versión 6
 * @author rafael <rclaver@xtec.cat>
*/
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_LIB_IOC')) define('DOKU_LIB_IOC', DOKU_INC."lib/lib_ioc/");
require_once DOKU_LIB_IOC . "upgrader/CommonUpgrader.php";

class upgrader_6 extends CommonUpgrader {

    public function process($type, $ver, $filename=NULL) {
        switch ($type) {
            case "fields":
                //Transforma los datos del proyecto desde la estructura de la versión $ver a la versión $ver+1
                $ret = true;
                break;
            case "templates":
                // Actualiza la versión del documento establecido en el sistema de calidad del IOC (Visible en el pie del documento)
                // Sólo se debe actualizar si el coordinador de claidad lo indica!!!!!!
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

                $aTokRep = [["(\*\*QFU\{##itemUf\[unitat formativa\]##\})(= \{#_GET_PERCENT\(\{##sum_ponderacio##\},\{##ponderacioAC##\}\)_#\}% AC)( \+ )",
                             "$1 $2<WIOCCL:IF condition=\"{##length##}\\>0\">$3</WIOCCL:IF>"]
                           ];
                $dataChanged = $this->updateTemplateByReplace($doc, $aTokRep);

                $aTokIns = [['regexp' => "<WIOCCL:IF condition=\"\{##keyUfEAF##\}===false && \{##keyUfJT##\}===false\">",
                             'text' => "\n<WIOCCL:IF condition=\"{##keyUfPAF##}===false && {##keyUfPAFV##}===false\">".
                                       "\nNo hi ha prova d'avaluació final.".
                                       "\n</WIOCCL:IF>".
                                       "\n<WIOCCL:IF condition=\"{##keyUfPAF##}!==false || {##keyUfPAFV##}!==false\">",
                             'pos' => CommonUpgrader::DESPRES,
                             'modif' => "m"],
                            ['regexp' => "<WIOCCL:IF condition=\"\{##keyUfEAF##\}!==false \|\| \{##keyUfJT##\}!==false\">",
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
