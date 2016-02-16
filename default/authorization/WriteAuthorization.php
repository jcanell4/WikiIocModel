<?php
/**
 * WriteAuthorization: Extensión clase Autorización para los comandos 
 * que precisan una autorización mínima de AUTH_EDIT
 * 
 * @author Rafael Claver
 */
if (!defined('DOKU_INC')) die();
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_INC . 'lib/plugins/wikiiocmodel/');
require_once (DOKU_INC . 'inc/auth.php');
require_once (WIKI_IOC_MODEL . 'default/DokuModelExceptions.php');
require_once (WIKI_IOC_MODEL . 'default/authorization/CommandAuthorization.php');

class WriteAuthorization extends CommandAuthorization {

        //ALERTA [Josep] Ara ja no cal això, només necessitem si hi ha error i el nom de l'excepció    
//    const NOT_AUTH_WRITE = 256 * AUTH_EDIT;

    public function canRun($permission = NULL) {
//        parent::canRun($permission);
//        if ( $this->permission->getInfoPerm() < AUTH_EDIT) {
//            $this->permission->error = 1009;
//            $this->errorAuth['error'] += self::NOT_AUTH_WRITE;
//            $this->errorAuth['exception'] = 'InsufficientPermissionToWritePageException';
//            $this->errorAuth['extra_param'] = $this->permission->getIdPage();
//        }
        if ( parent::canRun($permission) && $this->permission->getInfoPerm() < AUTH_EDIT) {
            $this->errorAuth['error'] = TRUE;
            $this->errorAuth['exception'] = 'InsufficientPermissionToWritePageException';
            $this->errorAuth['extra_param'] = $this->permission->getIdPage();
        }
        return !$this->errorAuth['error'];
    }
}
