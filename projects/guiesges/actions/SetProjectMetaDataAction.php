<?php
if (!defined('DOKU_INC')) die();

class SetProjectMetaDataAction extends BasicSetProjectMetaDataAction {

    protected function responseProcess() {
        $response = parent::responseProcess();
        $model = $this->getModel();
        if ($response[ProjectKeys::KEY_GENERATED]) {
            $llista = $model->llistaDePlantilles();
            foreach ($llista as $p) {
                p_set_metadata($p, array('metadataProjectChanged' => time()));
            }
        }else {
            $params = $model->buildParamsToPersons($response['projectMetaData'], $response['old_persons']);
            $model->modifyACLPageAndShortcutToPerson($params);
        }
        return $response;
    }
}
