<?php
/**
 * CreateDocumentAction: crea un documento en el proyecto, en la ruta indicada, a partir de una plantilla
 * @culpable Rafael
 */
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
require_once (DOKU_PLUGIN.'wikiiocmodel/projects/defaultProject/actions/CreatePageAction.php');

//[JOSEP] Alerta: Caldria pujar aquesta action a nivell de wikiocmodel/actions
class BasicCreateDocumentAction extends CreatePageAction {

    protected function runProcess() {
        $id = $this->params[ProjectKeys::KEY_ID];

        //sólo se ejecuta si existe el proyecto
        if (!$this->dokuPageModel->haveADirProject($id)) {
            throw new ProjectNotExistException($id);
        }

        $thisProject = $this->dokuPageModel->getThisProject($this->params['new_page']);

        if ($thisProject['nsproject'] !== $id) {
            throw new CantCreatePageInProjectException($this->params['espai']);
        }
        parent::runProcess();
    }

}
