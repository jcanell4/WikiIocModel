<?php
/**
 * ReadAuthorization: Extensión clase Autorización para los comandos 
 * que precisan una autorización mínima de AUTH_READ
 * 
 * @author Rafael Claver
 */
if (!defined('DOKU_INC')) die();
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_INC . 'lib/plugins/wikiiocmodel/');
require_once (DOKU_INC . 'inc/auth.php');
require_once (WIKI_IOC_MODEL . 'projects/default/DokuModelExceptions.php');
require_once (WIKI_IOC_MODEL . 'projects/default/authorization/PageCommandAuthorization.php');

class ReadAuthorization extends PageCommandAuthorization {
    /*
    public function canRun($permission = NULL) {
        $ret = parent::canRun($permission);
        $ret = $ret && $this->permission->getInfoPerm() >= AUTH_READ;
        return $ret;
    }
     */

    //ALERTA [Josep] Ara ja no cal això, només necessitem si hi ha error i el nom de l'excepció    
//    const NOT_AUTH_READ = 256 * AUTH_READ;

    public function canRun($permission = NULL) {
//        parent::canRun($permission);
//        if ( $this->permission->getInfoPerm() < AUTH_READ) {
//            $this->errorAuth['error'] += self::NOT_AUTH_READ;
//            $this->errorAuth['exception'] = 'InsufficientPermissionToViewPageException';
//            $this->errorAuth['extra_param'] = $this->permission->getIdPage();
//        }
        
        if ( parent::canRun($permission) && 
             $this->permission->getPageExist() &&
             $this->permission->getInfoPerm() < AUTH_READ ) 
        {
            $this->errorAuth['error'] = TRUE;
            $this->errorAuth['exception'] = 'InsufficientPermissionToViewPageException';
            $this->errorAuth['extra_param'] = $this->permission->getIdPage();
        }
        return !$this->errorAuth['error'];
    }
}
