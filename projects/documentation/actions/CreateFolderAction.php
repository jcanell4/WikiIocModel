<?php
/**
 * Description of CreatePageAction
 * @culpable Rafael
 */
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
require_once (DOKU_PLUGIN."wikiiocmodel/projects/documentation/actions/ProjectMetadataAction.php");

class CreateFolderAction extends ProjectMetadataAction {

    public function init($modelManager) {
        parent::init($modelManager);
        $this->defaultDo = ProjectKeys::KEY_CREATE;
    }

    protected function responseProcess() {
        $id = $this->params[ProjectKeys::KEY_ID];
        $projectType = $this->params[ProjectKeys::KEY_PROJECT_TYPE];

        $this->projectModel->init($id, $projectType);

        //sólo se ejecuta si no existe el proyecto
        if (!$this->projectModel->existProject($id)) {

            $id = str_replace(":", "_", $this->params[ProjectKeys::KEY_ID]);
            $response['info'] = $this->generateInfo("info", WikiIocLangManager::getLang('folder_created'), $id);
        }
        return $response;
    }

    protected function runProcess() {
        if (WikiIocInfoManager::getInfo("exists")) {
            throw new PageAlreadyExistsException($this->params[PageKeys::KEY_ID], 'pageExists');
        }
        //parent::runProcess();
    }

//    protected function startProcess() {
//    }

}
