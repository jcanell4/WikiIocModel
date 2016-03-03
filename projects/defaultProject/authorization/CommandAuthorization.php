<?php
/**
 * CommandAuthorization: define la clase de autorizaciones de los comandos
 *
 * @author Rafael Claver
 */
if (!defined('DOKU_INC')) die();
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_INC . 'lib/plugins/wikiiocmodel/');
if (!defined('DW_ACT_DENIED')) 	define('DW_ACT_DENIED', "denied" );

require_once (DOKU_INC . 'inc/common.php');
require_once (DOKU_INC . 'inc/auth.php');
require_once (WIKI_IOC_MODEL . 'WikiIocInfoManager.php');
require_once (WIKI_IOC_MODEL . 'AbstractCommandAuthorization.php');

class CommandAuthorization extends AbstractCommandAuthorization {

    protected $modelWrapper;    //está pendiente separar completamente el DokuModelAdapter de la Autorización

    public function __construct($params) {
        parent::__construct();
         $this->modelWrapper = $params->getModelWrapper();
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
//        $this->permission->setIsDenied($this->isDenied());  Gestionat per DokuAction
    }

    /**
     * Comproba si el token de seguretat està verificat o no fent servir una funció de la DokuWiki.
     * @return bool
    */
    public function isSecurityTokenVerified() {
        return checkSecurityToken();
    }

//  Gestionat per DokuAction
//    public function isDenied() {
//	global $ACT;
//	$this->modelWrapper->setParams('do', $ACT);
//	return $ACT == DW_ACT_DENIED;
//    }

}