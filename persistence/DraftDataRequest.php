<?php
if (! defined('DOKU_INC')) die();
if (!defined('DOKU_PLUGIN')) {
    define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
}

require_once (DOKU_PLUGIN . "wikiiocmodel/WikiIocModelExceptions.php");
require_once (DOKU_PLUGIN . 'wikiiocmodel/persistence/DataRequest.php');


/**
 * Description of DraftDataRequest
 *
 * @author josep
 */
class DraftDataRequest extends DataRequest{
    public function getFileName($id) {
        $id = WikiPageSystemManager::cleanIDForFiles($id);
        return getCacheName(WikiIocInfoManager::getInfo("client") . $id, '.draft');        
    }

    public function getNsTree($currentNode, $sortBy, $onlyDirs = FALSE) {
        throw new UnavailableMethodExecutionException("DraftDataRequest#getNsTree");
    }    
}
