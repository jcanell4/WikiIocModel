<?php
/**
 * CommandAuthorization: define la clase de autorizaciones de los comandos del proyecto "documentation"
 * @author Rafael Claver
 */
if (!defined('DOKU_INC')) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . "lib/plugins/");
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_PLUGIN . "wikiiocmodel/");
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
        if (($author = $this->permission->getAuthor()))
            return (in_array($_SERVER['REMOTE_USER'], $author));
        else
            return FALSE;
    }

    public function isResponsable() {
        global $_SERVER;
        if (($responsable = $this->permission->getResponsable()))
            return (in_array($_SERVER['REMOTE_USER'], $responsable));
        else
            return FALSE;
    }
}