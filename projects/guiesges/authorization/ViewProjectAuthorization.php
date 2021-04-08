<?php
/**
 * EditProjectAuthorization: Extensión clase Autorización para los comandos
 * que precisan una autorización mínima de AUTH_EDIT y que el usuario sea el Responsable o del grupo "admin" o "projectmanager"
 * @author Rafael Claver
 */
if (!defined('DOKU_INC')) die();

class ViewProjectAuthorization extends ProjectCommandAuthorization {

    public function __construct() {
        parent::__construct();
        $this->allowedGroups[] = "ges";
        $this->allowedGroups[] = "editorges";
        $this->allowedGroups[] = "projectmanager";
        $this->allowedRoles []= ProjectPermission::ROL_AUTOR;;
    }

    public function canRun($permis=AUTH_NONE, $type_exception="View") {
        if (!parent::canRun($permis, $type_exception)) {
            //editorges o ges entren sempre. Si es projectmanager, només si té permisos d'edició o superors
            if($this->isUserGroup(["projectmanager"]) && $this->permission->getInfoPerm() < AUTH_EDIT){ 
                $this->errorAuth['error'] = TRUE;
                $this->errorAuth['exception'] = 'InsufficientPermissionToEditProjectException';
                $this->errorAuth['extra_param'] = $this->permission->getIdPage();
            }
        }
        return !$this->errorAuth['error'];
    }
}
