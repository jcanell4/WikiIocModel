<?php
/**
 * SupervisorProjectAuthorization: Extensión clase Autorización para los proyectos
 *                                 que tienen atributo de supervisor
  * @author Rafael Claver
 */
if (!defined('DOKU_INC')) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . "lib/plugins/");
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_PLUGIN . "wikiiocmodel/");
require_once (WIKI_IOC_MODEL . "authorization/ProjectCommandAuthorization.php");

class SupervisorProjectAuthorization extends ProjectCommandAuthorization {

    public function setPermission($command) {

        $this->permission->setSupervisor($command->getKeyDataProject(Permission::ROL_SUPERVISOR));

        if ($this->isSupervisor()) {
            $this->permission->setRol(Permission::ROL_SUPERVISOR);
        }
        parent::setPermission($command);
    }

    public function isSupervisor() {
        global $_SERVER;
        $supervisor = $this->permission->getSupervisor();

        if ($supervisor) {
            $ret = (in_array($_SERVER['REMOTE_USER'], $supervisor));
        }
        return $ret;
    }

}
