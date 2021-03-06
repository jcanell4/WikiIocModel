<?php
/**
 * EditProjectAuthorization: Extensión clase Autorización para los comandos
 * que precisan una autorización mínima de AUTH_EDIT y
 * que el usuario sea el Responsable o del grupo "admin" o "projectmanager"
 * @author Rafael Claver
 */
if (!defined('DOKU_INC')) die();

class EditProjectAuthorization extends SupervisorProjectAuthorization {

    public function __construct() {
        parent::__construct();
        $this->allowedGroups = ["admin"];
    }

//    public function canRun() {
//        if (parent::canRun()) {
//            if (!$this->isUserGroup(["admin"]) && !$this->isResponsable() && !$this->isAuthor()) {
//                $this->errorAuth['error'] = TRUE;
//                $this->errorAuth['exception'] = 'InsufficientPermissionToEditProjectException';
//                $this->errorAuth['extra_param'] = $this->permission->getIdPage();
//            }
//        }
//        return !$this->errorAuth['error'];
//    }
}
