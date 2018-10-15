<?php
/**
 * ProfileAuthorization: Extensión clase Autorización para el comando 'profile'
 * comprueba que el nombre de usuario sea auténtico
 * @author Rafael Claver
 */
if (!defined('DOKU_INC')) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . "lib/plugins/");
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_PLUGIN . "wikiiocmodel/");
require_once (DOKU_INC . "inc/auth.php");
require_once (WIKI_IOC_MODEL . "projects/defaultProject/authorization/CommandAuthorization.php");

class ProfileAuthorization extends CommandAuthorization {

    public function canRun() {
        if ( parent::canRun() && !$this->permission->getIsValidUser()) {
            $this->errorAuth[self::ERROR_KEY] = TRUE;
            $this->errorAuth[self::EXCEPTION_KEY] = 'InvalidUserException';
        }
        return !$this->errorAuth[self::ERROR_KEY];
    }
}
