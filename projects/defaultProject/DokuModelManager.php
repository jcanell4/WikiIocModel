<?php
/**
 * DokuModelManager:
 * - proporciona acceso a las Autorizaciones y ModelAdapter del proyecto
 * - define las rutas de las clases y las clases por defecto necesarias para este proyecto
 * @author Rafael Claver
 */
if (!defined('DOKU_INC')) die();
if (!defined('DOKU_LIB_IOC')) define('DOKU_LIB_IOC', DOKU_INC.'lib/lib_ioc/');
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_INC . "lib/plugins/wikiiocmodel/");
define('WIKI_IOC_PROJECT', WIKI_IOC_MODEL . "projects/defaultProject/");

require_once(DOKU_LIB_IOC . "wikiiocmodel/DefaultProjectModelExceptions.php");
require_once(WIKI_IOC_PROJECT . 'DokuModelAdapter.php');

class DokuModelManager extends AbstractModelManager{

    const MOD = WIKI_IOC_MODEL;
    const DEF = WIKI_IOC_PROJECT;
    static $defDirClass = array (
                //'Action' =>         Está inactivo porque los ficheros de estas clases no están en directorios ajenos a este proyecto.
                //'Authorization' =>  Si algún fichero de clase está fuera del directorio de proyecto, éste es el lugar adecuado para indicarlo
                //'Model' => array(self::MOD."datamodel/") En este caso el modelo se encuentra directamente en el directorio datamodel de wikiiocmodel, y no es necesario especificar la ruta, pues se coge por defecto.
           );
    static $defMainClass = array(
                'DokuModelAdapter'     => self::DEF."DokuModelAdapter.php",
                'FactoryAuthorization' => self::DEF."authorization/FactoryAuthorization.php"
           );

    public function getAuthorizationManager($str_command) {
        $factory = \FactoryAuthorization::Instance(self::$defDirClass['Authorization']);
        return $factory->createAuthorizationManager($str_command);
    }

    public function getModelAdapterManager() {
        //return (new \DokuModelAdapter())->init($this->getPersistenceEngine());
	$dm = new DokuModelAdapter();
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
        return self::DEF;
    }
}
