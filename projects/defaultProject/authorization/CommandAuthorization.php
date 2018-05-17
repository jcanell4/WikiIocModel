<?php
/**
 * CommandAuthorization: define la clase de autorizaciones de los comandos
 *
 * @author Rafael Claver
 */
if (!defined('DOKU_INC')) die();
if (!defined('DOKU_LIB_IOC')) define('DOKU_LIB_IOC', DOKU_INC.'lib/lib_ioc/');
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_INC . "lib/plugins/wikiiocmodel/");
if (!defined('WIKI_IOC_PROJECTS')) define('WIKI_IOC_PROJECTS', WIKI_IOC_MODEL . 'projects/');

require_once (DOKU_INC . 'inc/common.php');
require_once (DOKU_INC . 'inc/auth.php');
require_once(DOKU_LIB_IOC . "wikiiocmodel/DefaultProjectModelExceptions.php");
require_once (WIKI_IOC_PROJECTS . 'defaultProject/authorization/Permission.php');

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
        $this->permission->setIdPage($command->getParams('id'));
        if(is_array(WikiIocInfoManager::getInfo('userinfo'))){
            $this->permission->setUserGroups(WikiIocInfoManager::getInfo('userinfo')['grps']);
        }
        $this->permission->setInfoPerm(WikiIocInfoManager::getInfo('perm'));
    }

    /* pendent de convertir a private quan no l'utilitzi ajax.php(duplicat) ni login_command */
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