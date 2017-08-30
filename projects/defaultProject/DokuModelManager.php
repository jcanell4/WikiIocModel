<?php
/**
 * DokuModelManager: proporciona autorizaciones y ModelWrapper
 * - proporciona autorizaciones y ModelWrapper
 * - define las rutas de las clases y las clases por defecto necesarias para este proyecto
 * @author Rafael Claver
 */
if (!defined('DOKU_INC')) die();
define('WIKI_IOC_MODEL', DOKU_INC . "lib/plugins/wikiiocmodel/");
define('WIKI_IOC_PROJECT', WIKI_IOC_MODEL . "projects/defaultProject/");

require_once(WIKI_IOC_MODEL . 'WikiIocModelManager.php');
require_once(WIKI_IOC_MODEL . 'persistence/BasicPersistenceEngine.php');
//Los siguientes includes son para Clases específicas y exclusivas de este proyecto
require_once(WIKI_IOC_PROJECT . 'DokuModelAdapter.php');
require_once(WIKI_IOC_PROJECT . 'DokuModelExceptions.php');

class DokuModelManager extends WikiIocModelManager{

    const MOD = WIKI_IOC_MODEL;
    const DEF = WIKI_IOC_PROJECT;
    static $defDirClass = array (
                //'Action' =>         Está inactivo porque los ficheros de estas clases no están en directorios ajenos a este proyecto.
                //'Authorization' =>  Si algún fichero de clase está fuera del directorio de proyecto, éste es el lugar adecuado para indicarlo
                'Model' => array(self::MOD."datamodel")
           );
    static $defMainClass = array(
                'DokuModelAdapter'     => self::DEF."DokuModelAdapter.php",
                'FactoryAuthorization' => self::DEF."authorization/FactoryAuthorization.php"
           );

    public function __construct() {}

    public function getAuthorizationManager($str_command) {
        $factory = \FactoryAuthorization::Instance(self::$defDirClass['Authorization']);
        return $factory->createAuthorizationManager($str_command);
    }

    public function getModelWrapperManager() {
        return (new \DokuModelAdapter())->init(new \BasicPersistenceEngine());
    }

    public static function getDefaultDirClass($name) {
        return self::$defDirClass[$name];
    }

    public static function getDefaultMainClass() {
        return self::$defMainClass;
    }
}
