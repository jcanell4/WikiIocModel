<?php
/**
 * CreateAuthorization: Extensión clase Autorización para los comandos 
 * que precisan una autorización mínima de AUTH_CREATE
 * 
 * @author Rafael Claver
 */
if (!defined('DOKU_INC')) die();
define('WIKI_IOC_PROJECT', DOKU_INC . "lib/plugins/wikiiocmodel/projects/defaultProject/");

require_once (DOKU_INC . 'inc/auth.php');
require_once (WIKI_IOC_PROJECT . 'authorization/PageCommandAuthorization.php');

class CreateAuthorization extends PageCommandAuthorization {

    public function getPermissionException() {
        if ($this->permission->getInfoPerm() < AUTH_CREATE) {
            $exception = 'InsufficientPermissionToCreatePageException';
        }
        return $exception;
    }
}
