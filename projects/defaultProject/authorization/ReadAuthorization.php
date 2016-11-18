<?php
/**
 * ReadAuthorization: Extensión clase Autorización para los comandos 
 * que precisan una autorización mínima de AUTH_READ
 * 
 * @author Rafael Claver
 */
if (!defined('DOKU_INC')) die();
define('WIKI_IOC_PROJECT', DOKU_INC . "lib/plugins/wikiiocmodel/projects/defaultProject/");

require_once (DOKU_INC . 'inc/auth.php');
require_once (WIKI_IOC_PROJECT . 'authorization/PageCommandAuthorization.php');

class ReadAuthorization extends PageCommandAuthorization {

//    public function canRun($permission = NULL) {
//        
//        if ( parent::canRun($permission) && 
//             $this->permission->getPageExist() &&
//             $this->permission->getInfoPerm() < AUTH_READ ) 
//        {
//            $this->errorAuth['error'] = TRUE;
//            $this->errorAuth['exception'] = 'InsufficientPermissionToViewPageException';
//            $this->errorAuth['extra_param'] = $this->permission->getIdPage();
//        }
//        return !$this->errorAuth['error'];
//    }
    
    public function getPermissionException() { // el parámetro $permission contiene lo mismo que $this->permission
        if ($this->permission->getPageExist() && $this->permission->getInfoPerm() < AUTH_READ) {
            $exception = 'InsufficientPermissionToViewPageException';
        }
        return $exception;
    }
}
