<?php
/**
 * WriteAuthorization: Extensión clase Autorización para los comandos 
 * que precisan una autorización mínima de AUTH_EDIT
 * 
 * @author Rafael Claver
 */
if (!defined('DOKU_INC')) die();
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_INC . 'lib/plugins/wikiiocmodel/');
require_once (DOKU_INC . 'inc/auth.php');
require_once (WIKI_IOC_MODEL . 'default/authorization/CommandAuthorization.php');

class WriteAuthorization extends CommandAuthorization {

    const NOT_AUTH_WRITE = 256 * AUTH_EDIT;

    public function canRun($permission = NULL) {
        $ret = parent::canRun($permission);
        if ( $this->permission->getInfoPerm() < AUTH_EDIT) {
            $ret += NOT_AUTH_WRITE;
        }
        return $ret;
    }
}