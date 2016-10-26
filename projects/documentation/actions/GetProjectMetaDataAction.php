<?php


class GetProjectMetaDataAction extends ProjectMetadataAction {

    public function get($paramsArr = array()) {
        //sólo se ejecuta si existe el proyecto
        if ($this->projectModel->existProject($paramsArr[ProjectKeys::KEY_ID])) {
            $this->projectModel->init($paramsArr[ProjectKeys::KEY_ID], $paramsArr[ProjectKeys::KEY_PROJECT_TYPE]);
            $ret = $this->projectModel->getData();
            $ret['info'] = $this->generateInfo("info", WikiIocLangManager::getLang('project_loaded'), $paramsArr[ProjectKeys::KEY_ID]);
        }
        if (!$ret)
            throw new ProjectNotExistException($paramsArr[ProjectKeys::KEY_ID]);
        else
            return $ret;
    }
}