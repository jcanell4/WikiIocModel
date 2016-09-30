<?php

if (! defined('DOKU_INC')) die();

require_once (DOKU_INC . 'inc/pageutils.php');
require_once (DOKU_PLUGIN . 'wikiiocmodel/persistence/DataQuery.php');


/**
 * Description of MediaMetaDataQuery
 *
 * @author josep
 */
class MediaMetaDataQuery extends DataQuery {
    public function getFileName($id, $sppar) {
        if($sppar && isset($sppar["ext"])){
            $ext = $sppar["ext"];
        }else{
            $ext ="";
        }

        return mediaMetaFN($id, $ext);
    }

    public function getNsTree($currentNode, $sortBy, $onlyDirs=FALSE, $expandProject=FALSE, $hiddenProjects=FALSE) {
        global $conf;
        $base = $conf['mediametadir'];

        return $this->getNsTreeFromBase( $base, $currentNode, $sortBy, $onlyDirs, $expandProject, $hiddenProjects );                
    }

    public function save($id, $data, $summary = "", $minor = false) {
        
    }

}
