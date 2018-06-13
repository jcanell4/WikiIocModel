<?php
if (!defined("DOKU_INC")) die();
include_once 'ProjectMetadataAction.php';

class CreateProjectMetaDataAction extends ProjectMetadataAction {

    /**
     * Crea una estructura de directorios para el nuevo proyecto (tipo de proyecto)
     * a partir del archivo de configuración configMain.json
     * @param array $paramsArr : parámetros recibidos por el ajaxCall
     */
    public function responseProcess() {
        $paramsArr = $this->params;
        $id = $paramsArr[ProjectKeys::KEY_ID];
        $projectType = $paramsArr[ProjectKeys::KEY_PROJECT_TYPE];

        $this->projectModel->init($id, $projectType);

        //sólo se ejecuta si no existe el proyecto
        if (!$this->projectModel->existProject($id)) {
            $ret = $this->projectModel->createProject($id, $projectType, $paramsArr[ProjectKeys::KEY_FILTER]);
        }
        if (!$ret)
            throw new ProjectExistException($id);
        else
            return $ret;
    }   
}