<?php
/**
 * DokuModelManager:
 * - proporciona autorizaciones y ModelWrapper
 * - define las rutas de las clases y las clases por defecto necesarias para este proyecto
 * @author Rafael Claver
 */
if (!defined('DOKU_INC')) die();
define('WIKI_IOC_MODEL', DOKU_INC . "lib/plugins/wikiiocmodel/");
define('WIKI_IOC_PROJECTS', WIKI_IOC_MODEL . 'projects/');

require_once(WIKI_IOC_MODEL . 'WikiIocModelManager.php');
require_once(WIKI_IOC_MODEL . 'persistence/BasicPersistenceEngine.php');
require_once(WIKI_IOC_MODEL . 'metadata/MetaDataService.php');
//Las siguientes includes son para Clases específicas y exclusivas de este proyecto
require_once(WIKI_IOC_PROJECTS . 'testmat/TestmatModelAdapter.php');
require_once(WIKI_IOC_PROJECTS . 'testmat/TestmatModelExceptions.php');

class DokuModelManager extends WikiIocModelManager{

    const PRJ = WIKI_IOC_PROJECTS . 'testmat/';
    const DEF = WIKI_IOC_PROJECTS . 'documentation/';
    
    static $defDirClass = array (
                "Authorization" => array (
                                        self::DEF."authorization"
                                   )
                //"Action" => los ficheros de estas clases no están en directorios ajenos a este proyecto
                //"Model" =>  los ficheros de estas clases no están en directorios ajenos a este proyecto
           );
    static $defMainClass = array(
               "TestmatModelAdapter" => self::PRJ."TestmatModelAdapter.php",
               "FactoryAuthorization" => self::DEF."authorization/FactoryAuthorization.php"
           );
    
    public function __construct() {}

    public function getAuthorizationManager($str_command) {
        $factory = \FactoryAuthorization::Instance();
        return $factory->createAuthorizationManager($str_command);
    }

    public function getModelWrapperManager() {
        return (new \TestmatModelAdapter())->init(new \BasicPersistenceEngine());
    }

    public static function getDefaultDirClass($name) {
        return self::$defDirClass[$name];
    }

    public static function getDefaultMainClass() {
        return DokuModelManager::$defMainClass;
    }
}
