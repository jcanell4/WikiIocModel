<?php
/**
 * EditProjectAuthorization: Extensión clase Autorización para los comandos
 * que precisan una autorización mínima de AUTH_EDIT y que el usuario sea el Responsable o del grupo "admin" o "projectmanager"
 * @author Rafael Claver
 */
if (!defined('DOKU_INC')) die();

class ViewProjectAuthorization extends ProjectCommandAuthorization {

    public function canRun() {
        if (parent::canRun()) {
            if(!$this->isUserGroup(["editorges","admin"])
                    && ($this->permission->getInfoPerm() < AUTH_READ || !$this->isUserGroup(["ges"]))
                    && ($this->permission->getInfoPerm() < AUTH_EDIT || !$this->isUserGroup(["projectmanager"]))) {
                $this->errorAuth['error'] = TRUE;
                $this->errorAuth['exception'] = 'InsufficientPermissionToEditProjectException';
                $this->errorAuth['extra_param'] = $this->permission->getIdPage();
            }
        }
        return !$this->errorAuth['error'];
    }
}
