<?php
/**
 * DeleteAuthorization: Extensión clase Autorización para los comandos
 * que precisan una autorización mínima de AUTH_DELETE
 *
 * @author Rafael Claver
 */
if (!defined('DOKU_INC')) die();

class DeleteAuthorization extends PageCommandAuthorization {

    public function getPermissionException() {
        if ($this->permission->getResourceExist() && $this->permission->getInfoPerm() < AUTH_DELETE) {
            $exception = 'InsufficientPermissionToDeletePageException';
        }
        return $exception;
    }

}
