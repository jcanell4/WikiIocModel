<?php
/* 
 * EditAuthorization: Extensión clase Autorización para el comando 'edit'
 * @author Rafael Claver
 */
if (!defined('DOKU_INC')) die();
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_INC . 'lib/plugins/wikiiocmodel/');
require_once (DOKU_INC . 'inc/auth.php');
require_once (WIKI_IOC_MODEL . 'WikiIocInfoManager.php');
require_once (WIKI_IOC_MODEL . 'default/authorization/CommandAuthorization.php');

class EditAuthorization extends CommandAuthorization {

    public function canRun($permission = NULL) {
        $ret = parent::canRun($permission);
        $ret = $ret && $this->permission->getInfoPerm() >= AUTH_READ;
        return $ret;
    }

}
