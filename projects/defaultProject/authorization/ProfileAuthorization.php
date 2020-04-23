<?php
/**
 * ProfileAuthorization: Extensión clase Autorización para el comando 'profile'
 * comprueba que el nombre de usuario sea auténtico
 * @author Rafael Claver
 */
if (!defined('DOKU_INC')) die();

class ProfileAuthorization extends BasicCommandAuthorization {

    public function canRun() {
        if ( parent::canRun() && !$this->permission->getIsValidUser()) {
            $this->errorAuth[self::ERROR_KEY] = TRUE;
            $this->errorAuth[self::EXCEPTION_KEY] = 'InvalidUserException';
        }
        return !$this->errorAuth[self::ERROR_KEY];
    }
}
