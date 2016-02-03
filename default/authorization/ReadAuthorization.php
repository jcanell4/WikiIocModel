<?php
/**
 * ReadAuthorization: Extensión clase Autorización para los comandos 
 * que precisan una autorización mínima de AUTH_READ
 * 
 * @author Rafael Claver
 */
if (!defined('DOKU_INC')) die();
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_INC . 'lib/plugins/wikiiocmodel/');
require_once (DOKU_INC . 'inc/auth.php');
require_once (WIKI_IOC_MODEL . 'default/DokuModelExceptions.php');
require_once (WIKI_IOC_MODEL . 'default/authorization/CommandAuthorization.php');

class ReadAuthorization extends CommandAuthorization {
    /*
    public function canRun($permission = NULL) {
        $ret = parent::canRun($permission);
        $ret = $ret && $this->permission->getInfoPerm() >= AUTH_READ;
        return $ret;
    }
     */

    const NOT_AUTH_READ = 256 * AUTH_READ;

    public function canRun($permission = NULL) {
        parent::canRun($permission);
        if ( $this->permission->getInfoPerm() < AUTH_READ) {
            $this->errorAuth['error'] += self::NOT_AUTH_READ;
            $this->errorAuth['exception'] = 'InsufficientPermissionToViewPageException';
            $this->errorAuth['extra_param'] = $this->permission->getIdPage();
        }
        return $this->errorAuth['error'];
    }
}
