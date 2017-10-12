<?php
/**
 * DokuModelManager:
 * - proporciona acceso a las Autorizaciones, ModelWrapper y Renderer del proyecto
 * - define las rutas de las clases y las clases por defecto necesarias para este proyecto
 * @author Rafael Claver
 */
if (!defined('DOKU_INC')) die();
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_INC . "lib/plugins/wikiiocmodel/");
if (!defined('WIKI_IOC_PROJECTS')) define('WIKI_IOC_PROJECTS', WIKI_IOC_MODEL . 'projects/');

require_once(WIKI_IOC_MODEL . 'WikiIocModelManager.php');
require_once(WIKI_IOC_MODEL . 'persistence/BasicPersistenceEngine.php');
require_once(WIKI_IOC_MODEL . 'metadata/MetaDataService.php');
//Las siguientes includes son para Clases específicas y exclusivas de este proyecto
require_once(WIKI_IOC_MODEL . 'BasicModelAdapter.php');
require_once(WIKI_IOC_PROJECTS . 'proves/DocumentationModelExceptions.php');

class DokuModelManager extends WikiIocModelManager{

    const MOD = WIKI_IOC_MODEL;
    const DEF = WIKI_IOC_PROJECTS . 'defaultProject/';
    const PRJ = WIKI_IOC_PROJECTS . 'proves/';

    static $defDirClass = array (
               'Authorization' => array(self::DEF."authorization/"), //se usa cuando los ficheros de esta clase están en un directorio ajeno a este proyecto
               'Action'        => array(self::DEF."actions/", self::DEF."actions/extra/"),
               'Model'         => array(self::MOD."datamodel"),
               'Renderer'      => array(self::PRJ."renderer")
           );
    static $defMainClass = array(
               'DokuModelAdapter'     => self::MOD."BasicModelAdapter.php",
               'FactoryAuthorization' => self::PRJ."authorization/FactoryAuthorization.php",
               'FactoryRenderer'      => self::PRJ."renderer/FactoryRenderer.php"
           );

    public function __construct() {}

    public function getAuthorizationManager($str_command) {
        $factory = \FactoryAuthorization::Instance(self::$defDirClass['Authorization']);
        return $factory->createAuthorizationManager($str_command);
    }

    public function getRendererManager() {
        return \FactoryRenderer::Instance(self::$defDirClass['Renderer']);
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
