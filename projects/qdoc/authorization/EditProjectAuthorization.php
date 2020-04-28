<?php
/**
 * EditProjectAuthorization: Extensión clase Autorización para los comandos del proyecto 'qdoc'
 * que precisan una autorización que el usuario sea el Responsable o Autor o del grupo "admin"
 * @author Rafael Claver
 */
if (!defined('DOKU_INC')) die();

class EditProjectAuthorization extends ProjectCommandAuthorization {

    public function __construct() {
        parent::__construct();
        $this->allowedRoles[] = Permission::ROL_AUTOR;
    }

//    public function canRun() {
//        if (parent::canRun()) {
//            if(!$this->isUserGroup(["admin"]) && !$this->isResponsable() && !$this->isAuthor()) {
//                $this->errorAuth['error'] = TRUE;
//                $this->errorAuth['exception'] = 'InsufficientPermissionToEditProjectException';
//                $this->errorAuth['extra_param'] = $this->permission->getIdPage();
//            }
//        }
//        return !$this->errorAuth['error'];
//    }
}
