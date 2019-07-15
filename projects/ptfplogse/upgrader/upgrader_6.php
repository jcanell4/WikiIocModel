<?php
/**
 * upgrader_6: Transforma el archivo continguts.txt del proyecto "ptfplogse"
 *             desde la versión 5 a la versión 6
 * @culpable rafael 26-06-2019
 */
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_LIB_IOC')) define('DOKU_LIB_IOC', DOKU_INC . "lib/lib_ioc/");
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_INC . "lib/plugins/wikiiocmodel/");
define('WIKI_IOC_PROJECT', WIKI_IOC_MODEL."projects/ptfplogse/");
require_once DOKU_LIB_IOC . "upgrader/CommonUpgrader.php";

class upgrader_6 extends CommonUpgrader {

    protected $model;
    protected $metaDataSubSet;

    public function __construct($model) {
        $this->model = $model;
        $this->metaDataSubSet = $this->model->getMetaDataSubSet();
    }

    public function process($type, $filename = NULL) {
        switch ($type) {
            case "fields":
                $status = TRUE;
                break;

            case "templates":
                if ($filename===NULL) { //se supone que $filename se pasa por parámetro
                    $filename = $this->model->getProjectDocumentName();
                }
                $doc1 = $this->model->getRawProjectDocument($filename);
                // Inserta etiquetas 'protected' en todo el documento
                //$doc = $this->putProtectedTag($doc1);

                $plantilla_5 = @file_get_contents(WIKI_IOC_PROJECT."metadata/plantilles/continguts.txt.v5");
                $plantilla_6 = @file_get_contents(WIKI_IOC_PROJECT."metadata/plantilles/continguts.txt.v6");

                //actualiza el doc1 del usuario en base a la plantilla
                $doc = $this->updateFromTemplatesWithTodoTags($plantilla_5, $plantilla_6, $doc1);

                /*Correció  del doble slash!
                /*
                    Es canvia "{##item_act[descripció]##} \ </WIOCCL:FOREACH>     ||"
                                   per "{##item_act[descripció]##} \\ </WIOCCL:FOREACH>     ||"
                */
                $aTokRep = [
                    [
                        "\\| \\<WIOCCL:FOREACH  var\\=\"item_act\" array\\=\"\\{##activitatsPerUD##\\}\" filter\\=\"\\{##item_act\\[nucli activitat\\]##\\}\\=\\=\\{##itemu\\[nucli activitat\\]##\\}\"\\>\\- \\{##item_act\\[descripció\\]##\\} \\\\ \<\/WIOCCL:FOREACH\>",
                        "| <WIOCCL:FOREACH  var=\"item_act\" array=\"{##activitatsPerUD##}\" filter=\"{##item_act[nucli activitat]##}=={##itemu[nucli activitat]##}\">- {##item_act[descripció]##} \\\\\\\\ </WIOCCL:FOREACH>"
                    ]
                ];
                $doc = $this->updateTemplateByReplace($doc, $aTokRep);

                if (!empty($doc)) {
                    $this->model->setRawProjectDocument($filename, $doc, "Upgrade: version 5 to 6");
                }
                $status = !empty($doc);
        }
        return $status;
    }

    /**
     * Inserta tags 'protected' en el documento alrededor de ciertos elementos
     * NOTA: función actualmente sin uso
     * @param string $doc
     * @return string
     */
    private function putProtectedTag($doc) {
        $pini = ":###\n";
        $pfin = "\n###:\n";
        $nl = "\n";

        $patternIniIF = "/^<WIOCCL:IF/m";
        $patternFinIF = "/^<\/WIOCCL:IF>/m";
        $patternIniFor = "/^<WIOCCL:FOR /m";
        $patternFinFor = "/^<\/WIOCCL:FOR>/m";
        $patternIniForeach = "/^<WIOCCL:FOREACH/m";
        $patternFinForeach = "/^<\/WIOCCL:FOREACH>/m";
        $patternIniTable = "/^::table:/m";
        $patternFinTable = "/^:::/m";

        $pendent_if = 0;      //tags pendientes de cerrar
        $pendent_for = 0;
        $pendent_foreach = 0;
        $pendent_table = 0;

        $lineas = preg_split('/\n/', $doc);
        foreach ($lineas as $linea) {
            if ($linea === "") {
                $ret .= $nl;
                continue;
            }
            if (!($pendent_if || $pendent_for || $pendent_foreach || $pendent_table)) {
                if (preg_match($patternIniIF, $linea) === 1) {
                    $ret .= $pini.$nl.$linea.$nl;
                    ++$pendent_if;
                    continue;
                }
                if (preg_match($patternIniFor, $linea) === 1) {
                    $ret .= $pini.$nl.$linea.$nl;
                    ++$pendent_for;
                    continue;
                }
                if (preg_match($patternIniForeach, $linea) === 1) {
                    $ret .= $pini.$nl.$linea.$nl;
                    ++$pendent_foreach;
                    continue;
                }
                if (preg_match($patternIniTable, $linea) === 1) {
                    $ret .= $pini.$nl.$linea.$nl;
                    ++$pendent_table;
                    continue;
                }
                $ret .= $pini.$linea.$pfin;
            }
            else {
                if ($pendent_if) {
                    if (preg_match($patternIniIF, $linea) === 1) {
                        //almacena los bloques anidados
                        ++$pendent_if;
                        continue;
                    }
                    if (preg_match($patternFinIF, $linea) === 1) {
                        if ($pendent_if === 1) {
                            //finaliza, si procede, el bloque
                            $ret .= $linea.$nl.$pfin;
                        }
                        --$pendent_if;
                        continue;
                    }
                }
                if ($pendent_for) {
                    if (preg_match($patternIniFor, $linea) === 1) {
                        ++$pendent_for;
                        continue;
                    }
                    if (preg_match($patternFinFor, $linea) === 1) {
                        if ($pendent_for === 1) {
                            $ret .= $linea.$nl.$pfin;
                        }
                        --$pendent_for;
                        continue;
                    }
                }
                if ($pendent_foreach) {
                    if (preg_match($patternIniForeach, $linea) === 1) {
                        ++$pendent_foreach;
                        continue;
                    }
                    if (preg_match($patternFinForeach, $linea) === 1) {
                        if ($pendent_foreach === 1) {
                            $ret .= $linea.$nl.$pfin;
                        }
                        --$pendent_foreach;
                        continue;
                    }
                }
                if ($pendent_table) {
                    if (preg_match($patternIniTable, $linea) === 1) {
                        ++$pendent_table;
                        continue;
                    }
                    if (preg_match($patternFinTable, $linea) === 1) {
                        if ($pendent_table === 1) {
                            $ret .= $linea.$nl.$pfin;
                        }
                        --$pendent_table;
                        continue;
                    }
                }
                $ret .= $linea.$nl;
            }
        }

        return $ret;
    }
}