<?php

if (! defined('DOKU_INC')) die();

require_once (DOKU_INC . 'inc/pageutils.php');
require_once (DOKU_PLUGIN . 'wikiiocmodel/persistence/DataQuery.php');


/**
 * Description of MetaDataQuery
 *
 * @author josep
 */
class MetaDataQuery extends DataQuery {
    public function getFileName($id, $sppar) {
        if($sppar && isset($sppar["ext"])){
            $ext = $sppar["ext"];
        }else{
            $ext ="";
        }

        return metaFN($id, $ext);
    }
    
    public function getNsTree($currentNode, $sortBy, $onlyDirs = FALSE) {
            global $conf;
            $base = $conf['metadir'];

            return $this->getNsTreeFromBase( $base, $currentnode, $sortBy, $onlyDirs );        
    }

}
