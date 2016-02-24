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
require_once (WIKI_IOC_MODEL . 'projects/default/DokuModelExceptions.php');
require_once (WIKI_IOC_MODEL . 'projects/default/authorization/PageCommandAuthorization.php');

class WriteAuthorization extends PageCommandAuthorization {

    public function canRun($permission = NULL) {
        if ( parent::canRun($permission) && $this->permission->getInfoPerm() < AUTH_EDIT) {
            $this->errorAuth['error'] = TRUE;
            $this->errorAuth['exception'] = 'InsufficientPermissionToWritePageException';
            $this->errorAuth['extra_param'] = $this->permission->getIdPage();
        }
        return !$this->errorAuth['error'];
    }
}
