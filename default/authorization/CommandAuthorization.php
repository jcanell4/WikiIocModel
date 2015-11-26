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
require_once (WIKI_IOC_MODEL . 'AbstractAuthorizationManager.php');
require_once (WIKI_IOC_MODEL . 'WikiIocInfoManager.php');
require_once (WIKI_IOC_MODEL . 'default/authorization/Permission.php');

class CommandAuthorization extends AbstractAuthorizationManager {
    protected $command;    //command_class object
    protected $modelWrapper;    //está pendiente separar completamente el DokuModelAdapter de la Autorización
    protected $permission;

    public function __construct($params) {
        parent::__construct();
        $this->command = $params;
        $this->modelWrapper = $this->command->getModelWrapper();
        
    }

    /* 
     * Responde a la pregunta: ¿los permisos permiten la ejecución del comando?
     * @return bool. Indica si se han obtenido, o no, los permisos generales
     */
    public function canRun($permission = NULL) {
        if ($permission === NULL) {
            $permission = $this->getPermission();
        }
        $ret = ( ! $permission->getAuthenticatedUsersOnly()
                || $permission->getIsSecurityTokenVerified()
                && $permission->getIsUserAuthenticated()
                && $permission->getIsAuthorized() );
        return $ret;
    }
    
    public function getPermission($params=array()) {
        WikiIocInfoManager::loadInfo();
        if ($this->permission === NULL) {
            $this->createPermission();
        }
        return $this->permission;
    }

    private function createPermission() {
        $permission = new Permission($this);
        $this->permission = &$permission;  //Comprovar el pas per referència
        
        $this->permission->setAuthenticatedUsersOnly($this->command->getAuthenticatedUsersOnly());
        $this->permission->setIsSecurityTokenVerified($this->isSecurityTokenVerified());
        $this->permission->setIsUserAuthenticated($this->isUserAuthenticated());
        $this->permission->setIsAuthorized($this->isAuthorized());
    }

    /* pendent de convertir a private quan no l'utilitzi ajax.php(duplicat) ni login_command */
    public function isUserAuthenticated() {
        global $_SERVER;
        return $_SERVER['REMOTE_USER'] ? TRUE : FALSE;
    }

    /* pendent de convertir a private quan no l'utilitzi abstract_command_class */
    public function isAuthorized() {
        $permissionFor = $this->command->getPermissionFor();
        $grup = $this->getUserGroup();
        $found = sizeof($permissionFor) == 0 || !is_array($grup);
        for($i = 0; !$found && $i < sizeof($grup); $i++) {
            $found = in_array($grup[$i], $permissionFor);
        }
        return $found;
    }

    /**
     * @return string[] hash amb els grups de l'usuari
    */
    private function getUserGroup() {
        global $INFO;
        return $INFO['userinfo']['grps'];
    }
    
    /**
     * Comproba si el token de seguretat està verificat o no fent servir una funció de la DokuWiki.
     * @return bool
    */
    public function isSecurityTokenVerified() {
        return checkSecurityToken();
    }

    /**
     * Si el valor de la variable global $ACT es 'denied' retorna false, en cualsevol altre cas retorna true.
     * @return bool
     */
    public function isDenied() {
	global $ACT;
	$this->modelWrapper->setParams('do', $ACT);
	return $ACT == DW_ACT_DENIED;
    }
}

