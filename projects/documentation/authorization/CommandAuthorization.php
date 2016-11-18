<?php
/**
 * CommandAuthorization: define la clase de autorizaciones de los comandos de este proyecto
 *
 * @author Rafael Claver
 */
if (!defined('DOKU_INC')) die();
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_INC . 'lib/plugins/wikiiocmodel/');
define('WIKI_IOC_PROJECT', WIKI_IOC_MODEL . 'projects/documentation/');

require_once (DOKU_INC . 'inc/common.php');
require_once (DOKU_INC . 'inc/auth.php');
require_once (WIKI_IOC_MODEL . 'WikiIocInfoManager.php');
require_once (WIKI_IOC_MODEL . 'AbstractCommandAuthorization.php');
require_once (WIKI_IOC_PROJECT . 'DokuModelExceptions.php');
require_once (WIKI_IOC_PROJECT . 'authorization/Permission.php');

class CommandAuthorization extends AbstractCommandAuthorization {

    public function __construct() {
        parent::__construct();
    }

    /* pendent de convertir a private quan no l'utilitzi ajax.php(duplicat) ni login_command */
    public function isUserAuthenticated() {
        global $_SERVER;
        return $_SERVER['REMOTE_USER'] ? TRUE : FALSE;
    }
    
    public function setPermission($command) {
        parent::setPermission($command);
        $this->permission->setIdPage($command->getParams('id'));
        $this->permission->setUserGroups(WikiIocInfoManager::getInfo('userinfo')['grps']);
        $this->permission->setInfoPerm(WikiIocInfoManager::getInfo('perm'));
    }

    /**
     * Comproba si el token de seguretat està verificat, fent servir una funció de la DokuWiki.
     * @return bool
    */
    public function isSecurityTokenVerified() {
        return checkSecurityToken();
    }

}