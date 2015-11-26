<?php
/* 
 * PageAuthorization: Extensión clase Autorización para el comando 'page'
 * @author Rafael Claver
 */
if (!defined('DOKU_INC')) die();
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_INC . 'lib/plugins/wikiiocmodel/');
require_once (WIKI_IOC_MODEL . 'default/authorization/CommandAuthorization.php');

class PageAuthorization extends CommandAuthorization {

    public function canRun($permission = NULL) {
        $ret = parent::canRun($permission);
        $ret = $ret && $this->permission->getInfoPerm() >= 1;
        return $ret;
    }

    public function getPermission($params=array()) {
        global $INFO;
        global $ID;
        $ID = $params['id'];
        parent::getPermission($params);
        $this->permission->setInfoPerm($INFO['perm']);
        return $this->permission;
    }

}
