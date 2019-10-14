<?php
/**
 * BasicRemoveProjectAction
 * @culpable Rafael
 */
if (!defined("DOKU_INC")) die();

class BasicRemoveProjectAction extends BasicViewProjectMetaDataAction {

    protected function runAction() {
        $model = $this->getModel();
        $response = $model->getData();

        $persons = $response['projectMetaData']['autor']['value'].",".$response['projectMetaData']['responsable']['value'];
        $model->removeProject($this->params[ProjectKeys::KEY_ID], $persons);

        $response = [ProjectKeys::KEY_ID => $this->idToRequestId($this->params[ProjectKeys::KEY_ID]),
                     ProjectKeys::KEY_OLD_ID => $this->params[ProjectKeys::KEY_ID],
                     ProjectKeys::KEY_CODETYPE => 0
                    ];
        return $response;
    }

    public function responseProcess() {
        $response = parent::responseProcess();
        return $response;
    }

    protected function initAction() {
        parent::initAction();

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
        $new_message = $this->generateMessageInfoForSubSetProject($response[ProjectKeys::KEY_ID], $this->params[ProjectKeys::KEY_METADATA_SUBSET], 'project_removed');
        $response['info'] = $this->addInfoToInfo($response['info'], $new_message);
    }

    public function requireResource($lock = FALSE) {
        $this->resourceLocker->init($this->params, TRUE);
        return $this->resourceLocker->requireResource($lock);
    }
}
