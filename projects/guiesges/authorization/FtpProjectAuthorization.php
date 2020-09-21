<?php

/**
 * Description of FtpProjectAuthorization
 *
 * @author josep
 */
if (!defined('DOKU_INC')) die();

class FtpProjectAuthorization extends ProjectCommandAuthorization {

    public function __construct() {
        parent::__construct();
        $this->allowedGroups[] = "manager";
        $this->allowedGroups[] = "editorges";
        $this->allowedRoles[] = Permission::ROL_AUTOR;
    }
}
