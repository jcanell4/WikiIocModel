<?php
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
include_once (DOKU_PLUGIN . 'wikiiocmodel/projects/documentation/actions/ProjectMetadataAction.php');

class GetProjectMetaDataAction extends ProjectMetadataAction {

    public function get($paramsArr = array()) {
        $id = $paramsArr[ProjectKeys::KEY_ID];
        $projectType = $paramsArr[ProjectKeys::KEY_PROJECT_TYPE];

        $this->projectModel->init($id, $projectType);
        
        //sólo se ejecuta si existe el proyecto
        if ($this->projectModel->existProject($id)) {
            $ret = $this->projectModel->getData();
            $ret['info'] = $this->generateInfo("info", WikiIocLangManager::getLang('project_loaded'), $id);
            $ret[ProjectKeys::KEY_ID] = $this->idToRequestId($id);
        }
        if (!$ret)
            throw new ProjectNotExistException($id);
        else
            return $ret;
    }
}