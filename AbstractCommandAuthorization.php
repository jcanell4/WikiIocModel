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

    protected $command;    //command_class object
    protected $permission;
    
    public function __construct() {}
//    public abstract function canRun($permission = NULL);
//    public abstract function getPermission($params=array());
    
    /* 
     * Responde a la pregunta: ¿los permisos permiten la ejecución del comando?
     * @return bool. Indica si se han obtenido, o no, los permisos generales
     */
    public function canRun($permis = NULL) {
        if ($permis === NULL) {
            $permis = $this->getPermission();
        }
        $ret = ( ! $permis->getAuthenticatedUsersOnly()
                || $permis->getIsSecurityTokenVerified()
                && $permis->getIsUserAuthenticated()
                && $permis->getIsCommandAllowed() );
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
        global $INFO;
        
        $permis = new Permission($this);
        $this->permission = &$permis;  //Comprovar el pas per referència
        
        $this->permission->setAuthenticatedUsersOnly($this->command->getAuthenticatedUsersOnly());
        $this->permission->setIsSecurityTokenVerified($this->isSecurityTokenVerified());
        $this->permission->setIsUserAuthenticated($this->isUserAuthenticated());
        $this->permission->setIsCommandAllowed($this->isCommandAllowed());
        $this->permission->setInfoWritable($INFO['writable']);
        $this->permission->setInfoIsadmin($INFO['isadmin']);
        $this->permission->setInfoIsmanager($INFO['ismanager']);
    }

}
