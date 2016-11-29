<?php
/**
 * AbstractCommandAuthorization: define la clase de autorizaciones de los comandos
 *
 * @author Rafael Claver
 */
if (!defined('DOKU_INC') ) die();
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_INC . 'lib/plugins/wikiiocmodel/');

require_once (WIKI_IOC_MODEL . 'WikiIocInfoManager.php');

abstract class AbstractCommandAuthorization {

    protected $permission;
    protected $errorAuth = array(
                              'error' => TRUE
                             ,'exception' => ''
                             ,'extra_param' => ''
                           );
    /**
     * getPermissionInstance: Devuelve una nueva instancia de la clase Permission
     */
    abstract protected function getPermissionInstance();
    
    public function __construct() {}

    /**
     * Responde a la pregunta: ¿los permisos permiten la ejecución del comando?
     * @return bool. Indica si se han obtenido, o no, los permisos generales
     */
    public function canRun() {
        $this->errorAuth['error'] = FALSE;
        $this->errorAuth['exception'] =  '';
        $this->errorAuth['extra_param'] = '';
        
        if ($this->permission->getAuthenticatedUsersOnly()) {
            if (($this->errorAuth['error'] = !$this->permission->getSecurityTokenVerified())){
                $this->errorAuth['exception'] = 'AuthorizationNotTokenVerified';
            } else { 
                if (($this->errorAuth['error'] = !$this->permission->getUserAuthenticated())) {
                    $this->errorAuth['exception'] = 'AuthorizationNotUserAuthenticated';
                } else {
                    if (($this->errorAuth['error'] = !$this->isCommandAllowed())){
                        $this->errorAuth['exception'] = 'AuthorizationNotCommandAllowed';
                    }
                }
            }
        }
        return !$this->errorAuth['error'];
    }
    
    public function getPermission() {
        return $this->permission;
    }

    public function getAuthorizationError($key) {
        return $this->errorAuth[$key];
    }
    
    public function setPermission($command) {
        WikiIocInfoManager::setIsMediaAction($command->getNeedMediaInfo());
        WikiIocInfoManager::setParams($command->getParams());
        if ($this->permission === NULL) {
            $this->_createPermission($command);
        }
    }

    private function _createPermission($command) {
        $this->permission = $this->getPermissionInstance();
    
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
