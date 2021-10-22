<?php
/**
 * upgrader_5: Transforma la estructura de datos y el archivo continguts.txt de los proyectos 'ptfploe'
 *             desde la versión 4 a la versión 5
 * @author rafael
 */
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_LIB_IOC')) define('DOKU_LIB_IOC', DOKU_INC . "lib/lib_ioc/");
require_once DOKU_LIB_IOC . "upgrader/CommonUpgrader.php";

class upgrader_5 extends CommonUpgrader {

    public function process($type, $ver, $filename = NULL) {
        switch ($type) {
            case "fields":
                $dataProject = $this->model->getCurrentDataProject($this->metaDataSubSet);
                if (!is_array($dataProject)) {
                    $dataProject = json_decode($dataProject, TRUE);
                }// cerquem les dades de la paf1 i paf 2 i les qualificacions son de l'any 2019 i canviar-les per la mateixa data però 2020

                $dataProject['itinerarisRecomanats'] = [
                    [
                        "mòdul" => $dataProject['modul'],
                        "itinerariRecomanatS1" => $dataProject['itinerariRecomanatS1'],
                        "itinerariRecomanatS2" => $dataProject['itinerariRecomanatS2'],
                    ]
                ];

                $ret = $this->model->setDataProject(json_encode($dataProject), "Upgrade fields: version ".($ver-1)." to $ver", '{"fields":'.$ver.'}');
                break;

            case "templates":
                if ($filename === NULL) {
                    $filename = $this->model->getProjectDocumentName();
                }
                $data = $this->model->getCurrentDataProject();
                $template_name = $this->model->getTemplateContentDocumentId($data);

                $file = $this->model->getTemplatePath($template_name, 'v5');
                $doc0 = io_readFile($file);
                $doc1 = $this->model->getRawProjectDocument($filename);

                $aTokSub = ["(::table:T11-\{\#\#itemUf\[unitat formativa\]\#\#\}\n)(.*\n)*(:::)"];

                $dataChanged = $this->updateTemplateBySubstitute($doc0, $doc1, $aTokSub);

                if (($ret = !empty($dataChanged))) {
                    $this->model->setRawProjectDocument($filename, $dataChanged, "Upgrade templates: version ".($ver-1)." to $ver", $ver);
                }
                break;
        }
        return $ret;
    }

}
