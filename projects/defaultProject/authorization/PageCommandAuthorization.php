<?php
/**
 * PageCommandAuthorization: define la clase de autorizaciones de los comandos
 * con acceso a páginas. Es una extensión de la jerarquía Authorization
 *
 * @author Rafael Claver
 */
if (!defined('DOKU_INC')) die();
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_INC . 'lib/plugins/wikiiocmodel/');

require_once (WIKI_IOC_MODEL . 'WikiIocInfoManager.php');
require_once (WIKI_IOC_MODEL . 'projects/defaultProject/authorization/CommandAuthorization.php');

abstract class PageCommandAuthorization extends CommandAuthorization {

    public function __construct($params) {
        parent::__construct($params);
    }

    public function canRun() {  // el parámetro $permission contiene lo mismo que $this->permission
        if ( parent::canRun() ) { 
            if (!$this->permission->getIsMyOwnNs()) {
                $exception = $this->getPermissionException($this->permission);
                if ($exception) {
                    $this->errorAuth['error'] = TRUE;
                    $this->errorAuth['exception'] = $exception;
                    $this->errorAuth['extra_param'] = $this->permission->getIdPage();
                }
            }
        }
        return !$this->errorAuth['error'];
    }
    
    public function setPermission($command) {
        parent::setPermission($command);
        $this->permission->setPageExist(WikiIocInfoManager::getInfo(WikiIocInfoManager::KEY_EXISTS));
        $this->permission->setIsMyOwnNs($this->isMyOwnNs($this->permission->getIdPage(), WikiIocInfoManager::getInfo('client')));
    }

    public function isMyOwnNs($page, $user) {
        $namespace = substr($page, 0, strrpos($page, ":"));
        $user_name = substr($namespace, strrpos($namespace, ":") + 1);
        $userpage_ns = ":" . $namespace;
        $ret = (WikiIocInfoManager::getInfo('namespace') == $namespace
                && $user_name == $user
                && WikiGlobalConfig::getConf('userpage_allowed', 'wikiiocmodel') === 1
                && ($userpage_ns == WikiGlobalConfig::getConf('userpage_ns', 'wikiiocmodel') . $user ||
                    $userpage_ns == WikiGlobalConfig::getConf('userpage_discuss_ns', 'wikiiocmodel') . $user)
                );
        return $ret;
    }
    
    protected abstract function getPermissionException();
}