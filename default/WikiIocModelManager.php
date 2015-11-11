<?php
/**
 * WikiIocModelManager: proporciona autorizaciones y ModelWrapper
 *
 * @author Rafael Claver
 */
if (!defined('DOKU_INC')) die();
if (!defined('DOKU_IOCMODEL')) define('DOKU_IOCMODEL', DOKU_INC . "lib/plugins/wikiiocmodel/default/");

require_once(DOKU_IOCMODEL . 'DokuModelAdapter.php');
require_once(DOKU_IOCMODEL . 'WikiIocModelExceptions.php');
require_once(DOKU_IOCMODEL . 'authorization/FactoryAuthorizationManager.php');

class WikiIocModelManager {
    
    public function __construct() {
        static $inst = NULL;
        if($inst === NULL) {
            $inst = 'a';
            $inst = new WikiIocModelManager();
        }
    }

    public function getAuthorizationManager($str_command, $params) {
        $factory = FactoryAuthorizationManager::Instance();
        return $factory->CreateAuthorizationManager($str_command, $params);
    }

    public function getModelWrapperManager() {
        return new DokuModelAdapter();
    }

}
