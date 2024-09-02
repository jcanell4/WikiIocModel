<?php
if (!defined('DOKU_INC')) die();

/**
 * Class RemoveProjectAction: Conserva una còpia del projecte que es vol eliminar
 *                            Cas particular dels projectes ActivityUtil
 * @culpable Rafael Claver
 */
class RemoveProjectAction extends BasicRemoveProjectAction {

    protected function runAction() {
        $model = $this->getModel();
        $oldID = $this->params[ProjectKeys::KEY_ID];
        $newID = "{$oldID}_bak_000";

        //Obtenir un nom de projecte que encara no existeixi
        $n = 1;
        while ($model->existProject($newID)) {
            $newID = "{$oldID}_bak_" . str_pad($n++, 3, "0", STR_PAD_LEFT);
        }

        $data = $model->getData();
        $persons = $data[ProjectKeys::KEY_PROJECT_METADATA]['autor']['value'].",".$data[ProjectKeys::KEY_PROJECT_METADATA]['responsable']['value'];

        $this->params[ProjectKeys::KEY_ID] = $newID;

        $old = explode(":", $oldID);
        $old_project = array_pop($old);
        $old_path = implode(":", $old);
        $model->duplicateProject($this->params[ProjectKeys::KEY_ID], $old_path, $old_project, $persons);

        $this->params[ProjectKeys::KEY_ID] = $oldID;
        $response = parent::runAction();

        return $response;
    }

}
