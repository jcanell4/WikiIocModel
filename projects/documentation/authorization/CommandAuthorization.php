<?php
/**
 * CommandAuthorization: define la clase de autorizaciones de los comandos del proyecto "documentation"
 * @author Rafael Claver
 */
if (!defined('DOKU_INC')) die();
define('DOKU_PLUGIN', DOKU_INC."lib/plugins/");
define('WIKI_IOC_PROJECTS', DOKU_PLUGIN."wikiiocmodel/projects/");

require_once(DOKU_INC."inc/common.php");
require_once(DOKU_INC."inc/auth.php");
require_once(DOKU_PLUGIN."ajaxcommand/defkeys/ProjectKeys.php");
require_once(DOKU_PLUGIN."wikiiocmodel/WikiIocInfoManager.php");
require_once(DOKU_PLUGIN."wikiiocmodel/AbstractCommandAuthorization.php");
require_once(WIKI_IOC_PROJECTS."documentation/DocumentationModelExceptions.php");
require_once(WIKI_IOC_PROJECTS."documentation/authorization/Permission.php");

class CommandAuthorization extends AbstractCommandAuthorization {

    public function __construct() {
        parent::__construct();
    }

    protected function getPermissionInstance() {
        $permis = new Permission($this);
        $ret = &$permis;
        return $ret;
    }

    public function setPermission($command) {
        parent::setPermission($command);
        $this->permission->setIdPage($command->getParams(ProjectKeys::KEY_ID));
        $this->permission->setUserGroups(WikiIocInfoManager::getInfo('userinfo')['grps']);
        $this->permission->setInfoPerm(WikiIocInfoManager::getInfo('perm'));
        $this->permission->setAuthor($command->getKeyDataProject(Permission::ROL_AUTOR));
        $this->permission->setResponsable($command->getKeyDataProject(Permission::ROL_RESPONSABLE));
        if ($this->isResponsable()) {
            $this->permission->setRol(Permission::ROL_RESPONSABLE);
        }else if ($this->isAuthor()) {
            $this->permission->setRol(Permission::ROL_AUTOR);
        }
    }

    /* pendent de convertir a private quan no l'utilitzi ajax.php(duplicat) ni login_command */
    public function isUserAuthenticated() {
        global $_SERVER;
        return $_SERVER['REMOTE_USER'] ? TRUE : FALSE;
    }

    /**
     * Comproba si el token de seguretat està verificat, fent servir una funció de la DokuWiki.
     * @return bool
    */
    public function isSecurityTokenVerified() {
        return checkSecurityToken();
    }

    public function isAuthor() {
        global $_SERVER;
        return ($this->permission->getAuthor() === $_SERVER['REMOTE_USER']);
    }

    public function isResponsable() {
        global $_SERVER;
        return ($this->permission->getResponsable() === $_SERVER['REMOTE_USER']);
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