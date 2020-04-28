<?php
/**
 * AdminAuthorization: Extensión clase Autorización para los comandos
 * que precisan una autorización mínima de AUTH_ADMIN
 * @author Rafael Claver
 */
if (!defined('DOKU_INC')) die();

class AdminAuthorization extends BasicCommandAuthorization {

    public function canRun() {
        if ( parent::canRun() && $this->permission->getInfoPerm() < AUTH_ADMIN) {
            $this->errorAuth[self::ERROR_KEY] = TRUE;
            $this->errorAuth[self::EXCEPTION_KEY] = 'AuthorizationNotCommandAllowed';
            $this->errorAuth[self::EXTRA_PARAM_KEY] = $this->permission->getIdPage();
        }
        return !$this->errorAuth[self::ERROR_KEY];
    }

}
