<?php

if (! defined('DOKU_INC')) die();
if (!defined('DOKU_PLUGIN')) {
    define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
}


require_once (DOKU_INC . 'inc/common.php');
require_once (DOKU_INC . 'inc/pageutils.php');
require_once (DOKU_INC . 'inc/parserutils.php');
require_once (DOKU_INC . 'inc/io.php');
require_once (DOKU_PLUGIN . 'wikiiocmodel/persistence/DataQuery.php');
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
    
    public function getChunks($id){
        $instructions = $this->getInstructionsForDocument($id);              
        $chunks = $this->_getChunks($instructions);
        
        return $chunks;
    }
    
    public function getRaw($id, $rev=NULL){
        return rawWiki($id, $rev);
    }
    
    public function getRawSlices($id, $range="", $rev=""){
        return rawWikiSlices($range, $id, $rev);
    }
    
    //[ALERTA Josep] CAL revisar per fer servir el PageDataQuery!
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
   
    private function getInstructionsForDocument($id){
        $file = $this->getFileName($id);
        $instructions = p_cached_instructions($file, FALSE, $id);
        return $instructions;
    }
    
        // TODO[Xavi] PER SUBISTIUIR PEL PLUGIN DEL RENDER
    // Només son editables parcialment les seccions de nivell 1, 2 i 3
    private function _getChunks($instructions){
        $sections = [];
        $currentSection = [];
        $lastClosePosition = 0;
        $lastHeaderRead = '';
        $firstSection = true;


        for ($i = 0; $i < count($instructions); $i++) {
            $currentSection['type'] = 'section';

            if ($instructions[$i][0] === 'header') {
                $lastHeaderRead = $instructions[$i][1][0];
            }

            if ($instructions[$i][0] === 'section_open' && $instructions[$i][1][0] < 4) {
                // Tanquem la secció anterior
                if ($firstSection) {
                    // Ho descartem, el primer element no conté informació
                    $firstSection = false;
                } else {
                    $currentSection['end'] = $instructions[$i][2];
                    $sections[] = $currentSection;
                }

                // Obrim la nova secció
                $currentSection = [];
                $currentSection['title'] = $lastHeaderRead;
                $currentSection['start'] = $instructions[$i][2];
                $currentSection['params']['level'] = $instructions[$i][1][0];
            }

            // Si trobem un tancament de secció actualitzem la ultima posició de tancament
            if ($instructions[$i][0] === 'section_close') {
                $lastClosePosition = $instructions[$i][2];
            }

        }
        // La última secció es tanca amb la posició final del document
        $currentSection['end'] = $lastClosePosition;
        $sections[] = $currentSection;

        return $sections;
    }

}
