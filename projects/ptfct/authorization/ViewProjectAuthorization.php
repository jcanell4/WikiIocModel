<?php
/**
 * CommandAuthorization: define la clase de autorizaciones de los comandos del proyecto "documentation"
 * @author Rafael Claver
 */
if (!defined('DOKU_INC')) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . "lib/plugins/");
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_PLUGIN . "wikiiocmodel/");
require_once (WIKI_IOC_MODEL . "authorization/EditProjectAuthorization.php");

class ViewProjectAuthorization extends EditProjectAuthorization {

    public function canRun() {

        if ($this->permission->getRol() === Permission::ROL_SUPERVISOR) {
            return true;
        } else {
            return parent::canRun();
        }
    }


    public function setPermission($command) {
        parent::setPermission($command);
        $this->permission->setSupervisor($command->getKeyDataProject(Permission::ROL_SUPERVISOR));


        if ($this->isSupervisor()) {
            $this->permission->setRol(Permission::ROL_SUPERVISOR);
        }
    }

    public function isSupervisor() {
        global $_SERVER;

        $supervisor = $this->permission->getSupervisor();

        if (!$supervisor) {
            return false;
        }

        return (in_array($_SERVER['REMOTE_USER'], $supervisor));
    }

}