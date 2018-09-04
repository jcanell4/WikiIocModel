<?php
/**
 * CreateFolderAction: crea una nueva carpeta para el proyecto en la ruta especificada
 * @culpable Rafael
 */
if (!defined('DOKU_INC')) die();
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_INC."lib/plugins/wikiiocmodel/");
require_once WIKI_IOC_MODEL."actions/ProjectMetadataAction.php";

class CreateFolderAction extends ProjectMetadataAction {

    protected function responseProcess() {
        $id = $this->params[ProjectKeys::KEY_ID];
        $projectId = $this->params['projectId'];
        $projectModel = $this->getModel();

        $projectModel->init($projectId, $this->params[ProjectKeys::KEY_PROJECT_TYPE]);

        //sólo se ejecuta si existe el proyecto
        if ($projectModel->existProject()) {

            if ($projectModel->folderExists($id)) {
                throw new PageAlreadyExistsException($id, 'pageExists');
            }
            //No se permite la creación de una carpeta dentro de un proyecto hijo
            $hasProject = $projectModel->getThisProject($id);
            if ($hasProject['nsproject'] !== $projectId) {
                throw new UnknownProjectException($id, "No es permet la creació d'una carpeta dins d'un subprojecte.");
            }

            if ($this->projectModel->createFolder($id)) {
                $response['info'] = $this->generateInfo("info", WikiIocLangManager::getLang('folder_created')." ($id)", $projectId);
            }else {
                $response['info'] = $this->generateInfo("error", WikiIocLangManager::getLang('folder_created_error')." ($id)", $projectId);
                $response['alert'] = WikiIocLangManager::getLang('folder_created_error')." ($id)";
            }
        }
        return $response;
    }

}
