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

abstract class AbstractCommandAuthorization {
    
    const IOC_AUTH_OK = TRUE;
    const IOC_AUTH_FORBIDEN_ACCESS = FALSE;

    //protected $command;   //command_class object
                            // ya no se guarda, se envia directamente a getPermission
    protected $permission;
    
    public function __construct() {}
    
    /* 
     * Responde a la pregunta: ¿los permisos permiten la ejecución del comando?
     * @return bool. Indica si se han obtenido, o no, los permisos generales
     */
    public function canRun($permis) {
        $ret = ( ! $permis->getAuthenticatedUsersOnly()
                || $permis->getSecurityTokenVerified()
                && $permis->getUserAuthenticated()
                && $this->isCommandAllowed()  );
        return $ret;
    }
    
    public function getPermission($command) {
        WikiIocInfoManager::setParams($command->getParams());
        if ($this->permission === NULL) {
            $this->createPermission($command);
        }
        return $this->permission;
    }

    private function createPermission($command) {
        global $INFO;
        
        $permis = new Permission($this);
        $this->permission = &$permis;  //Comprovar el pas per referència
        
        $this->permission->setPermissionFor($command->getPermissionFor());
        $this->permission->setAuthenticatedUsersOnly($command->getAuthenticatedUsersOnly());
        $this->permission->setSecurityTokenVerified($this->isSecurityTokenVerified());
        $this->permission->setUserAuthenticated($this->isUserAuthenticated());
        $this->permission->setInfoWritable(WikiIocInfoManager::getInfo('writable'));
        $this->permission->setInfoIsadmin(WikiIocInfoManager::getInfo('isadmin'));
        $this->permission->setInfoIsmanager(WikiIocInfoManager::getInfo('ismanager'));
    }

}
