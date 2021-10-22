<?php
/**
 * upgrader_11: Transforma el archivo continguts.txt del proyecto "ptfploe"
 *              desde la versión 10 a la versión 11
 * @culpable rafael 09-07-2019
 */
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_LIB_IOC')) define('DOKU_LIB_IOC', DOKU_INC . "lib/lib_ioc/");
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_INC . "lib/plugins/wikiiocmodel/");
define('WIKI_IOC_PROJECT', WIKI_IOC_MODEL."projects/ptfploe/");
require_once DOKU_LIB_IOC . "upgrader/CommonUpgrader.php";

class upgrader_11 extends CommonUpgrader {

    public function process($type, $ver, $filename = NULL) {
        switch ($type) {
            case "fields":
                $status = TRUE;
                break;

            case "templates":
                if ($filename===NULL) {
                    $filename = $this->model->getProjectDocumentName();
                }
                $doc1 = $this->model->getRawProjectDocument($filename);
                $plantilla_10 = @file_get_contents(WIKI_IOC_PROJECT."metadata/plantilles/continguts.txt.v10");
                $plantilla_11 = @file_get_contents(WIKI_IOC_PROJECT."metadata/plantilles/continguts.txt.v11");

                //actualiza el doc1 del usuario a partir de las diferencias entre las plantillas excluyendo los tags ##TODO
                $doc = $this->updateFromTemplatesWithTodoTags($plantilla_10, $plantilla_11, $doc1);

                /*Correció  del doble slash!
                /*
                    Es canvia "{##item_act[descripció]##} \ </WIOCCL:FOREACH>     ||"
                                   per "{##item_act[descripció]##} \\ </WIOCCL:FOREACH>     ||"
                */
                $aTokRep = [
                    [
                        "\\| \\<WIOCCL:FOREACH  var\\=\"item_act\" array\\=\"\\{##activitatsAprenentatge##\\}\" filter\\=\"\\{##item_act\\[unitat\\]##\\}\\=\\=\\{##item_per\\[unitat\\]##\\}\\&\\&\\{##item_act\\[període\\]##\\}\\=\\=\\{##item_per\\[període\\]##\\}\"\\>\\- \\{##item_act\\[descripció\\]##\\} \\\\ \<\/WIOCCL:FOREACH\>",
                        "| <WIOCCL:FOREACH  var=\"item_act\" array=\"{##activitatsAprenentatge##}\" filter=\"{##item_act[unitat]##}=={##item_per[unitat]##}&&{##item_act[període]##}=={##item_per[període]##}\">- {##item_act[descripció]##} \\\\\\\\ </WIOCCL:FOREACH>"
                    ]
                ];
                $doc = $this->updateTemplateByReplace($doc, $aTokRep);

                if (($status = !empty($doc))) {
                    $this->model->setRawProjectDocument($filename, $doc, "Upgrade templates: version ".($ver-1)." to $ver", $ver);
                }
                break;
        }
        return $status;
    }

}
