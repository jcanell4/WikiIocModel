<?php
/**
 * CreateAuthorization: Extensión clase Autorización para los comandos 
 * que precisan una autorización mínima de AUTH_CREATE
 * 
 * @author Rafael Claver
 */
if (!defined('DOKU_INC')) die();
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_INC . 'lib/plugins/wikiiocmodel/');
require_once (DOKU_INC . 'inc/auth.php');
// És obligatori carregar les excepcions específiques que genera l'autorització d'aquesta comanda
require_once (WIKI_IOC_MODEL . 'default/DokuModelExceptions.php');
require_once (WIKI_IOC_MODEL . 'default/authorization/CommandAuthorization.php');

class CreateAuthorization extends CommandAuthorization {
    /*
    public function canRun($permission = NULL) {
        $ret = parent::canRun($permission);
        $ret = $ret && $this->permission->getInfoPerm() >= AUTH_CREATE;
        return $ret;
    }
    */

    const NOT_AUTH_CREATE = 256 * AUTH_CREATE;

    public function canRun($permission = NULL) {
        $ret = parent::canRun($permission);
        if ( $this->permission->getInfoPerm() < AUTH_CREATE) {
            $this->permission->error = 1005;
            $ret += NOT_AUTH_CREATE;
        }
        return $ret;
    }
}
