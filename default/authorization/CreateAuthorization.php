<?php
/**
 * CreateAuthorization: Extensión clase Autorización para los comandos 
 * que precisan una autorización mínima de AUTH_CREATE
 * 
 * @author Rafael Claver
 */
if (!defined('DOKU_INC')) die();
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_INC . 'lib/plugins/wikiiocmodel/');
require_once (DOKU_INC . 'inc/auth.php');
require_once (WIKI_IOC_MODEL . 'default/DokuModelExceptions.php');
require_once (WIKI_IOC_MODEL . 'default/authorization/CommandAuthorization.php');

class CreateAuthorization extends CommandAuthorization {
    /*
    public function canRun($permission = NULL) {
        $ret = parent::canRun($permission);
        $ret = $ret && $this->permission->getInfoPerm() >= AUTH_CREATE;
        return $ret;
    }
    */

    //ALERTA [Josep] Ara ja no cal això, només necessitem si hi ha error i el nom de l'excepció
    //const NOT_AUTH_CREATE = 256 * AUTH_CREATE;

    public function canRun($permission = NULL) {
//          parent::canRun($permission)
//        if ($this->permission->getInfoPerm() < AUTH_CREATE) {
//            $this->permission->error = 1005; //per què serveix ??
//            $this->errorAuth['error'] += self::NOT_AUTH_CREATE;
//            $this->errorAuth['exception'] = 'InsufficientPermissionToCreatePageException';
//            $this->errorAuth['extra_param'] = $this->permission->getIdPage();
//        }
        if ( parent::canRun($permission) && $this->permission->getInfoPerm() < AUTH_CREATE) {
            //$this->permission->error = 1005; //per què serveix ??
            $this->errorAuth['error'] = TRUE;
            $this->errorAuth['exception'] = 'InsufficientPermissionToCreatePageException';
            $this->errorAuth['extra_param'] = $this->permission->getIdPage();
        }
        return !$this->errorAuth['error'];
    }
}
