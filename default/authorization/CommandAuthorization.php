<?php
/**
 * CommandAuthorization: define la clase de autorizaciones de los comandos
 *
 * @author Rafael Claver
 */
if (!defined('DOKU_INC') ) die();
if(!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_INC . 'lib/plugins/wikiiocmodel/');
require_once (WIKI_IOC_MODEL . 'AbstractAuthorizationManager.php');
require_once (WIKI_IOC_MODEL . 'default/authorization/Permission.php');

class CommandAuthorization extends AbstractAuthorizationManager {
    private $command;    //command_class object
    private $modelWrapper;
    private $permission;


    public function __construct($params) {
        parent::__construct();
        $this->command = $params;
        $this->modelWrapper = $this->command->getModelWrapper();
    }

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
    
    public function getPermission() {
        $this->createPermission();
        return $this->permission;
    }

    private function createPermission() {
        $permission = new Permission($this->modelWrapper);
        // Comprovar el pas per referència 
        $this->permission = &$permission;
        
        $this->permission->setAuthenticatedUsersOnly($this->command->getAuthenticatedUsersOnly());
        $this->permission->setIsSecurityTokenVerified($this->modelWrapper->isSecurityTokenVerified());
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
        $grup = $this->modelWrapper->getUserGroup();
        $found = sizeof($permissionFor) == 0 || !is_array($grup);
        for($i = 0; !$found && $i < sizeof($grup); $i++) {
            $found = in_array($grup[$i], $permissionFor);
        }
        return $found;
    }
}

