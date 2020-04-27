<?php
/**
 * Define y muestra los botones de un proyecto a partir de un fichero de control y de un template
 * @culpable Rafael Claver
 */
if (!defined("DOKU_INC")) die();
require_once (DOKU_INC . "inc/pageutils.php");

class action_plugin_wikiiocmodel_projects_ptfploe extends WikiIocProjectPluginAction {

    public function __construct($projectType, $dirProjectType) {
        parent::__construct($projectType, $dirProjectType);
    }

    function register(Doku_Event_Handler $controller) {
        $controller->register_hook('ADD_TPL_CONTROLS', "AFTER", $this, "addWikiIocButtons", array());
        $controller->register_hook('ADD_TPL_CONTROL_SCRIPTS', "AFTER", $this, "addControlScripts", array());
        $controller->register_hook('WIOC_PROCESS_RESPONSE_project', "AFTER", $this, "setExtraMeta", array());
        $controller->register_hook('WIOC_PROCESS_RESPONSE_projectUpdate', "AFTER", $this, "setExtraMeta", array());
        $controller->register_hook('WIOC_PROCESS_RESPONSE_projectExport', "AFTER", $this, "setExtraMeta", array());
        $controller->register_hook('WIOC_PROCESS_RESPONSE_ftpsend', "AFTER", $this, "setExtraMeta", array());
    }

    /**
     * Rellena de información una pestaña de la zona de MetaInformación
     */
    function setExtraMeta(&$event, $param) {
        //controlar que se trata del proyecto en curso
        if ($event->data['requestParams']['projectType'] === $this->projectType) {

            if (!isset($event->data['responseData'][ProjectKeys::KEY_CODETYPE])) {
                $result['ns'] = getID();
                $result['id'] = str_replace(':', '_', $result['ns']);
                $result['ext'] = ".zip";
                if (class_exists("ResultsWithFiles", TRUE)){
                    $html = ResultsWithFiles::get_html_metadata($result) ;
                }

                $event->data["ajaxCmdResponseGenerator"]->addExtraMetadata(
                            $result['id'],
                            $result['id']."_iocexport",
                            WikiIocLangManager::getLang("metadata_export_title"),
                            $html
                            );

                $event->data["ajaxCmdResponseGenerator"]->addExtraMetadata(
                        $result['id'],
                        $result['id']."_ftpsend",
                        WikiIocLangManager::getLang("metadata_ftpsend_title"),
                        $event->data['responseData'][AjaxKeys::KEY_FTPSEND_HTML]
                );
            }
        }
        return TRUE;
    }
}
