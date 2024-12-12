<?php
/**
 * ViewProjectAuthorization: define la clase de autorizaciones de los comandos del proyecto "guieseoianual"
 * @author Rafael Claver
 */
defined('DOKU_INC') || die();

class ViewProjectAuthorization extends EditProjectAuthorization {

    public function __construct() {
        parent::__construct();
        $this->allowedGroups[] = "eoi";
    }

}
