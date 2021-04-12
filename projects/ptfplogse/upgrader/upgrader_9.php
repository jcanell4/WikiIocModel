<?php
/**
 * upgrader_9: Transforma el archivo continguts.txt del proyecto "ptfplogse" desde la versión 8 a la versión 9
 *              sustituye, en el doc del usuario, el contenido incluido entre los tags protected
 *              por el contenido de los tags protected de la nueva plantilla,
 *              o bien, el _wikiIocSystem_.mdpr de los proyectos 'ptfplogse' desde la versión 8 a la versión 9
 * @culpable Josep 06-09-2019
 */
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_LIB_IOC')) define('DOKU_LIB_IOC', DOKU_INC . "lib/lib_ioc/");
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_INC . "lib/plugins/wikiiocmodel/");
define('WIKI_IOC_PROJECT', WIKI_IOC_MODEL."projects/ptfplogse/");
require_once DOKU_LIB_IOC . "upgrader/CommonUpgrader.php";

class upgrader_9 extends CommonUpgrader {

    protected $model;
    protected $metaDataSubSet;

    public function __construct($model) {
        $this->model = $model;
        $this->metaDataSubSet = $this->model->getMetaDataSubSet();
    }

    public function process($type, $ver, $filename = NULL) {
        switch ($type) {
            case "fields":
                $dataProject = $this->model->getCurrentDataProject($this->metaDataSubSet);
                if (!is_array($dataProject)) {
                    $dataProject = json_decode($dataProject, TRUE);
                }
                //Añade un campo en el primer nivel de la estructura de datos
                $name = "treballEquipEAF";
                $value = false;
                $dataProject = $this->addNewField($dataProject, $name, $value);

                $status = $this->model->setDataProject(json_encode($dataProject), "Upgrade fields: version ".($ver-1)." to $ver. Simultànea a l'actualització de 16 a 17 de templates", '{"fields":'.($ver-1).'}');
                break;

            case "templates":
                if ($filename===NULL) {
                    $filename = $this->model->getProjectDocumentName();
                }
                $doc = $this->model->getRawProjectDocument($filename);
                $plantilla = @file_get_contents(WIKI_IOC_PROJECT."metadata/plantilles/continguts.txt.v9");

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
                    $this->model->setRawProjectDocument($filename, $doc, "Upgrade: version 8 to 9");
                }
                $status = !empty($doc);
        }
        return $status;
    }

}
