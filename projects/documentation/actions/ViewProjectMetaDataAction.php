<?php
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
include_once (DOKU_PLUGIN . 'wikiiocmodel/projects/documentation/actions/GetProjectMetaDataAction.php');

class ViewProjectMetaDataAction extends GetProjectMetaDataAction {

    protected function setParams($params) {
        parent::setParams($params);
    }

    public function responseProcess() {
        $response = parent::responseProcess();
        $response['info'] = $this->generateInfo("info", WikiIocLangManager::getLang('project_view'), $this->params[ProjectKeys::KEY_ID]);
        return $response;
    }

}