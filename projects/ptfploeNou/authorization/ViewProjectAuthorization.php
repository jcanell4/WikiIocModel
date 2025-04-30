<?php
/**
 * ViewProjectAuthorization: define la clase de autorizaciones de los comandos del proyecto "ptfploeNou"
 * @author Rafael Claver
 */
if (!defined('DOKU_INC')) die();

class ViewProjectAuthorization extends EditProjectAuthorization {

    public function __construct() {
        parent::__construct();
        $this->allowedGroups[] = "platreballfp";
    }

}
