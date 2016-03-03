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
        $this->permission->setPageExist(WikiIocInfoManager::getInfo('exists'));
        $this->permission->setIsMyOwnNs($this->isMyOwnNs($this->permission->getIdPage(), WikiIocInfoManager::getInfo('client')));
    }

    public function isMyOwnNs($page, $user) {
        global $conf;
        include_once(WIKI_IOC_MODEL . 'conf/default.php');
        $namespace = substr($page, 0, strrpos($page, ":"));
        $user_name = substr($namespace, strrpos($namespace, ":") + 1);
        $userpage_ns = ":" . $namespace;
        $ret = FALSE;
        $ret = (WikiIocInfoManager::getInfo('namespace') == $namespace
                && $user_name == $user
                && $conf['userpage_allowed'] === 1
                && ($userpage_ns == $conf['userpage_ns'] . $user ||
                    $userpage_ns == $conf['userpage_discuss_ns'] . $user)
                );
        return $ret;
    }
    
    protected abstract function getPermissionException();
}