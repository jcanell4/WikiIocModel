<?php
if (! defined('DOKU_INC')) die();
if (!defined('DOKU_PLUGIN')) {
    define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
}

require_once (DOKU_PLUGIN . "wikiiocmodel/WikiIocModelExceptions.php");
require_once (DOKU_PLUGIN . 'wikiiocmodel/persistence/DataQuery.php');
require_once (DOKU_PLUGIN . 'wikiiocmodel/DraftManager.php');

/**
 * Description of DraftDataQuery
 *
 * @author josep
 */
class DraftDataQuery extends DataQuery{
    public function getFileName($id, $extra=NULL) {
        $id = WikiPageSystemManager::cleanIDForFiles($id);
        return getCacheName(WikiIocInfoManager::getInfo("client") . $id, '.draft');        
    }

    public function getNsTree($currentNode, $sortBy, $onlyDirs = FALSE) {
        throw new UnavailableMethodExecutionException("DraftDataQuery#getNsTree");
    }    
    
    public function generateFull($id){
         return DraftManager::generateFullDraft($id);
    }

    public function removePartialDraft($id) {
        DraftManager::removeStructuredDraftAll($id);
    }

}
