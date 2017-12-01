<?php
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
if (!defined('WIKI_IOC_PROJECTS')) define('WIKI_IOC_PROJECTS', DOKU_PLUGIN . 'wikiiocmodel/projects/');

require_once WIKI_IOC_PROJECTS . "defaultProject/DokuExtraAction.php";

/**
 * Description: Recoge el conjunto de arrays de parámetros de las acciones extra
 * @author culpable Rafa
 */
abstract class DokuActionManager extends AbstractActionManager{

    const EXTRA_ACTIONS = WIKI_IOC_PROJECTS . 'defaultProject/actions/extra/';

    /**
     * Construye un array que contiene las definiciones y parámetros de las acciones no comunes
     * @return array
     */
    public static function getActions() {
        $actionFiles = self::getListOfExtraActions();
        foreach ($actionFiles as $fileClass) {
            $actionArray = $fileClass::getActionParams();
            if ( $actionArray['type']['intervention'] == DokuExtraAction::KEY_NEED_USER_INTERVENTION ) {
                $actions[$fileClass] = array('level' => $actionArray['type']['level']
                                            ,'id' => $actionArray['id']
                                            ,'label' => $actionArray['label']
                                            ,'duration' => $actionArray['duration']
                                            ,'cmdParams' => $actionArray['cmdParams']
                                            );
            }
        }
        return $actions;
    }

    public static function getURLUpdateViewHandler() {
        return DOKU_URL . self::EXTRA_ACTIONS . 'UpdateViewHandler.js';
    }

    /**
     * Construye un JSON que contiene el array de las configuraciones de los controles
     * @return JSON
     */
    public static function getJSONActions() {
        $arr_actions = self::getActions();
        foreach ($arr_actions as $key => $arrAction) {
            if ($arrAction['level'] == DokuExtraAction::KEY_USUAL_INTERVENTION)
                $arrJson['button'][$key] = $arrAction;
            elseif ($arrAction['level'] == DokuExtraAction::KEY_HIGH_IMPORTANT_INTERVENTION)
                $arrJson['menu'][$key] = $arrAction;
            else
                $arrJson['other'][$key] = $arrAction; //El tipo es poco importante
        }
        $arrJson['url'] = self::getURLUpdateViewHandler();
        return json_encode($arrJson);
    }

    /**
     * Retorna un array que contiene el array de las acciones con sus configuraciones y la ruta del UpdateViewHandler
     * @return JSON
     */
    public static function getArrayActions() {
        $ret = array('actions'=> self::getActions(), 'url'=> self::getURLUpdateViewHandler());
        return $ret;
    }

    /**
     * Carrega en memòria els arxius del directori "actions/extra" i
     * omple un array amb la llista de classes
     * @return array Llista de classes dels arxius corresponents a les "accions/extra"
     */
    private static function getListOfExtraActions() {
        $path = self::EXTRA_ACTIONS;
        if (($rdir = opendir($path))) {
            while (false !== ($file = readdir($rdir))){
                if (strrchr($file, '.')=='.php' && filetype($path . $file) == 'file') {
                    require_once $path . $file;
                    // El nom de la classe ha de coincidir, necessàriament, amb el nom del fitxer
                    $fileClasses[] = substr($file, 0, strrpos($file, '.'));
                }
            }
            closedir($rdir);
        }
        sort($fileClasses, SORT_LOCALE_STRING);
        return $fileClasses;
    }

 }
