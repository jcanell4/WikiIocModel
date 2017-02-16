<?php
/**
 * DeleteAuthorization: Extensión clase Autorización para los comandos 
 * que precisan una autorización mínima de AUTH_DELETE
 * 
 * @author Rafael Claver
 */
if (!defined('DOKU_INC')) die();

require_once (DOKU_INC . 'inc/auth.php');
require_once (DOKU_INC . 'lib/plugins/wikiiocmodel/projects/defaultProject/authorization/PageCommandAuthorization.php');

class DeleteAuthorization extends PageCommandAuthorization {

    public function getPermissionException() {
        if ($this->permission->getResourceExist() && $this->permission->getInfoPerm() < AUTH_DELETE) {
            $exception = 'InsufficientPermissionToDeletePageException';
        }
        return $exception;
    }
    
}
