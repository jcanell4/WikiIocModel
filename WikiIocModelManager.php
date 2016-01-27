<?php
/**
 * WikiIocModelManager: proporciona autorizaciones y ModelWrapper
 *
 * @author Rafael Claver
 */
if (!defined('DOKU_INC')) die();
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_INC . "lib/plugins/wikiiocmodel/");

class WikiIocModelManager {

    public static function Instance(){
        //logica er decidir quin espai de noms cal activar
        //per defecte activarem defaultNamespace
        $inst = WikiIocModelManager::createDefaultModelManager();
        return $inst;
    }
    
    private static function createDefaultModelManager(){
        require_once(WIKI_IOC_MODEL . 'default/WikiIocModelManager.php');  
        return new \ioc_dokuwiki\WikiIocModelManager();
    }
}
