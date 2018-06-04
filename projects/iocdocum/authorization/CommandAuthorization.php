<?php
/**
 * CommandAuthorization: define la clase de autorizaciones de los comandos del proyecto "iocdocum"
 * @author Rafael Claver
 */
if (!defined('DOKU_INC')) die();
if (!defined('DOKU_LIB_IOC')) define('DOKU_LIB_IOC', DOKU_INC.'lib/lib_ioc/');
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC."lib/plugins/");
if (!defined('WIKI_IOC_PROJECTS')) define('WIKI_IOC_PROJECTS', DOKU_PLUGIN."wikiiocmodel/projects/");

require_once(DOKU_INC."inc/common.php");
require_once(DOKU_INC."inc/auth.php");
require_once(DOKU_LIB_IOC."wikiiocmodel/ProjectModelExceptions.php");
require_once(DOKU_PLUGIN."ajaxcommand/defkeys/ProjectKeys.php");
require_once(WIKI_IOC_PROJECTS."iocdocum/authorization/Permission.php");

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
        $this->permission->setIdPage($command->getParams(AjaxKeys::KEY_ID));
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

    /* Comproba si l'usuari ha fet login */
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