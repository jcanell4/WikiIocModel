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
        $changeWidgetPropertyFalse = "";
        $changeWidgetPropertyCondition = "";
        $VarsIsButtonVisible = "";
        $permissionsButtonVisible = "";
        $conditionsButtonVisible = "";
        $path = "{$this->dirProjectType}metadata/config/";

        //Lectura de los botones definidos en el fichero de control
        foreach ($this->viewArray as $arrayButton) {
            if ($arrayButton['scripts']['getFunctions']) {
                //carga de los archivos de funciones de los botones
                foreach ($arrayButton['scripts']['getFunctions'] as $key => $value) {
                    if ($key === "path")
                        $event->data->addControlScript($path.$value);
                }
            }

            $id = $arrayButton['parms']['DOM']['id'];
            //Construcción de los valores de sustitución de los patrones para el template UpdateViewHandler
            //changeWidgetProperty para todos los botones
            $changeWidgetPropertyFalse .= "disp.changeWidgetProperty('${id}', 'visible', false);\n\t\t\t";
            $changeWidgetPropertyCondition .= "disp.changeWidgetProperty('${id}', 'visible', is${id}ButtonVisible);\n\t\t\t\t";
            $VarsIsButtonVisible .= "var is${id}ButtonVisible = true;\n\t\t\t\t\t";

            //bucle para que los permisos determinen si el botón correspondiente es visible u oculto
            $permButtonVisible = "";
            if ($arrayButton['scripts']['updateHandler']['permissions']) {
                $permButtonVisible = "is${id}ButtonVisible = (";
                foreach ($arrayButton['scripts']['updateHandler']['permissions'] as $value) {
                    $permButtonVisible .= "disp.getGlobalState().permissions['$value'] || ";
                }
                $permButtonVisible = substr($permButtonVisible, 0, -4) . ");";
            }
            $permissionsButtonVisible .= $permButtonVisible . "\n\t\t\t\t\t\t\t";

            //bucle para que los roles determinen si el botón correspondiente es visible u oculto
            $rolButtonVisible = "";
            if ($arrayButton['scripts']['updateHandler']['rols']) {
                $rolButtonVisible = "is${id}ButtonVisible = is${id}ButtonVisible || (";
                foreach ($arrayButton['scripts']['updateHandler']['rols'] as $value) {
                    $rolButtonVisible .= "page.rol=='".$value."' || ";
                }
                $rolButtonVisible = substr($rolButtonVisible, 0, -4) . ");";
            }
            $rolesButtonVisible .= $rolButtonVisible . "\n\t\t\t\t\t\t";

            //bucle para que otras condiciones determinen si el botón correspondiente es visible u oculto
            $condButtonVisible = "";
            if ($arrayButton['scripts']['updateHandler']['conditions']) {
                $condButtonVisible = "is${id}ButtonVisible = is${id}ButtonVisible && (";
                foreach ($arrayButton['scripts']['updateHandler']['conditions'] as $key => $value) {
                    $condButtonVisible .= "$key==$value && ";
                }
                $condButtonVisible = substr($condButtonVisible, 0, -4) . ");";
            }
            $conditionsButtonVisible .= $condButtonVisible . "\n\t\t\t\t\t\t";
        }

        $aReplacements["search"] = ["//%_changeWidgetPropertyFalse_%",
                                    "%_projectType_%",
                                    "//%_VarsIsButtonVisible_%",
                                    "//%_permissionButtonVisible_%",
                                    "//%_rolesButtonVisible_%",
                                    "//%_conditionsButtonVisible_%",
                                    "//%_changeWidgetPropertyCondition_%"];
        $aReplacements["replace"] = [$changeWidgetPropertyFalse,
                                     $this->projectType,
                                     $VarsIsButtonVisible,
                                     $permissionsButtonVisible,
                                     $rolesButtonVisible,
                                     $conditionsButtonVisible,
                                     $changeWidgetPropertyCondition];

        $arxiu =  WIKI_IOC_MODEL."metadata/templates/templateUpdateViewHandler.js";
        $event->data->addControlScript($arxiu, $aReplacements);
    }

    function addWikiIocButtons(Doku_Event &$event, $param) {
        //Lectura de los botones definidos en el fichero de control
        foreach ($this->viewArray as $arrayButton) {
            $button = array();
            if(isset($arrayButton['class'])){
                $class = $arrayButton['class'];
                if ($arrayButton['parms']['DOM']) $button['DOM'] = $arrayButton['parms']['DOM'];
                if ($arrayButton['parms']['DJO']) $button['DJO'] = $arrayButton['parms']['DJO'];
                if ($arrayButton['parms']['CSS']) $button['CSS'] = $arrayButton['parms']['CSS'];
                if ($arrayButton['parms']['PRP']) $button['PRP'] = $arrayButton['parms']['PRP'];
                $event->data->addWikiIocButton($class, $button);
            }
        }
    }
}
