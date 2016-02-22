<?php

if (! defined('DOKU_INC')) die();
if (!defined('DOKU_PLUGIN')) {
    define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
}

/**
 * Description of BasicPersistenceEngine
 *
 * @author josep
 */
class BasicPersistenceEngine {
    /**
     * 
     * @return \PageDataQuery
     */
    public function createPageDataQuery(){
        require_once(DOKU_PLUGIN . 'wikiiocmodel/persistence/PageDataQuery.php');
        return new PageDataQuery();
        
    }

    /**
     * 
     * @return \MediaDataQuery
     */
    public function createMediaDataQuery(){
        require_once(DOKU_PLUGIN . 'wikiiocmodel/persistence/MediaDataQuery.php');
        return new MediaDataQuery();
    }

    /**
     * 
     * @return \MediaMetaDataQuery
     */
    public function createMediaMetaDataQuery(){
        require_once(DOKU_PLUGIN . 'wikiiocmodel/persistence/MediaMetaDataQuery.php');
        return new MediaMetaDataQuery();
    }

    /**
     * 
     * @return \MetaDataQuery
     */
    public function createMetaDataQuery(){
        require_once(DOKU_PLUGIN . 'wikiiocmodel/persistence/MetaDataQuery.php');
        return new MetaDataQuery();
    }

    /**
     * 
     * @return \DraftDataQuery
     */
    public function createDraftDataQuery(){
        require_once(DOKU_PLUGIN . 'wikiiocmodel/persistence/DraftDataQuery.php');
        return new DraftDataQuery();
    }
    
     /**
     * 
     * @return \ProjectMetaDataQuery
     */
    public function createProjectMetaDataQuery(){
        require_once(DOKU_PLUGIN . 'wikiiocmodel/persistence/ProjectMetaDataQuery.php');
        return new ProjectMetaDataQuery();
    }
}
