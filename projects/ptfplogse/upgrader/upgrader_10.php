<?php
/**
 * upgrader_10: Transforma el archivo de proyecto 'ptfplogse' desde la versión 9 a la versión 10
 *              y el archivo continguts.txt
 * @culpable Josep 06-09-2019
 * @author rafael <rclaver@xtec.cat>
 */
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_LIB_IOC')) define('DOKU_LIB_IOC', DOKU_INC . "lib/lib_ioc/");
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_INC . "lib/plugins/wikiiocmodel/");
define('WIKI_IOC_PROJECT', WIKI_IOC_MODEL."projects/ptfplogse/");
require_once DOKU_LIB_IOC . "upgrader/CommonUpgrader.php";

class upgrader_10 extends CommonUpgrader {

    public function process($type, $ver, $filename = NULL) {
        switch ($type) {
            case "fields":
                //Transforma los datos del proyecto "ptfplogse" desde la estructura de la versión 9 a la versión 10
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

                $status = $this->model->setDataProject(json_encode($dataProject), "Upgrade fields: version ".($ver-1)." to $ver. Simultànea a l'actualització de 18 a 19 de templates", '{"fields":'.$ver.'}');
                break;

            case "templates":
                if ($filename===NULL) {
                    $filename = $this->model->getProjectDocumentName();
                }
                $doc = $this->model->getRawProjectDocument($filename);
                $plantilla = @file_get_contents(WIKI_IOC_PROJECT."metadata/plantilles/continguts.txt.v10");

                //actualiza el doc del usuario en base a la plantilla
                $doc = $this->updateDocFromTemplateUsingProtectecTags($plantilla, $doc);

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
                    $this->model->setRawProjectDocument($filename, $doc, "Upgrade templates: version ".($ver-1)." to $ver", $ver);
                }
                $status = !empty($doc);
        }
        return $status;
    }

}
