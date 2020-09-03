<?php
/**
 * ViewProjectAuthorization: define la clase de autorizaciones de los comandos del proyecto "sintesi"
 * @author Rafael Claver
 */
if (!defined('DOKU_INC')) die();

class ViewProjectAuthorization extends EditProjectAuthorization {

    public function __construct() {
        parent::__construct();
        $this->allowedGroups[] = "platreballfp";
    }

//    public function canRun() {
//        if ($this->isUserGroup(array("platreballfp"))
//                || $this->permission->getRol() === Permission::ROL_SUPERVISOR) {
//            return true;
//        }else {
//            return parent::canRun();
//        }
//    }

}
