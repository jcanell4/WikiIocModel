<?php
/**
 * DeleteProjectAuthorization: Extensión clase Autorización para los comandos
 * que precisan una autorización mínima de AUTH_DELETE y que el usuario sea del grupo "admin" o "projectmanager"
 *
 * @author Rafael Claver
 */
if (!defined('DOKU_INC')) die();
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_INC . "lib/plugins/wikiiocmodel/");
require_once (WIKI_IOC_MODEL . "authorization/ProjectCommandAuthorization.php");

class DeleteProjectAuthorization extends CommandAuthorization {

    public function canRun() {
        if (parent::canRun()) {
            if ($this->permission->getInfoPerm() < AUTH_DELETE) {
                $this->errorAuth['error'] = TRUE;
                $this->errorAuth['exception'] = 'InsufficientPermissionToDeleteProjectException';
            }else {
                if (!$this->isUserGroup(array("projectmanager","admin"))) {
                    $this->errorAuth['error'] = TRUE;
                    $this->errorAuth['exception'] = 'UserNotAuthorizedException';
                    $this->errorAuth['extra_param'] = $this->permission->getIdPage();
                }
            }
        }
        return !$this->errorAuth['error'];
    }
}
