<?php
/**
 * upgrader_1: Transforma la estructura de datos y el archivo continguts.txt de los proyectos 'ptce'
 *             desde la versión 0 a la versión 1
 * @author rafael
 * @adapter marjose
 */
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_LIB_IOC')) define('DOKU_LIB_IOC', DOKU_INC."lib/lib_ioc/");
require_once DOKU_LIB_IOC . "upgrader/CommonUpgrader.php";

class upgrader_1 extends CommonUpgrader {

    public function process($type, $ver, $filename=NULL) {
        switch ($type) {
            case "fields":
                //Transforma los datos del proyecto "ptce" desde la estructura de la versión 0 a la versión 1
                $dataProject = $this->model->getCurrentDataProject($this->metaDataSubSet);
                if (!is_array($dataProject)) {
                    $dataProject = json_decode($dataProject, TRUE);
                }
                //Añade el campo 'hiHaRecuperacio' a la tabla 'datesJT'
                //$dataProject = $this->addFieldInMultiRow($dataProject, "datesJT", "hiHaRecuperacio", TRUE);
                
                //Cambia el nombre del campo
                $dataProject = $this->changeFieldName($dataProject, "dataPaf11", "dataPv1");
                $dataProject = $this->changeFieldName($dataProject, "dataPaf12", "dataPv2");
                $dataProject = $this->changeFieldName($dataProject, "dataPaf21", "dataPaf1");
                $dataProject = $this->changeFieldName($dataProject, "dataPaf22", "dataPaf2");
                $dataProject = $this->changeFieldName($dataProject, "dataQualificacioPaf1", "dataQualificacioPv");
                $dataProject = $this->changeFieldName($dataProject, "dataQualificacioPaf2", "dataQualificacioPaf");
                
                $ret = $this->model->setDataProject(json_encode($dataProject), "Upgrade fields: version ".($ver-1)." to $ver", '{"fields":'.$ver.'}');
                break;
                
                
                
                
                
                /*
                 * 
                //Transforma los datos del proyecto "ptfploe" desde la estructura de la versión 8 a la versión 9
                $dataProject = $this->model->getCurrentDataProject($this->metaDataSubSet);
                if (!is_array($dataProject)) {
                    $dataProject = json_decode($dataProject, TRUE);
                }
                //Cambia el nombre del campo
                $dataProject = $this->changeFieldName($dataProject, "dataPaf1", "dataPaf11");
                $dataProject = $this->changeFieldName($dataProject, "dataPaf2", "dataPaf21");

                //Añade un campo en el primer nivel de la estructura de datos
                $dataProject = $this->addNewField($dataProject, "dataPaf12", $dataProject['dataPaf11']);
                $dataProject = $this->addNewField($dataProject, "dataPaf22", $dataProject['dataPaf21']);

                $status = $this->model->setDataProject(json_encode($dataProject), "Upgrade fields: version ".($ver-1)." to $ver (simultànea a la actualització de 25 a 26 de templates)", '{"fields":'.$ver.'}');
                break;
                 */

            case "templates":
                // Línia 96.  Es canvia "  :title:Taula Unitats" per "  :title:Apartats"
                // Línia 102. Es canvia "{#_DATE("{##itemc[inici]##}", ".")_#}-{#_DATE("{##itemc[inici]##}" per "{#_DATE("{##itemc[inici]##}", ".")_#}-{#_DATE("{##itemc[final]##}"

                
                //Línia 275. Es canvia "|  1  |  {#_DATE("{##dataPaf11##}")_#}<WIOCCL:IF condition="!{#_IS_STR_EMPTY(''{##dataPaf12##}'')_#}"> o {#_DATE("{##dataPaf12##}")_#}</WIOCCL:IF>  |  {#_DATE("{##dataQualificacioPaf1##}")_#}  |" per "|  PV  |  {#_DATE("{##dataPv1##}")_#}<WIOCCL:IF condition="!{#_IS_STR_EMPTY(''{##dataPv2##}'')_#}"> o {#_DATE("{##dataPv2##}")_#}</WIOCCL:IF>  |  {#_DATE("{##dataQualificacioPv##}")_#}  |"
                //Línia 276. Es canvia "|  2  |  {#_DATE("{##dataPaf21##}")_#}<WIOCCL:IF condition="!{#_IS_STR_EMPTY(''{##dataPaf22##}'')_#}"> o {#_DATE("{##dataPaf22##}")_#}</WIOCCL:IF>  |  {#_DATE("{##dataQualificacioPaf2##}")_#}  |" per "|  PAF  |  {#_DATE("{##dataPaf1##}")_#}<WIOCCL:IF condition="!{#_IS_STR_EMPTY(''{##dataPaf2##}'')_#}"> o {#_DATE("{##dataPaf2##}")_#}</WIOCCL:IF>  |  {#_DATE("{##dataQualificacioPaf##}")_#}  |"
                
                if ($filename===NULL) { //Ojo! Ahora se pasa por parámetro
                    $filename = $this->model->getProjectDocumentName();
                }
                $doc = $this->model->getRawProjectDocument($filename);

                /*
                $aTokRep = [["dataPaf11","dataPv1"],
                ["dataPaf12","dataPv2"],
                ["|  1  |  {#_DATE\(","|  PV  |  \{#_DATE\("],
                ["dataPaf21","dataPaf1"],
                ["dataPaf22","dataPaf2"],
                ["|  2  |  {#_DATE\(","|  PAF  |  \{#_DATE\("]];
                 * */
                
                /*
                $aTokRep = [[" +:title:Taula Unitats",
                             "  :title:Apartats"],
                            ["{#_DATE\(\"{##itemc\[inici\]##}\", \"\.\"\)_#}-{#_DATE\(\"{##itemc\[inici\]##}",
                             "{#_DATE(\"{##itemc[inici]##}\", \".\")_#}-{#_DATE(\"{##itemc[final]##}"]];
                $dataChanged = $this->updateTemplateByReplace($doc, $aTokRep);
                */
                
                
                $aTokRep = [
                    ["  1  \\| \\< \\{#_DATE\\(\"\\{##dataPaf11##\\}\")_#\\}\\<WIOCCL:IF condition\\=\"!\\{#_IS_STR_EMPTY\\(''\\{##dataPaf12##\\}'')_#\\}\"\\> o \\{#_DATE\\(\"\\{##dataPaf12##\\}\")_#\\}\\</WIOCCL:IF\\>  \\|  \\{#_DATE\\(\"\\{##dataQualificacioPaf1##\\}\")_#\\}  \\|" , 
                     "  PV  \\| \\< \\{#_DATE\\(\"\\{##dataPv1##\\}\")_#\\}\\<WIOCCL:IF condition\\=\"!\\{#_IS_STR_EMPTY\\(''\\{##dataPv2##\\}'')_#\\}\"\\> o \\{#_DATE\\(\"\\{##dataPv2##\\}\")_#\\}\\</WIOCCL:IF\\>  \\|  \\{#_DATE\\(\"\\{##dataQualificacioPv##\\}\")_#\\}  \\|"
                    ],
                    ["  2  \\| \\< \\{#_DATE\\(\"\\{##dataPaf21##\\}\")_#\\}\\<WIOCCL:IF condition\\=\"!\\{#_IS_STR_EMPTY\\(''\\{##dataPaf22##\\}'')_#\\}\"\\> o \\{#_DATE\\(\"\\{##dataPaf22##\\}\")_#\\}\\</WIOCCL:IF\\>  \\|  \\{#_DATE\\(\"\\{##dataQualificacioPaf2##\\}\")_#\\}  \\|" , 
                     "  PAF  \\| \\< \\{#_DATE\\(\"\\{##dataPaf##\\}\")_#\\}\\<WIOCCL:IF condition\\=\"!\\{#_IS_STR_EMPTY\\(''\\{##dataPaf##\\}'')_#\\}\"\\> o \\{#_DATE\\(\"\\{##dataPaf##\\}\")_#\\}\\</WIOCCL:IF\\>  \\|  \\{#_DATE\\(\"\\{##dataQualificacioPaf##\\}\")_#\\}  \\|"
                    ]                    
                ];
                $dataChanged = $this->updateTemplateByReplace($doc, $aTokRep);


                if (($ret = !empty($dataChanged))) {
                    $this->model->setRawProjectDocument($filename, $dataChanged, "Upgrade templates: version ".($ver-1)." to $ver", $ver);
                }
                break;
        }
        return $ret;
    }

}
