<?php
/**
 * Cierra todas las pestañas del contenedor central de la dokuwiki
 * @author culpable Rafa
*/

//
//[JOSEP] ALERTA! Això només és unh exemple no té cap funció, actualment dins la WIKI
//
if (!defined("DOKU_INC")) die();

class CloseAllTabsAction extends DokuExtraAction {

    protected function startProcess() {}
    protected function runProcess() {}
    protected function responseProcess() {}

    /**
     * Devuelve un array con los parámetros que tipifican esta acción
     * @return type array
     */
    public static function getActionParams() {
        return self::setActionParams();
    }

    /**
     * Crea un array con los parámetros que tipifican esta acción
     * @return type array
     */
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
        $cmdParams = array('urlBase' => 'lib/exe/ioc_ajax.php?call=close');

        // Contrucción del array con los parámetros que tipifican esta acción
        $action = array('type' => $actionType
                        ,'id' => $id
                        ,'label' => $label
                        ,'duration' => $duration
                        ,'cmdParams' => $cmdParams
                       );
        return $action;
    }

}
