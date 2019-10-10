<?php
/**
 * BasicRenameProjectAction
 * @culpable Rafael
 */
if (!defined("DOKU_INC")) die();

class BasicRenameProjectAction extends BasicViewProjectMetaDataAction {

    protected function runAction() {
        $model = $this->getModel();
        $response = $model->getData();

        $persons = $response['projectMetaData']['autor']['value'].",".$response['projectMetaData']['responsable']['value'];
        $model->renameProject($this->params[ProjectKeys::KEY_ID], $this->params[ProjectKeys::KEY_NEWNAME], $persons);

        $new_ns = preg_replace("/:[^:]*$/", ":{$this->params[ProjectKeys::KEY_NEWNAME]}", $this->params[ProjectKeys::KEY_ID]);
        $model->init($new_ns, $this->params[ProjectKeys::KEY_PROJECT_TYPE]);

        $response = parent::runAction();

        $response[ProjectKeys::KEY_OLD_NS] = $this->params[ProjectKeys::KEY_ID];
        $response[ProjectKeys::KEY_OLD_ID] = $this->idToRequestId($this->params[ProjectKeys::KEY_ID]);
        $response[ProjectKeys::KEY_NS] = $new_ns;
        $response[ProjectKeys::KEY_ID] = $this->idToRequestId($response[ProjectKeys::KEY_NS]);

        return $response;
    }

    public function responseProcess() {
        $response = parent::responseProcess();
        return $response;
    }

    protected function initAction() {
        parent::initAction();
        $this->params[ProjectKeys::KEY_NEWNAME] = preg_replace(array('/\s+/', '/:+/'), "", $this->params[ProjectKeys::KEY_NEWNAME]);

        $this->lockStruct = $this->requireResource(TRUE);
        if ($this->lockStruct["state"]!== ResourceLockerInterface::LOCKED){
            throw new FileIsLockedException($this->params[PageKeys::KEY_ID]);
        }

        if ($this->resourceLocker->isLockedChild($this->params[PageKeys::KEY_ID])) {
            $this->resourceLocker->leaveResource(TRUE);
            throw new FileIsLockedException($this->params[PageKeys::KEY_ID]);
        }
    }

    protected function postAction(&$response) {
        $this->resourceLocker->leaveResource(TRUE);
        $new_message = $this->generateMessageInfoForSubSetProject($response[ProjectKeys::KEY_ID], $this->params[ProjectKeys::KEY_METADATA_SUBSET], 'project_renamed');
        $response['info'] = $this->addInfoToInfo($response['info'], $new_message);
    }

    public function requireResource($lock = FALSE) {
        $this->resourceLocker->init($this->params, TRUE);
        return $this->resourceLocker->requireResource($lock);
    }
}
