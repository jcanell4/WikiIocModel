<?php
/**
 * CreateFolderAction: crea una nueva carpeta para el proyecto en la ruta especificada
 * @culpable Rafael
 */
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
require_once (DOKU_PLUGIN."wikiiocmodel/projects/documentation/actions/ProjectMetadataAction.php");

class CreateFolderAction extends ProjectMetadataAction {

    protected function responseProcess() {
        $id = $this->params[ProjectKeys::KEY_ID];
        $new_folder = $this->params['new_folder'];
        $this->projectModel->init($id, $this->params[ProjectKeys::KEY_PROJECT_TYPE]);

        //sólo se ejecuta si existe el proyecto
        if ($this->projectModel->existProject($id)) {

            if ($this->projectModel->folderExists($new_folder)) {
                throw new PageAlreadyExistsException($new_folder, 'pageExists');
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
