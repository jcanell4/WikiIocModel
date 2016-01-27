<?php

if (! defined('DOKU_INC')) die();

require_once (DOKU_INC . 'inc/pageutils.php');
require_once (DOKU_PLUGIN . 'wikiiocmodel/persistence/DataRequest.php');


/**
 * Description of MediaMetaDataRequest
 *
 * @author josep
 */
class MediaMetaDataRequest extends DataRequest {
    public function getFileName($id, $ext) {
        return mediaMetaFN($id, $ext);
    }

    public function getNsTree($currentNode, $sortBy, $onlyDirs = FALSE) {
        global $conf;
        $base = $conf['mediametadir'];

        return $this->getNsTreeFromBase( $base, $currentnode, $sortBy, $onlyDirs );                
    }

    public function save($id, $data, $summary = "", $minor = false) {
        
    }

}
