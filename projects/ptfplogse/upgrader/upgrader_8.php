<?php
/**
 * upgrader_8: Transforma el archivo continguts.txt del proyecto "ptfplogse" desde la versión 6 a la versión 7
 *              sustituye, en el doc del usuario, el contenido incluido entre los tags protected
 *              por el contenido de los tags protected de la nueva plantilla
 * @culpable Josep 06-09-2019
 */
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_LIB_IOC')) define('DOKU_LIB_IOC', DOKU_INC . "lib/lib_ioc/");
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_INC . "lib/plugins/wikiiocmodel/");
define('WIKI_IOC_PROJECT', WIKI_IOC_MODEL."projects/ptfplogse/");
require_once DOKU_LIB_IOC . "upgrader/CommonUpgrader.php";

class upgrader_8 extends CommonUpgrader {

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
                $matches=array();
                preg_match("/([MC]\d{2})? *-? *(.+)/", $dataProject["credit"], $matches); 
                if(empty($matches[1])){
                    $dataProject['creditId']="";
                }else{
                    $dataProject['creditId']=$matches[1];
                }
                $dataProject['credit'] = $matches[2];

                $status = $this->model->setDataProject(json_encode($dataProject), "Upgrade fields: version ".($ver-1)." to $ver", '{"fields":'.$ver.'}');
                break;

            case "templates":
                if ($filename===NULL) {
                    $filename = $this->model->getProjectDocumentName();
                }
                $doc = $this->model->getRawProjectDocument($filename);
                $plantilla = @file_get_contents(WIKI_IOC_PROJECT."metadata/plantilles/continguts.txt.v8");

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
