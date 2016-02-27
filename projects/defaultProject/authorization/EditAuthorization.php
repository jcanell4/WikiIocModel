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
require_once (WIKI_IOC_MODEL . 'projects/defaultProject/DokuModelExceptions.php');
require_once (WIKI_IOC_MODEL . 'projects/defaultProject/authorization/CommandAuthorization.php');

class EditAuthorization extends CommandAuthorization {
    public function canRun($permission = NULL) {
        if ( parent::canRun($permission) && $this->permission->getInfoPerm() < AUTH_READ) {
            $this->errorAuth['error'] = TRUE;
            $this->errorAuth['exception'] = 'InsufficientPermissionToViewPageException';
            $this->errorAuth['extra_param'] = $this->permission->getIdPage();
        }
        return !$this->errorAuth['error'];
    }
    
    public function getPermission($command) {
        parent::getPermission($command);
        $this->permission->setReadOnly($this->permission->getInfoPerm() == AUTH_READ);
        return $this->permission;
    }

}
