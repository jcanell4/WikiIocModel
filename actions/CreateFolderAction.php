<?php
/**
 * CreateFolderAction: crea una nueva carpeta para el proyecto en la ruta especificada
 * @culpable Rafael
 */
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
require_once (DOKU_PLUGIN."wikiiocmodel/actions/ProjectMetadataAction.php");

class CreateFolderAction extends ProjectMetadataAction {

    protected function responseProcess() {
        $id = $this->params[ProjectKeys::KEY_ID];
        $new_folder = $this->params['new_folder'];
        $projectModel = $this->getModel();

        $projectModel->init($id, $this->params[ProjectKeys::KEY_PROJECT_TYPE]);

        //sólo se ejecuta si existe el proyecto
        if ($projectModel->existProject($id)) {

            if ($projectModel->folderExists($new_folder)) {
                throw new PageAlreadyExistsException($new_folder, 'pageExists');
            }
            //No se permite la creación de una carpeta dentro de un proyecto hijo
            $hasProject = $projectModel->getThisProject($new_folder);
            if ($hasProject['nsproject'] !== $id) {
                throw new UnknownProjectException($new_folder, "No es permet la creació d'una carpeta dins d'un subprojecte.");
            }

            if ($this->projectModel->createFolder($new_folder)) {
                $response['info'] = $this->generateInfo("info", WikiIocLangManager::getLang('folder_created')." ($new_folder)", $id);
            }else {
                $response['info'] = $this->generateInfo("error", WikiIocLangManager::getLang('folder_created_error')." ($new_folder)", $id);
                $response['alert'] = WikiIocLangManager::getLang('folder_created_error')." ($new_folder)";
            }
        }
        return $response;
    }

}
