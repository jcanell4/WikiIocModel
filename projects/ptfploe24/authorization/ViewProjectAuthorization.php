<?php
/**
 * ViewProjectAuthorization: define la clase de autorizaciones de los comandos del proyecto "ptfploe24"
 * @author Rafael Claver
 */
if (!defined('DOKU_INC')) die();

class ViewProjectAuthorization extends EditProjectAuthorization {

    public function __construct() {
        parent::__construct();
        $this->allowedGroups[] = "platreballfp";
    }

}
