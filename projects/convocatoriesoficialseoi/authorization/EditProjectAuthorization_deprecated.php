<?php
/**
 * EditProjectAuthorization: Extensión clase Autorización para los comandos
 * que precisan una autorización mínima de AUTH_EDIT y que el usuario sea el Responsable o del grupo "admin" o "projectmanager"
 * @author Rafael Claver
 */
if (!defined('DOKU_INC')) die();
//[JOSEP] ALERTA => RAFA: deprecated vol dir que no es fa servir? ui fa aquest canrun?
class EditProjectAuthorization extends ProjectCommandAuthorization {

    public function __construct() {
        parent::__construct();
        $this->allowedRoles[] = ProjectPermission::ROL_AUTOR;
    }

    public function canRun($permis=AUTH_NONE, $type_exception="Edit") {
        if (!parent::canRun($permis, $type_exception)) {
            if ($this->permission->getInfoPerm() < AUTH_EDIT || !$this->isUserGroup($this->allowedGroups)) {
                $this->errorAuth['error'] = TRUE;
                $this->errorAuth['exception'] = 'InsufficientPermissionToEditProjectException';
                $this->errorAuth['extra_param'] = $this->permission->getIdPage();
            }
        }
        return !$this->errorAuth['error'];
    }
}
