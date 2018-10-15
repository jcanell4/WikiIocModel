<?php
/**
 * CommandAuthorization: define la clase de autorizaciones de los comandos del proyecto "pblactivity"
 * @author Rafael Claver
 */
if (!defined('DOKU_INC')) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . "lib/plugins/");
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_PLUGIN . "wikiiocmodel/");
require_once(WIKI_IOC_MODEL."authorization/ProjectCommandAuthorization.php");

class NotAllowedCommandAuthorization extends ProjectCommandAuthorization {
    public function canRun() {
        $this->errorAuth[self::ERROR_KEY] = TRUE;
        $this->errorAuth[self::EXCEPTION_KEY] =  'CommandAuthorizationNotFound';
        $this->errorAuth[self::EXTRA_PARAM_KEY] = NULL;

        return !$this->errorAuth[self::ERROR_KEY];
    }
}