<?php
/**
 * AbstractCommandAuthorization: define la clase de autorizaciones de los comandos
 *
 * @author Rafael Claver
 */
if (!defined('DOKU_INC') ) die();
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_INC . 'lib/plugins/wikiiocmodel/');

require_once (WIKI_IOC_MODEL . 'WikiIocInfoManager.php');
require_once (WIKI_IOC_MODEL . 'default/authorization/Permission.php');
require_once (WIKI_IOC_MODEL . 'default/DokuModelExceptions.php');

abstract class AbstractCommandAuthorization {
    
    const IOC_AUTH_OK = TRUE;
    const IOC_AUTH_FORBIDEN_ACCESS = FALSE;
    const AUTH_OK = 0;
    const NOT_AUTH_TOKEN_VERIFIED = 1;
    const NOT_AUTH_USER_AUTHENTICATED = 2;
    const NOT_AUTH_COMMAND_ALLOWED = 4;

    protected $permission;
    protected $errorAuth = array(
                              'error' => self::AUTH_OK
                             ,'exception' => ''
                             ,'extra_param' => ''
                           );
    
    public function __construct() {}
    
    /* 
     * Responde a la pregunta: ¿los permisos permiten la ejecución del comando?
     * @return bool. Indica si se han obtenido, o no, los permisos generales
     */
    public function canRun($permis) {
        /*
        $ret = ( ! $permis->getAuthenticatedUsersOnly()
                || $permis->getSecurityTokenVerified()
                && $permis->getUserAuthenticated()
                && $this->isCommandAllowed() );
        */
        if ($permis->getAuthenticatedUsersOnly()) {
            $this->errorAuth['error'] = $permis->getSecurityTokenVerified() ? self::AUTH_OK : self::NOT_AUTH_TOKEN_VERIFIED;
            $this->errorAuth['exception'] = 'AuthorizationNotTokenVerified';
            if ($this->errorAuth['error'] == self::AUTH_OK) {
                $this->errorAuth['error'] = $permis->getUserAuthenticated() ? self::AUTH_OK : self::NOT_AUTH_USER_AUTHENTICATED;
                $this->errorAuth['exception'] = 'AuthorizationNotUserAuthenticated';
            }
            if ($this->errorAuth['error'] == self::AUTH_OK) {
                $this->errorAuth['error'] = $this->isCommandAllowed() ? self::AUTH_OK : self::NOT_AUTH_COMMAND_ALLOWED;
                $this->errorAuth['exception'] = 'AuthorizationNotCommandAllowed';
            }
            if ($this->errorAuth['error'] == self::AUTH_OK) {
                $this->errorAuth['exception'] = '';
                $this->errorAuth['extra_param'] = '';
            }
        }
        else {
            $this->errorAuth['error'] = self::AUTH_OK;
        }
            
        return $this->errorAuth['error'];
    }
    
    public function getPermission($command) {
        WikiIocInfoManager::setParams($command->getParams());
        if ($this->permission === NULL) {
            $this->createPermission($command);
        }
        return $this->permission;
    }

    public function getAuthorizationError($key) {
        return $this->errorAuth[$key];
    }
    
    private function createPermission($command) {
        $permis = new Permission($this);
        $this->permission = &$permis;  //Comprovar el pas per referència
        
        $this->permission->setPermissionFor($command->getPermissionFor());
        $this->permission->setAuthenticatedUsersOnly($command->getAuthenticatedUsersOnly());
        $this->permission->setSecurityTokenVerified($this->isSecurityTokenVerified());
        $this->permission->setUserAuthenticated($this->isUserAuthenticated());
        $this->permission->setInfoWritable(WikiIocInfoManager::getInfo('writable'));
        $this->permission->setInfoIsadmin(WikiIocInfoManager::getInfo('isadmin'));
        $this->permission->setInfoIsmanager(WikiIocInfoManager::getInfo('ismanager'));
        $this->permission->setUserGroups(array());
    }

    private function isCommandAllowed() {
        $permissionFor = $this->permission->getPermissionFor();
        $grup = $this->permission->getUserGroups();
        $found = sizeof($permissionFor) == 0 || !is_array($grup);
        for($i = 0; !$found && $i < sizeof($grup); $i++) {
            $found = in_array($grup[$i], $permissionFor);
        }
        return $found;
    }
}
