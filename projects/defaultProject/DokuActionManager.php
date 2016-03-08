<?php
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
if (!defined('DOKU_ACTIONS')) define('DOKU_ACTIONS', DOKU_PLUGIN . 'wikiiocmodel/projects/defaultProject/actions/');

require_once DOKU_PLUGIN."wikiiocmodel/AbstractWikiActionManager.php";
require_once DOKU_PLUGIN."wikiiocmodel/WikiIocInfoManager.php";
require_once DOKU_PLUGIN."wikiiocmodel/WikiIocLangManager.php";
require_once DOKU_PLUGIN."wikiiocmodel/WikiIocModelExceptions.php";

//namespace ioc_dokuwiki;

/**
 * Description: define el array de parámetros de la acción
 *
 * @author culpable Rafa
 */
abstract class DokuActionManager extends AbstractActionManager{

    protected $params = array();
    
    public function getActionParams() {
        
        $this->params = [
             'action' => ''
            ,'type' => ''
        ];
    }
    
    private function getListOfCommonActions() {
        $ficheros
    }
}
