<?php
/**
 * BasicRenameProjectAction
 * @culpable Rafael
 */
if (!defined("DOKU_INC")) die();

class BasicRenameProjectAction extends BasicViewProjectMetaDataAction implements ResourceLockerInterface {

    protected function runAction() {
        $this->lockStruct = $this->requireResource(TRUE);
        if ($this->lockStruct["state"]!== ResourceLockerInterface::LOCKED){
            throw new FileIsLockedException($this->params[PageKeys::KEY_ID]);
        }

        $model = $this->getModel();
        $model->renameProject($this->params[ProjectKeys::KEY_ID], $this->params['newname']);

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
        $new_message = $this->generateMessageInfoForSubSetProject($response[ProjectKeys::KEY_ID], $this->params[ProjectKeys::KEY_METADATA_SUBSET], 'project_renamed');
        $response['info'] = $this->addInfoToInfo($response['info'], $new_message);
    }

    public function requireResource($lock = FALSE) {
        $this->resourceLocker->init($this->params);
        return $this->resourceLocker->requireResource($lock);
    }
}
