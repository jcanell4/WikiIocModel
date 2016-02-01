<?php
/**
 * UploadAuthorization: Extensión clase Autorización para los comandos 
 * que precisan una autorización mínima de AUTH_UPLOAD
 * 
 * @author Rafael Claver
 */
if (!defined('DOKU_INC')) die();
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_INC . 'lib/plugins/wikiiocmodel/');
require_once (DOKU_INC . 'inc/auth.php');
require_once (WIKI_IOC_MODEL . 'default/authorization/CommandAuthorization.php');

class UploadAuthorization extends CommandAuthorization {

//    public function canRun($permission = NULL) {
//        $ret = parent::canRun($permission);
//        $ret = $ret && $this->permission->getInfoPerm() >= AUTH_UPLOAD;
//        return $ret;
//    }

    const NOT_AUTH_UPLOAD = 256 * AUTH_UPLOAD;

    public function canRun($permission = NULL) {
        $ret = parent::canRun($permission);
        if ( $this->permission->getInfoPerm() < AUTH_UPLOAD) {
            $ret += NOT_AUTH_UPLOAD;
        }
        return $ret;
    }
}
