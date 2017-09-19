<?php
/**
 * Define y muestra los botones de un proyecto a partir de un fichero de control y de un template
 * @culpable Rafael Claver
 */
if (!defined("DOKU_INC")) die();
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_INC . 'lib/plugins/wikiiocmodel/');
require_once (WIKI_IOC_MODEL . 'WikiIocPluginAction.php');

class action_plugin_wikiiocmodel_projects_documentation extends WikiIocPluginAction {
    private $viewArray;

    public function __construct($projectType) {
        parent::__construct();
        $this->projectType = $projectType;
        $this->viewArray = $this->projectMetaDataQuery->getMetaViewConfig($this->projectType, "controls");
    }

    function register(Doku_Event_Handler $controller) {
        $controller->register_hook('ADD_TPL_CONTROLS', "AFTER", $this, "addWikiIocButtons", array());
        $controller->register_hook('ADD_TPL_CONTROL_SCRIPTS', "AFTER", $this, "addControlScripts", array());
        $controller->register_hook('CALLING_EXTRA_COMMANDS', "AFTER", $this, "addCommands", array());        
    }

    function addCommands(Doku_Event &$event, $param) {
        $event->data["projectrender"] = array(
            "callFile" => WIKI_IOC_MODEL."projects/documentation/command/projectrender_command.php"
        );
    }


    function addControlScripts(Doku_Event &$event, $param) {
        $changeWidgetPropertyFalse = "";
        $changeWidgetPropertyCondition = "";
        $VarsIsButtonVisible = "";
        $permissionsButtonVisible = "";
        $conditionsButtonVisible = "";
        $path = WIKI_IOC_MODEL."projects/documentation/metadata/config/";

        //Lectura de los botones definidos en el fichero de control
        foreach ($this->viewArray as $nameButton => $arrayButton) {
            //carga de los archivos de funciones de los botones
            foreach ($arrayButton['scripts']['getFunctions'] as $key => $value) {
                if ($key === "path") $event->data->addControlScript($path.$value);
            }

            //Construcción de los valores de sustitución de los patrones para el template UpdateViewHandler
            //changeWidgetProperty para todos los botones
            $changeWidgetPropertyFalse .= "disp.changeWidgetProperty('$nameButton', 'visible', false);\n\t\t\t";
            $changeWidgetPropertyCondition .= "disp.changeWidgetProperty('$nameButton', 'visible', is${nameButton}ButtonVisible);\n\t\t\t\t";
            $VarsIsButtonVisible .= "var is${nameButton}ButtonVisible = true;\n\t\t\t\t\t";

            //bucle para que los permisos determinen si el botón correspondiente es visible u oculto
            $permButtonVisible = "";
            if ($arrayButton['scripts']['updateHandler']['permissions']) {
                $permButtonVisible = "is${nameButton}ButtonVisible = (";
                foreach ($arrayButton['scripts']['updateHandler']['permissions'] as $value) {
                    $permButtonVisible .= "disp.getGlobalState().permissions['$value'] || ";
                }
                $permButtonVisible = substr($permButtonVisible, 0, -3) . ");";
            }
            $permissionsButtonVisible .= $permButtonVisible . "\n\t\t\t\t\t\t";

            //bucle para que los roles determinen si el botón correspondiente es visible u oculto
            $rolButtonVisible = "";
            if ($arrayButton['scripts']['updateHandler']['rols']) {
                $rolButtonVisible = "is${nameButton}ButtonVisible = is${nameButton}ButtonVisible || (";
                foreach ($arrayButton['scripts']['updateHandler']['rols'] as $value) {
                    $rolButtonVisible .= "page.rol==='".$value."' || ";
                }
                $rolButtonVisible = substr($rolButtonVisible, 0, -3) . ");";
            }
            $rolesButtonVisible .= $rolButtonVisible . "\n\t\t\t\t\t\t";

            //bucle para que otras condiciones determinen si el botón correspondiente es visible u oculto
            $condButtonVisible = "";
            if ($arrayButton['scripts']['updateHandler']['conditions']) {
                $condButtonVisible = "is${nameButton}ButtonVisible = is${nameButton}ButtonVisible && (";
                foreach ($arrayButton['scripts']['updateHandler']['conditions'] as $key => $value) {
                    $condButtonVisible .= "$key===$value && ";
                }
                $condButtonVisible = substr($condButtonVisible, 0, -3) . ");";
            }
            $conditionsButtonVisible .= $condButtonVisible . "\n\t\t\t\t\t";

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
            $class = $arrayButton['class'];
            if ($arrayButton['parms']['DOM']) $button['DOM'] = $arrayButton['parms']['DOM'];
            if ($arrayButton['parms']['DJO']) $button['DJO'] = $arrayButton['parms']['DJO'];
            if ($arrayButton['parms']['CSS']) $button['CSS'] = $arrayButton['parms']['CSS'];
            if ($arrayButton['parms']['PRP']) $button['PRP'] = $arrayButton['parms']['PRP'];
            $event->data->addWikiIocButton($class, $button);
        }
    }
}
