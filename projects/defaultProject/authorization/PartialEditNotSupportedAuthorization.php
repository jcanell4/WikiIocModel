<?php
/**
 * CommandAuthorization: define la clase de autorizaciones de los comandos del proyecto "pblactivity"
 * @author Rafael Claver
 */
if (!defined('DOKU_INC')) die();

class PartialEditNotSupportedAuthorization extends BasicCommandAuthorization {
    public function canRun() {
        $this->errorAuth[self::ERROR_KEY] = TRUE;
        $this->errorAuth[self::EXCEPTION_KEY] =  'PartialEditNotSupportedException'; // TODO: canviar el tipus d'excepció
        $this->errorAuth[self::EXTRA_PARAM_KEY] = NULL;

        return !$this->errorAuth[self::ERROR_KEY];
    }
}