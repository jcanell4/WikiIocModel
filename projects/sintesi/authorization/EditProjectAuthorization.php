<?php
/**
 * EditProjectAuthorization: Extensión clase Autorización para los comandos
 * que precisan una autorización mínima de AUTH_EDIT y
 * que el usuario sea el Responsable o del grupo "admin" o "projectmanager"
 * @author Rafael Claver
 */
if (!defined('DOKU_INC')) die();

class EditProjectAuthorization extends SupervisorProjectAuthorization {

//    public function __construct() {
//        parent::__construct();
//        array_merge($this->allowedGroups, ['admin']);
//        array_merge($this->allowedRoles, [Permission::ROL_RESPONSABLE, Permission::ROL_AUTOR]);
//    }

//    public function canRun() {
//        if (parent::canRun()) {
//            if (!$this->isUserGroup($this->allowedGroups) && !$this->isUserRole($this->allowedRoles)) {
//                $this->errorAuth['error'] = TRUE;
//                $this->errorAuth['exception'] = 'InsufficientPermissionToEditProjectException';
//                $this->errorAuth['extra_param'] = $this->permission->getIdPage();
//            }
//        }
//        return !$this->errorAuth['error'];
//    }

}
