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
        $this->allowedGroups = ["fctmanager"];
    }

//    public function canRun() {
//        if (parent::canRun()) {
//            if(!$this->isUserGroup(["fctmanager","admin"]) && !$this->isResponsable()) {
//                $this->errorAuth['error'] = TRUE;
//                $this->errorAuth['exception'] = 'InsufficientPermissionToEditProjectException';
//                $this->errorAuth['extra_param'] = $this->permission->getIdPage();
//            }
//        }
//        return !$this->errorAuth['error'];
//    }
}
