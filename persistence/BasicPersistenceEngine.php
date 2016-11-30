<?php
/**
 * Description of BasicPersistenceEngine
 *
 * @author josep
 */
if (! defined('DOKU_INC')) die();
if (!defined('DOKU_PERSISTENCE')) define('DOKU_PERSISTENCE', DOKU_INC . 'lib/plugins/wikiiocmodel/persistence/');

class BasicPersistenceEngine {
    /**
     * 
     * @return \PageDataQuery
     */
    public function createPageDataQuery(){
        require_once(DOKU_PERSISTENCE . 'PageDataQuery.php');
        return new PageDataQuery();
        
    }

    /**
     * 
     * @return \MediaDataQuery
     */
    public function createMediaDataQuery(){
        require_once(DOKU_PERSISTENCE . 'MediaDataQuery.php');
        return new MediaDataQuery();
    }

    /**
     * 
     * @return \MediaMetaDataQuery
     */
    public function createMediaMetaDataQuery(){
        require_once(DOKU_PERSISTENCE . 'MediaMetaDataQuery.php');
        return new MediaMetaDataQuery();
    }

    /**
     * 
     * @return \MetaDataQuery
     */
    public function createMetaDataQuery(){
        require_once(DOKU_PERSISTENCE . 'MetaDataQuery.php');
        return new MetaDataQuery();
    }

    /**
     * 
     * @return \DraftDataQuery
     */
    public function createDraftDataQuery(){
        require_once(DOKU_PERSISTENCE . 'DraftDataQuery.php');
        return new DraftDataQuery();
    }

    /**
     *
     * @return \NotifyDataQuery
     */
    public function createNotifyDataQuery(){
        require_once(DOKU_PERSISTENCE . 'NotifyDataQuery.php');
        return new NotifyDataQuery();
    }

    /**
     *
     * @return \NotifyDataQuery class
     */
    public function getNotifyDataQueryClass(){
        require_once(DOKU_PERSISTENCE . 'NotifyDataQuery.php');
        return NotifyDataQuery::class;
    }

    public function createLockDataQuery(){
        require_once(DOKU_PERSISTENCE . 'LockDataQuery.php');
        return new LockDataQuery();
    }
    
    public function createProjectMetaDataQuery(){
        require_once(DOKU_PERSISTENCE . 'ProjectMetaDataQuery.php');
        return new ProjectMetaDataQuery();
    }
}
