<?php
/**
 * AdminAuthorization: Extensión clase Autorización para los comandos 
 * que precisan una autorización mínima de AUTH_ADMIN
 * 
 * @author Rafael Claver
 */
if (!defined('DOKU_INC')) die();
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_INC . 'lib/plugins/wikiiocmodel/');
require_once (DOKU_INC . 'inc/auth.php');
require_once (WIKI_IOC_MODEL . 'projects/default/DokuModelExceptions.php');
require_once (WIKI_IOC_MODEL . 'projects/default/authorization/CommandAuthorization.php');

class AdminAuthorization extends CommandAuthorization {

    public function canRun($permission = NULL) {
        if ( parent::canRun($permission) && $this->permission->getInfoPerm() < AUTH_ADMIN) {
            $this->errorAuth['error'] = TRUE;
            $this->errorAuth['exception'] = 'AuthorizationNotCommandAllowed';
            $this->errorAuth['extra_param'] = $this->permission->getIdPage();
        }
        return !$this->errorAuth['error'];
    }

}
