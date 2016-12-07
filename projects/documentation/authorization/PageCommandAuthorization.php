<?php
/**
 * PageCommandAuthorization: define la clase de autorizaciones de los comandos
 * con acceso a páginas. Es una extensión de la jerarquía Authorization
 *
 * @author Rafael Claver
 */
if (!defined('DOKU_INC')) die();
define('WIKI_IOC_MODEL', DOKU_INC . 'lib/plugins/wikiiocmodel/');
define('WIKI_IOC_PROJECTS', WIKI_IOC_MODEL . 'projects/');

require_once (WIKI_IOC_MODEL . 'WikiIocInfoManager.php');
require_once (WIKI_IOC_PROJECTS . 'documentation/authorization/CommandAuthorization.php');

abstract class PageCommandAuthorization extends CommandAuthorization {

    public function __construct($params) {
        parent::__construct($params);
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
        $this->permission->setPageExist(WikiIocInfoManager::getInfo(WikiIocInfoManager::KEY_EXISTS));
    }
    
    protected abstract function getPermissionException();
}