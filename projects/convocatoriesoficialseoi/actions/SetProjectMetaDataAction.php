<?php
if (!defined('DOKU_INC')) die();

class SetProjectMetaDataAction extends BasicSetProjectMetaDataAction
{
    protected function responseProcess()
    {
        $response = parent::responseProcess();
        $model = $this->getModel();

        if ($model->isProjectGenerated()) {
            $id = $this->getModel()->getContentDocumentId($response);
            p_set_metadata($id, array('metadataProjectChanged' => time()));
        }else {
            $params = $model->buildParamsToPersons($response['projectMetaData'], $response['old_persons']);
            $model->modifyACLPageAndShortcutToPerson($params);
        }

        $response[ProjectKeys::KEY_GENERATED] = $model->generateProject();

        return $response;
    }

}