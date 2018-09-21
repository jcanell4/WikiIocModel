<?php
/**
 * CommandAuthorization: define la clase de autorizaciones de los comandos del proyecto "documentation"
 * @author Rafael Claver
 */
if (!defined('DOKU_INC')) die();
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_INC . "lib/plugins/wikiiocmodel/");
require_once(WIKI_IOC_MODEL."authorization/BasicCommandAuthorization.php");

class ProjectCommandAuthorization extends BasicCommandAuthorization {

    public function setPermission($command) {
        parent::setPermission($command);
        $this->permission->setAuthor($command->getKeyDataProject(Permission::ROL_AUTOR));
        $this->permission->setResponsable($command->getKeyDataProject(Permission::ROL_RESPONSABLE));
        if ($this->isResponsable()) {
            $this->permission->setRol(Permission::ROL_RESPONSABLE);
        }else if ($this->isAuthor()) {
            $this->permission->setRol(Permission::ROL_AUTOR);
        }
    }

    public function isAuthor() {
        global $_SERVER;
        return (in_array($_SERVER['REMOTE_USER'], $this->permission->getAuthor()));
    }

    public function isResponsable() {
        global $_SERVER;
        return (in_array($_SERVER['REMOTE_USER'], $this->permission->getResponsable()));
    }

    public function isUserGroup($grups=array()) {
        $ret = FALSE;
        $userGrups = $this->permission->getUserGroups();
        foreach ($grups as $grup) {
            $ret |= in_array($grup, $userGrups);
        }
        return $ret;
    }

}