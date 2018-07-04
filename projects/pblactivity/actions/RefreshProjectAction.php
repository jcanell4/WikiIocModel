<?php
/**
 * RefreshProjectAction
 * @culpable Rafael
 */
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
include_once (DOKU_PLUGIN . "wikiiocmodel/projects/pblactivity/actions/ViewProjectMetaDataAction.php");

class RefreshProjectAction extends ViewProjectMetaDataAction implements ResourceLockerInterface {

    private $resourceLocker;

    public function init($modelManager) {
        parent::init($modelManager);
        $this->resourceLocker = new ResourceLocker($this->persistenceEngine);
    }

    protected function runAction() {
        $this->lockStruct = $this->requireResource(TRUE);
        if ($this->lockStruct["state"]!== ResourceLockerInterface::LOCKED){
            throw new FileIsLockedException($this->params[PageKeys::KEY_ID]);
        }
        $response[PageKeys::KEY_CODETYPE] = 0;
        return $response;
    }

    public function requireResource($lock = FALSE) {
        $this->resourceLocker->init($this->params);
        return $this->resourceLocker->requireResource($lock);
    }
}
