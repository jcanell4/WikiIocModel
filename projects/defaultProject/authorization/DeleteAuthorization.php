<?php
/**
 * DeleteAuthorization: Extensión clase Autorización para los comandos 
 * que precisan una autorización mínima de AUTH_DELETE
 * 
 * @author Rafael Claver
 */
if (!defined('DOKU_INC')) die();
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_INC . 'lib/plugins/wikiiocmodel/');
require_once (DOKU_INC . 'inc/auth.php');
require_once (WIKI_IOC_MODEL . 'projects/defaultProject/DokuModelExceptions.php');
require_once (WIKI_IOC_MODEL . 'projects/defaultProject/authorization/CommandAuthorization.php');

class DeleteAuthorization extends CommandAuthorization {

//    public function canRun($permission = NULL) {
//        $ret = parent::canRun($permission);
//        $ret = $ret && $this->permission->getInfoPerm() >= AUTH_DELETE;
//        return $ret;
//    }

    //ALERTA [Josep] Ara ja no cal això, només necessitem si hi ha error i el nom de l'excepció    
//    const NOT_AUTH_DELETE = 256 * AUTH_DELETE;

    public function canRun($permission = NULL) {
//        $ret = parent::canRun($permission);
//        if ( $this->permission->getInfoPerm() < AUTH_DELETE) {
//            $ret += NOT_AUTH_DELETE;
//        }
//        return $ret;
        if ( parent::canRun($permission) && $this->permission->getInfoPerm() < AUTH_DELETE) {
            $this->errorAuth['error'] = TRUE;
            $this->errorAuth['exception'] = 'InsufficientPermissionToDeletePageException';
            $this->errorAuth['extra_param'] = $this->permission->getIdPage();
        }
        return !$this->errorAuth['error'];
    }
}
