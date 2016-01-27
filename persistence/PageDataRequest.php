<?php

if (! defined('DOKU_INC')) die();
if (!defined('DOKU_PLUGIN')) {
    define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
}


require_once (DOKU_INC . 'inc/pageutils.php');
require_once (DOKU_PLUGIN . 'wikiiocmodel/persistence/DataRequest.php');


/**
 * Description of PageDataRequest
 *
 * @author josep
 */
class PageDataRequest extends DataRequest {
    public function getFileName($id, $rev = "", $specparams=NULL) {
        if($specparams){
             $clean=$specparams;
        }else{
             $clean=true;
        }
        return wikiFN($raw_id, $rev, $clean);
    }

    /**
     * És la crida principal de la comanda ns_tree_rest
     * @global type $conf
     * @param type $currentnode
     * @param type $sortBy
     * @param type $onlyDirs
     * @return type
     */
    public function getNsTree( $currentnode, $sortBy, $onlyDirs = FALSE ) {
            global $conf;
            $base = $conf['datadir'];

            return $this->getNsTreeFromBase( $base, $currentnode, $sortBy, $onlyDirs );
    }
        
    
    public function getMetaFiles($id){
        return metaFiles($id);
    }

    public function save($id, $text, $summary, $minor = false){
        saveWikiText($id, $text, $summary, $minor);
    }
}
