<?php
/**
 * Define y muestra los botones de un proyecto a partir de un fichero de control y de un template
 * @culpable Rafael Claver
 */
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . "lib/plugins/");
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_PLUGIN . "wikiiocmodel/");
require_once (DOKU_INC . "inc/pageutils.php");
require_once (WIKI_IOC_MODEL . "WikiIocPluginAction.php");

class action_plugin_wikiiocmodel_projects_qdoc extends WikiIocPluginAction {
    private $dirProjectType;
    private $viewArray;

    public function __construct($projectType, $dirProjectType) {
        parent::__construct();
        $this->projectType = $projectType;
        $this->dirProjectType = $dirProjectType;
        $this->viewArray = $this->projectMetaDataQuery->getMetaViewConfig("controls", $projectType);
    }

    function register(Doku_Event_Handler $controller) {
        $controller->register_hook('ADD_TPL_CONTROLS', "AFTER", $this, "addWikiIocButtons", array());
        $controller->register_hook('ADD_TPL_CONTROL_SCRIPTS', "AFTER", $this, "addControlScripts", array());
        $controller->register_hook('WIOC_PROCESS_RESPONSE_project', "AFTER", $this, "setExtraMeta", array());
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
                if (class_exists("ResultsWithFiles", TRUE)){
                    $html = ResultsWithFiles::get_html_metadata($result) ;
                }

                $event->data["ajaxCmdResponseGenerator"]->addExtraMetadata(
                            $result['id'],
                            $result['id']."_iocexport",
                            WikiIocLangManager::getLang("metadata_export_title"),
                            $html
                            );
            }
        }
        return TRUE;
    }

    function addControlScripts(Doku_Event &$event, $param) {
        $aux = array(
            "dirProjectType" => $this->dirProjectType,
            "viewArray" => $this->viewArray,
            "projectType" => $this->projectType
        );
        IocCommon::addControlScripts($event, $param, $aux);
    }

    function addWikiIocButtons(Doku_Event &$event, $param) {
        $aux = array(
            "viewArray" => $this->viewArray
        );
        IocCommon::addWikiIocButtons($event, $param, $aux);
    }
}
