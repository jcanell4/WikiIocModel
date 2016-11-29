<?php
/**
 * DokuModelManager:
 * - proporciona autorizaciones y ModelWrapper
 * - define las rutas de las clases y las clases por defecto necesarias para este proyecto
 * @author Rafael Claver
 */
if (!defined('DOKU_INC')) die();
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_INC . "lib/plugins/wikiiocmodel/");
define('WIKI_IOC_PROJECT', WIKI_IOC_MODEL . "projects/documentation/");

require_once(WIKI_IOC_MODEL . 'persistence/BasicPersistenceEngine.php');
require_once(WIKI_IOC_MODEL . 'WikiIocModelManager.php');
require_once(WIKI_IOC_MODEL . 'metadata/MetaDataService.php');
//Las siguientes includes son para Clases específicas y exclusivas de este proyecto
require_once(WIKI_IOC_MODEL . 'BasicModelAdapter.php');
require_once(WIKI_IOC_PROJECT . 'DocumentationModelExceptions.php');

class DokuModelManager extends WikiIocModelManager{
    
    const DEF = WIKI_IOC_MODEL;
    const PRJ = WIKI_IOC_PROJECT;
    static $defDirClass = array (
                //"Authorization" => array(self::DEF."authorization")
                //,"Action" => se usa cuando los ficheros de esta clase están en un directorio ajeno a este proyecto
                //,"Model" =>  se anula porque los ficheros de esta clase NO están en un directorio ajeno a este proyecto
           );
    static $defMainClass = array(
                "DokuModelAdapter" => self::DEF."BasicModelAdapter.php",
                "FactoryAuthorization" => self::PRJ."authorization/FactoryAuthorization.php"
           );

    public function __construct() {}

    public function getAuthorizationManager($str_command, $params) {
        $factory = \FactoryAuthorization::Instance();
        return $factory->createAuthorizationManager($str_command, $params);
    }

    public function getModelWrapperManager() {
        return (new \BasicModelAdapter())->init(new \BasicPersistenceEngine());
    }

    public static function getDefaultDirClass($name) {
        return self::$defDirClass[$name];
    }

    public static function getDefaultMainClass() {
        return self::$defMainClass;
    }
}
