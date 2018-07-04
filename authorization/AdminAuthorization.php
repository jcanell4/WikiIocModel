<?php
/**
 * AdminAuthorization: Extensión clase Autorización para los comandos
 * que precisan una autorización mínima de AUTH_ADMIN
 *
 * @author Rafael Claver
 */
if (!defined('DOKU_INC')) die();
require_once (DOKU_INC . 'inc/auth.php');
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_INC . "lib/plugins/wikiiocmodel/");
require_once (WIKI_IOC_MODEL . "authorization/ProjectCommandAuthorization.php");

class AdminAuthorization extends ProjectCommandAuthorization {

    public function canRun() {
        if ( parent::canRun() && $this->permission->getInfoPerm() < AUTH_ADMIN) {
            $this->errorAuth[self::ERROR_KEY] = TRUE;
            $this->errorAuth[self::EXCEPTION_KEY] = 'AuthorizationNotCommandAllowed';
            $this->errorAuth[self::EXTRA_PARAM_KEY] = $this->permission->getIdPage();
        }
        return !$this->errorAuth[self::ERROR_KEY];
    }

}
