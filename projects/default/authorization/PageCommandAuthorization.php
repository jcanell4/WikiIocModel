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
require_once (WIKI_IOC_MODEL . 'projects/default/authorization/CommandAuthorization.php');

abstract class PageCommandAuthorization extends CommandAuthorization {

    public function __construct($params) {
        parent::__construct($params);
    }

    public function canRun($permission = NULL) {
        if ( parent::canRun($permission) ) { 
            if (!$permission->getIsMyOwnNs()) {
                $exception = $this->getPermissionException($permission);
                if ($exception) {
                    $this->errorAuth['error'] = TRUE;
                    $this->errorAuth['exception'] = $exception;
                    $this->errorAuth['extra_param'] = $this->permission->getIdPage();
                }
            }
        }
        return !$this->errorAuth['error'];
    }
    
    public function getPermission($command) {
        parent::getPermission($command);
        $this->permission->setPageExist(WikiIocInfoManager::getInfo('exists'));
        $this->permission->setIsMyOwnNs($this->isMyOwnNs($this->permission->getIdPage(), WikiIocInfoManager::getInfo('client')));
        return $this->permission;
    }

    public function isMyOwnNs($page, $user) {
        global $conf;
        include_once(WIKI_IOC_MODEL . 'conf/default.php');
        $namespace = substr($page, 0, strrpos($page, ":"));
        $userpage_ns = ":" . $namespace;
        $user_name = substr($userpage_ns, strrpos($userpage_ns, ":") + 1);
        $ret = FALSE;
        $ret = (WikiIocInfoManager::getInfo('namespace') == $namespace
                && $user_name == $user
                && $conf['userpage_allowed'] === 1
                && ($userpage_ns == $conf['userpage_ns'] . $user ||
                    $userpage_ns == $conf['userpage_discuss_ns'] . $user)
                );
        return $ret;
    }
    
    protected abstract function getPermissionException($permission);
}