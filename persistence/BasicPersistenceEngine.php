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
     * @return \PageDataRequest
     */
    public function createPageDataRequest(){
        require_once(DOKU_PLUGIN . 'wikiiocmodel/persistence/PageDataRequest.php');
        return new PageDataRequest();
        
    }

    /**
     * 
     * @return \MediaDataRequest
     */
    public function createMediaDataRequest(){
        require_once(DOKU_PLUGIN . 'wikiiocmodel/persistence/MediaDataRequest.php');
        return new MediaDataRequest();
    }

    /**
     * 
     * @return \MediaMetaDataRequest
     */
    public function createMediaMetaDataRequest(){
        require_once(DOKU_PLUGIN . 'wikiiocmodel/persistence/MediaMetaDataRequest.php');
        return new MediaMetaDataRequest();
    }

    /**
     * 
     * @return \MetaDataRequest
     */
    public function createMetaDataRequest(){
        require_once(DOKU_PLUGIN . 'wikiiocmodel/persistence/MetaDataRequest.php');
        return new MetaDataRequest();
    }

    /**
     * 
     * @return \DraftDataRequest
     */
    public function createDraftDataRequest(){
        require_once(DOKU_PLUGIN . 'wikiiocmodel/persistence/DraftDataRequest.php');
        return new DraftDataRequest();
    }
}
