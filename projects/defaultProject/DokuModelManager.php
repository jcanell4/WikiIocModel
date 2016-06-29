<?php
//namespace ioc_dokuwiki;

/**
 * WikiIocModelManager: proporciona autorizaciones y ModelWrapper
 *
 * @author Rafael Claver
 */
if (!defined('DOKU_INC')) die();
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_INC . "lib/plugins/wikiiocmodel/");
if (!defined('DOKU_IOC_MODEL')) define('DOKU_IOC_MODEL', DOKU_INC . "lib/plugins/wikiiocmodel/projects/defaultProject/");

require_once(WIKI_IOC_MODEL . 'persistence/BasicPersistenceEngine.php');  
require_once(WIKI_IOC_MODEL . 'WikiIocModelManager.php');
require_once(DOKU_IOC_MODEL . 'DokuModelAdapter.php');
require_once(DOKU_IOC_MODEL . 'DokuModelExceptions.php');
require_once(DOKU_IOC_MODEL . 'authorization/FactoryAuthorization.php');

class DokuModelManager extends WikiIocModelManager{
    
    public function __construct() {}

    public static function Instance(){
        static $inst = null;
        if ($inst === null) {
            $inst = new WikiIocModelManager();
        }
        return $inst;
    }

    public function getAuthorizationManager($str_command, $params) {
        $factory = \FactoryAuthorization::Instance();
        return $factory->createAuthorizationManager($str_command, $params);
    }

    public function getModelWrapperManager() {
        return (new \DokuModelAdapter())->init(new \BasicPersistenceEngine());
    }

}
