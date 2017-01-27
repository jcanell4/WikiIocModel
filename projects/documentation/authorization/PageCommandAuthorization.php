<?php
/**
 * PageCommandAuthorization: define la clase de autorizaciones de los comandos
 * con acceso a páginas. Es una extensión de la jerarquía Authorization
 *
 * @author Rafael Claver
 */
if (!defined('DOKU_INC')) die();
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_INC . 'lib/plugins/wikiiocmodel/');
define('WIKI_IOC_PROJECT', WIKI_IOC_MODEL . 'projects/documentation/');

require_once (WIKI_IOC_MODEL . 'WikiIocInfoManager.php');
require_once (WIKI_IOC_PROJECT . 'authorization/CommandAuthorization.php');

abstract class PageCommandAuthorization extends CommandAuthorization {

    public function __construct() {
        parent::__construct();
    }

    public function canRun() {
        if ( parent::canRun() ) { 
            $exception = $this->getPermissionException($this->permission);
            if ($exception) {
                $this->errorAuth['error'] = TRUE;
                $this->errorAuth['exception'] = $exception;
                $this->errorAuth['extra_param'] = $this->permission->getIdPage();
            }
        }
        return !$this->errorAuth['error'];
    }
    
    public function setPermission($command) {
        parent::setPermission($command);
        $this->permission->setResourceExist(WikiIocInfoManager::getInfo(WikiIocInfoManager::KEY_EXISTS));
    }
    
    protected abstract function getPermissionException();
}