<?php
/**
 * BasicCommandAuthorization: define la clase general de autorizaciones de los comandos
  * @author Rafael Claver
 */
if (!defined('DOKU_INC')) die();
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_INC . "lib/plugins/wikiiocmodel/");
require_once (DOKU_INC . "inc/common.php");
require_once (WIKI_IOC_MODEL . "authorization/BasicPermission.php");

class BasicCommandAuthorization extends AbstractCommandAuthorization {

    public function __construct() {
        parent::__construct();
    }

    protected function getPermissionInstance() {
//        $permis = new Permission($this);
//        $ret = &$permis;
//        return $ret;
        return $this->permission;
    }

    public function setPermissionInstance($permission) {
        $this->permission = $permission;
    }

    public function setPermission($command) {
        parent::setPermission($command);
        $this->permission->setIdPage($command->getParams('id'));
        if (is_array(WikiIocInfoManager::getInfo('userinfo'))){
            $this->permission->setUserGroups(WikiIocInfoManager::getInfo('userinfo')['grps']);
        }
        $this->permission->setInfoPerm(WikiIocInfoManager::getInfo('perm'));
    }

    // pendent de convertir a private quan no l'utilitzi login_command
    public function isUserAuthenticated($userId=NULL) {
        global $_SERVER;

        if ($userId) {
            return $_SERVER['REMOTE_USER'] === $userId;
        } else {
            return $_SERVER['REMOTE_USER'] ? TRUE : FALSE;
        }

    }

    /**
     * Comproba si el token de seguretat està verificat, fent servir una funció de la DokuWiki.
     * @return bool
    */
    public function isSecurityTokenVerified() {
        return checkSecurityToken();
    }

}