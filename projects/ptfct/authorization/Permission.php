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

    public function setSupervisor($user) {
        if(is_string($user) && !empty($user)){
            $this->supervisor = preg_split("/[\s,]+/", $user);
        }        
    }

}
