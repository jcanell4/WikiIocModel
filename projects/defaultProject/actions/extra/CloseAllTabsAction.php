<?php

if (!defined("DOKU_INC")) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
if (!defined('DOKU_DEFAULT_PROJECT')) define('DOKU_DEFAULT_PROJECT', DOKU_PLUGIN . 'wikiiocmodel/projects/defaultProject/');

require_once DOKU_DEFAULT_PROJECT . "DokuExtraAction.php";

/**
 * Cierra todas las pestañas del contenedor central de la dokuwiki
  * @author culpable Rafa
*/
class CloseAllTabsAction extends DokuExtraAction {

    public static function getActionParams() {
        return self::setActionParams();
    }

    private function setActionParams() {
        // Establecimiento del valor de las variables 
        // Tipo de acción: describe si es necesaria la intervención del usuario o si se trata de una acción interna
        $actionType = array('intervention' => self::KEY_NEED_USER_INTERVENTION,
                            'level' => self::KEY_USUAL_INTERVENTION);
        
        // Parámetros generales de la acción
        $id = 'id_closeAllTabs';
        $label = 'CloseAllTabs';
        $duration = self::KEY_LONG_DURATION;
        
        // Array de parámetros del comando de la acción
        $cmdParams = array('urlBase' => 'lib/plugins/ajaxcommand/ajax.php?call=close');
                
        $action = array('type' => $actionType
                        ,'id' => $id
                        ,'label' => $label
                        ,'duration' => $duration
                        ,'cmdParams' => $cmdParams
                       );
        return $action;
    }

}
