<?php

if (! defined('DOKU_INC')) die();
if (!defined('DOKU_PLUGIN')) {
    define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
}


require_once (DOKU_INC . 'inc/common.php');
require_once (DOKU_INC . 'inc/changelog.php');
require_once(DOKU_INC . 'inc/template.php');
require_once (DOKU_INC . 'inc/pageutils.php');
require_once (DOKU_INC . 'inc/parserutils.php');
require_once (DOKU_INC . 'inc/io.php');
require_once (DOKU_PLUGIN . 'wikiiocmodel/persistence/DataQuery.php');
require_once (DOKU_PLUGIN . 'wikiiocmodel/persistence/WikiPageSystemManager.php');
require_once DOKU_PLUGIN."ownInit/WikiGlobalConfig.php";
require_once DOKU_PLUGIN."wikiiocmodel/WikiIocLangManager.php";


/**
 * Description of PageDataQuery
 *
 * @author josep
 */
class PageDataQuery extends DataQuery {
    
    public function getFileName($id, $specparams=NULL) {
        $clean=true;
        $rev = "";
        if(is_array($specparams)){
            if($specparams["clean"]){
                $clean=$specparams["clean"];
            }
            if($specparams["rev"]){
                $rev=$specparams["rev"];
            }            
        }else{
            $rev = $specparams;
        }
        return wikiFN($id, $rev, $clean);
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
            $base = WikiGlobalConfig::getConf('datadir');

            return $this->getNsTreeFromBase( $base, $currentnode, $sortBy, $onlyDirs );
    }
        
    
    public function getMetaFiles($id){
        return metaFiles($id);
    }

    public function save($id, $text, $summary, $minor = false){
        saveWikiText($id, $text, $summary, $minor);
    }
    
    public function getHtml($id, $rev = null){
        $html = $this->p_wiki_xhtml($id, $rev, true);

        return $html;
        
    }
    
    public function getRaw($id, $rev=NULL){
        return rawWiki($id, $rev);
    }
    
    public function getRawSlices($id, $range="", $rev=""){
        return rawWikiSlices($range, $id, $rev);
    }
    
    public function getToc($id){
        global $ACT;
        $act_aux = $ACT;
        $ACT = "show";
        $toc = tpl_toc(TRUE);
        $ACT = $act_aux;
        return $toc;
    }


   private function p_wiki_xhtml($id, $rev='', $excuse=true){
       $file = $this->getFileName($id,$rev);
       $ret  = '';

       //ensure $id is in global $ID (needed for parsing)
       global $ID;
       $keep = $ID;
       $ID   = $id;

       if($rev){
           if(@file_exists($file)){
               $ret = p_render('xhtml',p_get_instructions(io_readWikiPage($file,$id,$rev)),$info); //no caching on old revisions
           }elseif($excuse){
               $ret = WikiIocLangManager::getXhtml('norev');
           }
       }else{
           if(@file_exists($file)){
               $ret = p_cached_output($file,'xhtml',$id);
           }elseif($excuse){
               $ret = WikiIocLangManager::getXhtml('newpage');
           }
       }

       //restore ID (just in case)
       $ID = $keep;

       return $ret;
    }
   
    public function getInstructions($id, $rev=NULL){
        $file = $this->getFileName($id);
        if(!$rev){
            $instructions = p_cached_instructions($file, FALSE, $id);
        }else{
            $instructions = p_get_instructions(io_readWikiPage($file,$id,$rev));
        }
        return $instructions;
    }
    
     public function getRevisionList($id){
        $revisions = getRevisions($id, -1, 50);

        $ret = [];

        foreach ($revisions as $revision) {
            $ret[$revision] = getRevisionInfo($id, $revision);
            $ret[$revision]['date'] =  WikiPageSystemManager::extractDateFromRevision($ret[$revision]['date']);
        }
        $ret['current'] = @filemtime(wikiFN($id));
        $ret['docId'] = $id;
        return $ret;
    }
}
