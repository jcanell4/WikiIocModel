<?php
/**
 * CreateAuthorization: Extensión clase Autorización para los comandos
 * que precisan una autorización mínima de AUTH_CREATE
 * @author Rafael Claver
 */
if (!defined('DOKU_INC')) die();

class CreateAuthorization extends PageCommandAuthorization {

    public function getPermissionException() {
        if ($this->permission->getInfoPerm() < AUTH_CREATE) {
            $exception = 'InsufficientPermissionToCreatePageException';
        }
        return $exception;
    }
}
