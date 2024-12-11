<?php
/**
 * DokuModelManager:
 * - proporciona acceso a las Autorizaciones, ModelAdapter y Renderer del proyecto 'guieseoianual'
 * - define las rutas de las clases y las clases por defecto necesarias para este proyecto
 * @author Rafael Claver
 * @re-author Marjose
 */
defined('DOKU_INC') || die();
defined('DOKU_LIB_IOC') || define('DOKU_LIB_IOC', DOKU_INC . "lib/lib_ioc/");
defined('DOKU_PLUGIN') || define('DOKU_PLUGIN', DOKU_INC . "lib/plugins/");
defined('WIKI_IOC_MODEL') || define('WIKI_IOC_MODEL', DOKU_PLUGIN . "wikiiocmodel/");
//define('WIKI_IOC_MODEL', DOKU_PLUGIN . "projectsdev/");

require_once(DOKU_LIB_IOC . "wikiiocmodel/ProjectModelExceptions.php");
require_once(WIKI_IOC_MODEL . "BasicModelAdapter.php");

class guieseoianualDokuModelManager extends AbstractModelManager{

    const MOD = WIKI_IOC_MODEL;
    const DEF = WIKI_IOC_MODEL . "projects/defaultProject/";
    const PRJ = __DIR__ . "/";
    const CFG = WIKI_IOC_MODEL . "projects/configuration/";
    const DLI = DOKU_LIB_IOC . "wikiiocmodel/";

    static $defDirClass = array (
               'Authorization' => array(self::DLI."authorization/", self::DEF."authorization/"),
               'Action'        => array(self::DLI."actions/", self::DEF."actions/extra/"), //se usa cuando los ficheros de esta clase están en un directorio ajeno a este proyecto
               'Model'         => array(self::DLI."datamodel/", self::CFG."datamodel/"),
               'Render'        => array(self::DLI."metadata/"),
               'Renderer'      => array(self::PRJ."renderer/"),
               'Upgrader'      => array(self::PRJ."upgrader/")
           );
    static $defMainClass = array(
               'DokuModelAdapter'     => self::MOD."BasicModelAdapter.php",
               'FactoryAuthorization' => self::PRJ."authorization/FactoryAuthorization.php",
               'FactoryExporter'      => self::PRJ."exporter/FactoryExporter.php",
               'Permission'           => self::PRJ."authorization/Permission.php"
           );

    public function getAuthorizationManager($str_command) {
        require_once(self::$defMainClass['Permission']);
        require_once(self::$defMainClass['FactoryAuthorization']);
        $factory = \guieseoianual\FactoryAuthorization::Instance(self::$defDirClass['Authorization'], "\guieseoianual");
        return $factory->createAuthorizationManager($str_command);
    }

    public function getExporterManager() {
        return new \FactoryExporter();
    }

    public function getModelAdapterManager() {
        return (new \BasicModelAdapter())->init($this->getPersistenceEngine());
    }

    public static function getDefaultDirClass($name) {
        return self::$defDirClass[$name];
    }

    public static function getDefaultMainClass() {
        return self::$defMainClass;
    }

    public function getProjectTypeDir() {
        return self::PRJ;
    }
}
