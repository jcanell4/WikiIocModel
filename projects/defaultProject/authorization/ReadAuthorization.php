<?php
/**
 * ReadAuthorization: Extensión clase Autorización para los comandos
 * que precisan una autorización mínima de AUTH_READ
 * (ver las 'sustitucones' en FactoryAuthorizationCfg.php)
 * @author Rafael Claver
 */
if (!defined('DOKU_INC')) die();

class ReadAuthorization extends PageCommandAuthorization {

    public function getPermissionException() {
        if ($this->permission->getResourceExist() && $this->permission->getInfoPerm() < AUTH_READ) {
            $exception = 'InsufficientPermissionToViewPageException';
        }
        return $exception;
    }
}
