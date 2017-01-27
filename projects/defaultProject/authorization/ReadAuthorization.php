<?php
/**
 * ReadAuthorization: Extensión clase Autorización para los comandos 
 * que precisan una autorización mínima de AUTH_READ
 * 
 * @author Rafael Claver
 */
if (!defined('DOKU_INC')) die();

require_once (DOKU_INC . 'inc/auth.php');
require_once (DOKU_INC . 'lib/plugins/wikiiocmodel/projects/defaultProject/authorization/PageCommandAuthorization.php');

class ReadAuthorization extends PageCommandAuthorization {

    public function getPermissionException() {
        if ($this->permission->getResourceExist() && $this->permission->getInfoPerm() < AUTH_READ) {
            $exception = 'InsufficientPermissionToViewPageException';
        }
        return $exception;
    }
}
