<?php
/**
 * ProfileAuthorization: Extensión clase Autorización para el comando 'profile'
 * comprueba que el nombre de usuario sea auténtico
 * @author Rafael Claver
 */
if (!defined('DOKU_INC')) die();
define('WIKI_IOC_PROJECTS', DOKU_INC . 'lib/plugins/wikiiocmodel/projects/');
require_once (DOKU_INC . 'inc/auth.php');
require_once (WIKI_IOC_PROJECTS . 'defaultProject/authorization/CommandAuthorization.php');

class ProfileAuthorization extends CommandAuthorization {

    public function canRun() {
        if ( parent::canRun() && !$this->permission->getIsValidUser()) {
            $this->errorAuth[self::ERROR_KEY] = TRUE;
            $this->errorAuth[self::EXCEPTION_KEY] = 'InvalidUserException';
        }
        return !$this->errorAuth[self::ERROR_KEY];
    }
}
