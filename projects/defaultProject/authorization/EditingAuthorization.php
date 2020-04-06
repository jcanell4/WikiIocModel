<?php
/**
 * EditingAuthorization: Extensión clase Autorización para los comandos
 * que precisan una autorización mínima de AUTH_EDIT
 * @author Rafael Claver
 */
if (!defined('DOKU_INC')) die();

class EditingAuthorization extends PageCommandAuthorization {

    public function getPermissionException() {
        if ($this->permission->getResourceExist() && $this->permission->getInfoPerm() < AUTH_EDIT) {
            $exception = 'InsufficientPermissionToWritePageException';
        }
        return $exception;
    }

}
