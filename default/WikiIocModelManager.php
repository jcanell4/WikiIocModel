<?php
namespace ioc_dokuwiki;

/**
 * WikiIocModelManager: proporciona autorizaciones y ModelWrapper
 *
 * @author Rafael Claver
 */
if (!defined('DOKU_INC')) die();
if (!defined('DOKU_IOCMODEL')) define('DOKU_IOCMODEL', DOKU_INC . "lib/plugins/wikiiocmodel/default/");

require_once(DOKU_IOCMODEL . 'DokuModelAdapter.php');
require_once(DOKU_IOCMODEL . 'DokuModelExceptions.php');
require_once(DOKU_IOCMODEL . 'authorization/FactoryAuthorization.php');

class WikiIocModelManager {
    
    public function __construct() {}

    public static function Instance(){
        static $inst = null;
        if ($inst === null) {
            $inst = new WikiIocModelManager();
        }
        return $inst;
    }

    public function getAuthorizationManager($str_command, $aParams) {
        $factory = \FactoryAuthorization::Instance();
        return $factory->createAuthorizationManager($str_command, $aParams);
    }

    public function getModelWrapperManager() {
        return new \DokuModelAdapter();
    }

}
