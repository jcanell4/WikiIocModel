<?php
/**
 * CreateAuthorization: Extensión clase Autorización para los comandos
 * que precisan una autorización mínima de AUTH_CREATE
 * @author Rafael Claver
 */
if (!defined('DOKU_INC')) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . "lib/plugins/");
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_PLUGIN . "wikiiocmodel/");
require_once (DOKU_INC . "inc/auth.php");
require_once (WIKI_IOC_MODEL . "projects/defaultProject/authorization/PageCommandAuthorization.php");

class CreateAuthorization extends PageCommandAuthorization {

    public function getPermissionException() {
        if ($this->permission->getInfoPerm() < AUTH_CREATE) {
            $exception = 'InsufficientPermissionToCreatePageException';
        }
        return $exception;
    }
}
