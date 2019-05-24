<?php
/**
 * Permission: la clase gestiona los permisos de usuario en este proyecto
 * @author Rafael Claver
 */
if (!defined('DOKU_INC') ) die();

class Permission extends ProjectPermission {

    protected $supervisor;   //array
    const ROL_SUPERVISOR = "supervisor";

    public function getSupervisor() {
        return $this->supervisor;
    }

    public function setSupervisor($supervisor) {
        $this->supervisor = preg_split("/[\s,]+/", $supervisor);
    }

}
