<?php
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . "lib/plugins/");
include_once (DOKU_PLUGIN . "wikiiocmodel/actions/ProjectMetadataAction.php");

class CreateSubprojectMetaDataAction extends ProjectMetadataAction {

    /**
     * Crea una estructura de directorios para el nuevo proyecto (tipo de proyecto)
     * a partir del archivo de configuración configMain.json
     */
    public function responseProcess() {
        $parent_id = $this->params['parent_id'];
        $parent_projectType = $this->params['parent_projectType'];
        $new_id = $this->params[ProjectKeys::KEY_ID];
        $new_projectType = $this->params[ProjectKeys::KEY_PROJECT_TYPE];

        $projectModel = $this->getModel();
        $projectModel->init($parent_id, $parent_projectType);

        //Verifica que el proyecto solicitado sea un proyecto permitido
        $listProjectTypes = $projectModel->getListProjectTypes($parent_projectType);
        if (!in_array($new_projectType, $listProjectTypes)) {
            throw new UnknownProjectException($new_id, "El tipus de projecte so·licitat no està permés.");
        }
        //No se permite la creación de un nuevo proyecto dentro de un proyecto hijo
        $hasProject = $projectModel->getThisProject($new_id);
        if ($hasProject['nsproject'] !== $parent_id) {
            throw new UnknownProjectException($new_id, "No es permet la creació d'un projecte dins d'un subprojecte.");
        }

        $action = $this->getModelManager()->getActionInstance("CreateProjectMetaDataAction");
        $ret = $action->get($this->params);

        return $ret;
    }
}