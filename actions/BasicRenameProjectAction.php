<?php
/**
 * BasicRenameProjectAction
 * @culpable Rafael
 */
if (!defined("DOKU_INC")) die();

class BasicRenameProjectAction extends BasicViewProjectMetaDataAction {

    protected function runAction() {
        $this->lockStruct = $this->requireResource(TRUE);
        if ($this->lockStruct["state"]!== ResourceLockerInterface::LOCKED){
            throw new FileIsLockedException($this->params[PageKeys::KEY_ID]);
        }

        if ($this->resourceLocker->isLockedChild($this->params[PageKeys::KEY_ID])) {
            $this->resourceLocker->leaveResource(TRUE);
            throw new FileIsLockedException($this->params[PageKeys::KEY_ID]);
        }

        $model = $this->getModel();
        $data = $model->getData();
        $persons = $data['projectMetaData']['autor']['value'].",".$data['projectMetaData']['responsable']['value'];
        $model->renameProject($this->params[ProjectKeys::KEY_ID], $this->params['newname'], $persons);

        $response[PageKeys::KEY_CODETYPE] = 0;
        return $response;
    }

    public function responseProcess() {
        $this->initAction();
        $response = $this->runAction();
        $this->postAction($response);
        return $response;
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
