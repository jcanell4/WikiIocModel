<?php
/**
 * DokuModelManager: proporciona autorizaciones y ModelWrapper
 * - proporciona autorizaciones y ModelWrapper
 * - define las rutas de las clases y las clases por defecto necesarias para este proyecto
 * @author Rafael Claver
 */
if (!defined('DOKU_INC')) die();
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_INC . "lib/plugins/wikiiocmodel/");
if (!defined('DOKU_IOC_PROJECT')) define('DOKU_IOC_PROJECT', WIKI_IOC_MODEL . "projects/defaultProject/");

require_once(WIKI_IOC_MODEL . 'persistence/BasicPersistenceEngine.php');  
require_once(WIKI_IOC_MODEL . 'WikiIocModelManager.php');
require_once(DOKU_IOC_PROJECT . 'DokuModelExceptions.php');
//Los siguientes includes son para Clases específicas y exclusivas de este proyecto
require_once(DOKU_IOC_PROJECT . 'DokuModelAdapter.php');

class DokuModelManager extends WikiIocModelManager{
    
    public function __construct() {}

    public function getAuthorizationManager($str_command, $params) {
        $factory = \FactoryAuthorization::Instance();
        return $factory->createAuthorizationManager($str_command, $params);
    }

    public function getModelWrapperManager() {
        return (new \DokuModelAdapter())->init(new \BasicPersistenceEngine());
    }

    const DEF = DOKU_IOC_PROJECT;
    static $defClassDir = array (
                //"Action" => los ficheros de estas clases no están en directorios ajenos a este proyecto
                //,"Authorization" => si algún fichero está fuera del directorio de proyecto, este es el lugar adecuado para indicarlo
                //,"Model" => 
           );

    static $defMainClass = array(
                   "FactoryAuthorization" => DokuModelManager::DEF."authorization/FactoryAuthorization.php"
                   //"DokuModelAdapter" => DokuModelManager::DEF."DokuModelAdapter.php"
           );
    
    public static function getDefaultClassDir($name) {
        return DokuModelManager::$defClassDir[$name];
    }

    public static function getDefaultMainClass() {
        return DokuModelManager::$defMainClass;
    }
}
