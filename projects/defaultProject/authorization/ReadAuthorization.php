<?php
/**
 * ReadAuthorization: Extensión clase Autorización para los comandos
 * que precisan una autorización mínima de AUTH_READ
 * (ver las 'sustitucones' en FactoryAuthorizationCfg.php)
 *
 * @author Rafael Claver
 */
if (!defined('DOKU_INC')) die();
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_INC . 'lib/plugins/wikiiocmodel/');
require_once (DOKU_INC . 'inc/auth.php');
require_once (WIKI_IOC_MODEL . 'projects/defaultProject/authorization/PageCommandAuthorization.php');

class ReadAuthorization extends PageCommandAuthorization {

    public function getPermissionException() {
        if ($this->permission->getResourceExist() && $this->permission->getInfoPerm() < AUTH_READ) {
            $exception = 'InsufficientPermissionToViewPageException';
        }
        return $exception;
    }
}
