<?php
/**
 * Permission: define la clase de permisos de este proyecto
 *
 * @author Rafael Claver
 */
if (!defined('DOKU_INC') ) die();
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_INC . 'lib/plugins/wikiiocmodel/');

require_once (WIKI_IOC_MODEL . 'AbstractPermission.php');

class Permission extends AbstractPermission {
    
    private $info_perm;
    private $pageExist;
    private $isMyOwnNs;
    
    public function getInfoPerm() {
        return $this->info_perm;
    }
  
    public function setInfoPerm($info_perm) {
        $this->info_perm = $info_perm;
    }
  
    public function isReadOnly(){
        return ($this->getInfoPerm() < AUTH_EDIT);
    }
    
    public function getPageExist() {
        return $this->pageExist;
    }
  
    public function setPageExist($pageExist) {
        $this->pageExist = $pageExist;
    }

    public function getIsMyOwnNs() {
        return $this->isMyOwnNs;
    }

    public function setIsMyOwnNs($isMyOwnNs) {
        $this->isMyOwnNs = $isMyOwnNs;
    }
}
