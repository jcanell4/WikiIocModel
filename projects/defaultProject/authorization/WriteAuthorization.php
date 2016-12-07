<?php
/**
 * WriteAuthorization: Extensión clase Autorización para los comandos 
 * que precisan una autorización mínima de AUTH_EDIT
 * 
 * @author Rafael Claver
 */
if (!defined('DOKU_INC')) die();
define('WIKI_IOC_PROJECTS', DOKU_INC . "lib/plugins/wikiiocmodel/projects/");

require_once (DOKU_INC . 'inc/auth.php');
require_once (WIKI_IOC_PROJECTS . 'defaultProject/authorization/PageCommandAuthorization.php');

class WriteAuthorization extends PageCommandAuthorization {

    public function getPermissionException() {
        if ($this->permission->getPageExist() && $this->permission->getInfoPerm() < AUTH_EDIT) {
            $exception = 'InsufficientPermissionToWritePageException';
        }
        return $exception;
    }
}
