<?php
/**
 * upgrader_2: Transforma el archivo continguts.txt de los proyectos 'prgfploe'
 *             desde la versión 1 a la versión 2
 * @author rafael <rclaver@xtec.cat>
*/
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_LIB_IOC')) define('DOKU_LIB_IOC', DOKU_INC."lib/lib_ioc/");
require_once DOKU_LIB_IOC . "upgrader/CommonUpgrader.php";

class upgrader_2 extends ProgramacionsCommonUpgrader {

    public function process($type, $ver, $filename=NULL) {
        switch ($type) {
            case "fields":
                //Transforma los datos del proyecto desde la estructura de la versión 1 a la versión 2
                $dataProject = $this->model->getCurrentDataProject($this->metaDataSubSet);
                $dataProject = IocCommon::toArrayThroughArrayOrJson($dataProject);

                $dataProject['estrategiesMetodologiques'] = preg_replace("/<p>(\s*&(amp;)*lt;p&(amp;)*gt;)*\s*(.*?)(\s*&(amp;)*lt;\/p&(amp;)*gt;)*\s*<\/p>/s", "$4", $dataProject['estrategiesMetodologiques']);

                $ret = $this->model->setDataProject(json_encode($dataProject), "Upgrade fields: version ".($ver-1)." to $ver. Simultànea a l'actualització de 18 a 19 de templates", '{"fields":'.$ver.'}');
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

                $aTokRep = [["l’ EAF",
                             "l'EAF"],
                            ["’",
                             "'"]
                           ];
                $dataChanged = $this->updateTemplateByReplace($doc, $aTokRep);

                $aTokIns = [['regexp' => "^ *\* Presentar-se a l'EAF i en cas de no.*\n",
                             'text' => "<WIOCCL:IF condition=\"true!=={#_ARRAY_GET_VALUE(''treballEnEquip'',{#_ARRAY_GET_VALUE(''{##keyUfEAF##}'',{##taulaInstrumentsAvaluacio##},[])_#},false)_#}\">\n",
                             'pos' => CommonUpgrader::DESPRES,
                             'modif' => "m"],
                            ['regexp' => "^ *\* Presentar-se directament a l'EAF recuperació\.\n",
                             'text' => "</WIOCCL:IF>\n",
                             'pos' => CommonUpgrader::DESPRES,
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
