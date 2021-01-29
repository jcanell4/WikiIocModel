<?php
/**
 * ResponsableProjectAuthorization: Extensión clase Autorización para los proyectos
 *                                 que tienen atributo de responsable
  * @author Rafael Claver
 */
if (!defined('DOKU_INC')) die();

class ResponsableProjectAuthorization extends ProjectCommandAuthorization {

     public function __construct() {
        parent::__construct();
        $this->allowedRoles[] = ProjectPermission::ROL_RESPONSABLE;
        $this->allowedGroups[] = "fctmanager";
    }
    
}
