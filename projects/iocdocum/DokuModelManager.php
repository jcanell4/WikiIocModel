<?php
/**
 * DokuModelManager:
 * - proporciona acceso a las Autorizaciones, ModelAdapter y Renderer del proyecto
 * - define las rutas de las clases y las clases por defecto necesarias para este proyecto
 * @author Rafael Claver
 */
if (!defined('DOKU_INC')) die();
if (!defined('DOKU_LIB_IOC')) define('DOKU_LIB_IOC', DOKU_INC.'lib/lib_ioc/');
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_INC . "lib/plugins/wikiiocmodel/");
if (!defined('WIKI_IOC_PROJECTS')) define('WIKI_IOC_PROJECTS', WIKI_IOC_MODEL . 'projects/');

require_once(DOKU_LIB_IOC . "wikiiocmodel/ProjectModelExceptions.php");
require_once(WIKI_IOC_MODEL . "metadata/MetaDataService.php");
require_once(WIKI_IOC_MODEL . "BasicModelAdapter.php");

class DokuModelManager extends AbstractModelManager{

    const MOD = WIKI_IOC_MODEL;
    const DEF = WIKI_IOC_PROJECTS . 'defaultProject/';
    const PRJ = WIKI_IOC_PROJECTS . 'iocdocum/';

    static $defDirClass = array (
               'Authorization' => array(self::DEF."authorization/"), //se usa cuando los ficheros de esta clase están en un directorio ajeno a este proyecto
               'Action'        => array(self::DEF."actions/", self::DEF."actions/extra/"),
               'Model'         => array(self::MOD."datamodel"),
               'Renderer'      => array(self::PRJ."renderer")
           );
    static $defMainClass = array(
               'DokuModelAdapter'     => self::MOD."BasicModelAdapter.php",
               'FactoryAuthorization' => self::PRJ."authorization/FactoryAuthorization.php",
               'FactoryExporter'      => self::PRJ."export/FactoryExporter.php"
           );

    public function getAuthorizationManager($str_command) {
        $factory = \FactoryAuthorization::Instance(self::$defDirClass['Authorization']);
        return $factory->createAuthorizationManager($str_command);
    }

    public function getExporterManager() {
        return new \FactoryExporter();
    }

    public function getModelAdapterManager() {
        //return (new \BasicModelAdapter())->init($this->getPersistenceEngine());
	$dm = new \BasicModelAdapter();
	$dm->init($this->getPersistenceEngine());
	return $dm;
    }

    public static function getDefaultDirClass($name) {
        return self::$defDirClass[$name];
    }

    public static function getDefaultMainClass() {
        return self::$defMainClass;
    }

    public function getProjectDir() {
        return self::PRJ;
    }
}
