<?php
/**
 * GenerateProjectAuthorization: Extensión clase Autorización para los comandos 
 * que precisan una autorización mínima de AUTH_CREATE y que el usuario sea del grupo "admin" o "projectmanager"
 * 
 * @author Rafael Claver
 */
if (!defined('DOKU_INC')) die();
define('WIKI_IOC_PROJECTS', DOKU_INC . "lib/plugins/wikiiocmodel/projects/");

require_once (DOKU_INC . 'inc/auth.php');
require_once (WIKI_IOC_PROJECTS . 'taulasubs/authorization/CommandAuthorization.php');

class GenerateProjectAuthorization extends CommandAuthorization {

    public function canRun() {
        if (parent::canRun()) {
            if ($this->permission->getInfoPerm() < AUTH_CREATE) {
                $this->errorAuth['error'] = TRUE;
                $this->errorAuth['exception'] = 'InsufficientPermissionToGenerateProjectException';
                $this->errorAuth['extra_param'] = $this->permission->getIdPage();
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
