<?php
/**
 * upgrader_2: Transforma la estructura de datos y el archivo continguts.txt de los proyectos 'ptfploe'
 *             desde la versión 1 a la versión 2
 * @author rafael
 */
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_LIB_IOC')) define('DOKU_LIB_IOC', DOKU_INC."lib/lib_ioc/");
require_once DOKU_LIB_IOC . "upgrader/CommonUpgrader.php";

class upgrader_2 extends CommonUpgrader {

    public function process($type, $ver, $filename=NULL) {
        switch ($type) {
            case "fields":
                $dataProject = $this->model->getCurrentDataProject($this->metaDataSubSet);
                $dataProject = IocCommon::toArrayThroughArrayOrJson($dataProject);

                //Añade el campo 'hiHaSolucio' a la tabla 'datesEAF'
                $dataProject = $this->addFieldInMultiRow($dataProject, "datesEAF", "hiHaSolucio", TRUE);
                $ret = $this->model->setDataProject(json_encode($dataProject), "Upgrade fields: version ".($ver-1)." to $ver", '{"fields":'.$ver.'}');
                break;

            case "templates":
                /*
                    linea 82:
                    ::table:T03
                      :title:Unitats
                      :type:pt_taula
                    .*
                    :::
                    ------------------------------------------------
                    linea 198:
                    L'AC es realitza a distància, es concreta en:
                    .*
                    Les activitats de l'avaluació contínua
                    ------------------------------------------------
                    linea 311:
                    La qualificació final de les unitats formatives.*:
                    .*
                    La qualificació final és numèrica
                    ------------------------------------------------
                    linea 328:
                    Si la qualificació de la PAF és inferior a {##notaMinimaPAF##},00, el càlcul de cada QUF serà:
                    .*
                    En cas de no superar la UF, el següent semestre s'han de tornar a realitzar
                    ------------------------------------------------
                    linea 351:
                    ====== Planificació ======
                    .*
                */
                if ($filename===NULL) { //Ojo! Ahora se pasa por parámetro
                    $filename = $this->model->getProjectDocumentName();
                }
                $data = $this->model->getCurrentDataProject();
                $template_name = $this->model->getTemplateContentDocumentId($data);

                $file = $this->model->getTemplatePath($template_name, 'v2');
                $doc0 = io_readFile($file);
                $doc1 = $this->model->getRawProjectDocument($filename);
                $aTokSub = ["(::table:T03\n\s+:title:Unitats\s+:type:pt_taula\n)(.*\n)*(:::)",
                            "(L'AC es realitza a distància, es concreta en:\n)(.*\n)*(Les activitats de l'avaluació contínua)",
                            "(La qualificació final de les unitats formatives.*:\n)(.*\n)*(La qualificació final és numèrica)",
                            "(Si la qualificació de la PAF és inferior a .* el càlcul de cada QUF serà:\n)(.*\n)*(En cas de no superar la UF,)",
                            "(====== Planificació ======\n)(.*\n)*"];
                $dataChanged = $this->updateTemplateBySubstitute($doc0, $doc1, $aTokSub);

                if (($ret = !empty($dataChanged))) {
                    $this->model->setRawProjectDocument($filename, $dataChanged, "Upgrade templates: version ".($ver-1)." to $ver", $ver);
                }
                break;
        }
        return $ret;
    }

}
