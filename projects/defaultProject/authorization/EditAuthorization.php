<?php
/**
 * ReadAuthorization: Extensión clase Autorización para los comandos 
 * que precisan una autorización mínima de AUTH_READ
 * 
 * @author Rafael Claver
 */
if (!defined('DOKU_INC')) die();
if (!defined('DOKU_IOC_MODEL')) define('DOKU_IOC_MODEL', DOKU_INC . "lib/plugins/wikiiocmodel/projects/default/");

require_once (DOKU_INC . 'inc/auth.php');
require_once (DOKU_IOC_MODEL . 'authorization/PageCommandAuthorization.php');

class EditAuthorization extends PageCommandAuthorization {
    
//    public function canRun($permission = NULL) {
//        if ( parent::canRun($permission) && $this->permission->getInfoPerm() < AUTH_READ) {
//            $this->errorAuth['error'] = TRUE;
//            $this->errorAuth['exception'] = 'InsufficientPermissionToEditPageException';
//            $this->errorAuth['extra_param'] = $this->permission->getIdPage();
//        }
//        return !$this->errorAuth['error'];
//    }

    public function getPermissionException($permission) {
        if ($permission->getPageExist() && $permission->getInfoPerm() < AUTH_READ) {
            $exception = 'InsufficientPermissionToEditPageException';
        }
        return $exception;
    }
    
    public function getPermission($command) {
        parent::getPermission($command);
        $this->permission->setReadOnly($this->permission->getInfoPerm() == AUTH_READ);
        return $this->permission;
    }

}
