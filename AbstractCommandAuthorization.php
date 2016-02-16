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

    protected $permission;
    protected $errorAuth = array(
                              'error' => TRUE
                             ,'exception' => ''
                             ,'extra_param' => ''
                           );
    
    public function __construct() {}
    
    /* 
     * Responde a la pregunta: ¿los permisos permiten la ejecución del comando?
     * @return bool. Indica si se han obtenido, o no, los permisos generales
     */
    public function canRun($permis) {
        
        $this->errorAuth = array(
                              'error' => FALSE
                             ,'exception' => ''
                             ,'extra_param' => ''
                           );
        
        if ($permis->getAuthenticatedUsersOnly()) {
            if ($this->errorAuth['error'] = !$permis->getSecurityTokenVerified()){
                $this->errorAuth['exception'] = 'AuthorizationNotTokenVerified';
            } else { // getSecurityTokenVerified = OK!
                if ($this->errorAuth['error'] = !$permis->getUserAuthenticated()) {
                    $this->errorAuth['exception'] = 'AuthorizationNotUserAuthenticated';
                } else {
                    if ($this->errorAuth['error'] = !$this->isCommandAllowed()){
                        $this->errorAuth['exception'] = 'AuthorizationNotCommandAllowed';
                    }
                }
            }
        }
        return !$this->errorAuth['error'];
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
