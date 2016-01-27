<?php

if (! defined('DOKU_INC')) die();

require_once (DOKU_INC . 'inc/pageutils.php');
require_once (DOKU_PLUGIN . 'wikiiocmodel/persistence/DataRequest.php');


/**
 * Description of MetaDataRequest
 *
 * @author josep
 */
class MetaDataRequest extends DataRequest {
    public function getFileName($id, $ext, $specparams=NULL) {
        return metaFN($id, $ext);
    }
    
    public function getNsTree($currentNode, $sortBy, $onlyDirs = FALSE) {
            global $conf;
            $base = $conf['metadir'];

            return $this->getNsTreeFromBase( $base, $currentnode, $sortBy, $onlyDirs );        
    }

}
