<?php
/**
 * EditProjectAuthorization: Extensión clase Autorización para los comandos
 * que precisan una autorización mínima de AUTH_EDIT y que el usuario sea el Responsable o del grupo "admin" o "projectmanager"
 *
 * @author Rafael Claver
 */
if (!defined('DOKU_INC')) die();
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_INC . "lib/plugins/wikiiocmodel/");
require_once (WIKI_IOC_MODEL . "authorization/ProjectCommandAuthorization.php");

class EditProjectAuthorization extends ProjectCommandAuthorization {

    public function canRun() {
        if (parent::canRun()) {
            if(($this->permission->getInfoPerm() < AUTH_EDIT || !$this->isUserGroup(array("projectmanager","admin")))
                    && !$this->isResponsable()) {
                $this->errorAuth['error'] = TRUE;
                $this->errorAuth['exception'] = 'InsufficientPermissionToEditProjectException';
                $this->errorAuth['extra_param'] = $this->permission->getIdPage();
//                    $this->errorAuth['error'] = TRUE;
//                    $this->errorAuth['exception'] = 'UserNotAuthorizedException';
//                    $this->errorAuth['extra_param'] = $this->permission->getIdPage();
            }
        }
        return !$this->errorAuth['error'];
    }
}