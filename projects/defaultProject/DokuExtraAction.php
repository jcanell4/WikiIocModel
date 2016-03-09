<?php
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
if (!defined('DOKU_ACTIONS')) define('DOKU_ACTIONS', DOKU_PLUGIN . 'wikiiocmodel/projects/defaultProject/actions/');

require_once DOKU_PLUGIN . "wikiiocmodel/DokuActionManager.php";

/**
 * Description: Construye un array que contiene las definiciones y parámetros de las acciones no comunes
 * @author culpable Rafa
 */
abstract class DokuExtraAction extends DokuActionManager{

    const KEY_IS_INTERNAL_ACTION = 0;
    const KEY_NEED_USER_INTERVENTION = 1;
    const KEY_LOW_USUAL_INTERVENTION = 0;
    const KEY_USUAL_INTERVENTION = 1;
    const KEY_HIGH_IMPORTANT_INTERVENTION = 2;
    const KEY_LOW_DURATION = 0;
    const KEY_LONG_DURATION = 1;

    abstract static function getActionParams();

    /**
     * Construye un array que contiene las definiciones y parámetros de las acciones no comunes
     * @return array
     */
    public static function getActions() {
        $actionFiles = self::getListOfExtraActions();
        foreach ($actionFiles as $fileClass) {
            $actionArray = $fileClass::getActionParams();
            if ( $actionArray['type']['intervention'] == self::KEY_NEED_USER_INTERVENTION ) {
                $action[] = array('level' => $actionArray['type']['level']
                                  ,'id' => $actionArray['id']
                                  ,'label' => $actionArray['label']
                                  ,'duration' => $actionArray['duration']
                                  ,'cmdParams' => $actionArray['cmdParams']
                                 );
            }
        }
        return $action;
    }
    
    /**
     * Obté la llista d'arxius del directori "actions/extra"
     * @return array Llista dels arxius corresponents a les "accions"
     */
    private static function getListOfExtraActions() {
        if (($rdir = opendir(DOKU_ACTIONS . 'extra/'))) {
            while (false !== ($file = readdir($rdir))){
                if (filetype(DOKU_ACTIONS . 'extra/' . $file) == 'file') {
                    $ficheros[] = $file;
                }
            }
            closedir($rdir);
        }
        sort($ficheros, SORT_LOCALE_STRING); 
        return $ficheros;
    }
    
}
