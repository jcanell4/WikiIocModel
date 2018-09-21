<?php
/**
 * BasicCreateDocumentAction: crea un documento en el proyecto, en la ruta indicada, a partir de una plantilla
 * @culpable Rafael
 */
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
require_once (DOKU_PLUGIN.'wikiiocmodel/actions/CreatePageAction.php');

class BasicCreateDocumentAction extends CreatePageAction {

    protected function runProcess() {
        $id = $this->params[PageKeys::KEY_ID];
        $projectId = $this->params['projectId'];

        //sólo se ejecuta si existe el proyecto
        if (!$this->dokuPageModel->haveADirProject($projectId)) {
            throw new ProjectNotExistException($projectId);
        }

        $thisProject = $this->dokuPageModel->getThisProject($id);
        if ($thisProject['nsproject'] !== $projectId) {
            throw new CantCreatePageInProjectException($this->params['espai']);
        }
        $arrTmp = split(":", $id);
        $file = array_pop($arrTmp) . ".txt";
        if ($this->dokuPageModel->fileExistsInProject($projectId, $file)) {
            throw new PageAlreadyExistsException($id, "pageExists");
        }

        $this->_runProcess();
    }

    private function _runProcess() {
        global $ACT;
        $ACT = act_permcheck($ACT);
        if ($ACT === "denied"){
            throw new InsufficientPermissionToCreatePageException($this->params[PageKeys::KEY_ID]);
        }
        if ($this->checklock() === LockDataQuery::LOCKED) {
            throw new FileIsLockedException($this->params[PageKeys::KEY_ID]);
        }

        $this->lockStruct = $this->updateLock();
        if ($this->lockState() === self::LOCKED) {
            $this->_save();
            $this->leaveResource(TRUE);
        }
    }

    private function _save(){
        //spam check
        if (checkwordblock()) {
            throw new WordBlockedException();
        }
        $toSave = con($this->params[PageKeys::KEY_PRE],
                      $this->params[PageKeys::KEY_WIKITEXT],
                      $this->params[PageKeys::KEY_SUF], 1);
        if ($this->params["contentFormat"] === self::HTML_FORMAT){
            $toSave = $this->translateToDW($toSave);
        }
        $this->dokuPageModel->setData(array(
                                        PageKeys::KEY_WIKITEXT => $toSave,
                                        PageKeys::KEY_SUM      => $this->params[PageKeys::KEY_SUM],
                                        PageKeys::KEY_MINOR    => $this->params[PageKeys::KEY_MINOR])
                                     );
    }

}