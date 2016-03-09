<?php
/**
 * Permission: define la clase de permisos
 *
 * @author Rafael Claver
 */
if (!defined('DOKU_INC') ) die();
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_INC . 'lib/plugins/wikiiocmodel/');

require_once (WIKI_IOC_MODEL . 'AbstractPermission.php');

class Permission extends AbstractPermission {
    
//    private $isDenied; Gestionat per DokuAction
    private $info_perm;
    private $readonly=FALSE;
    private $pageExist;
    private $isMyOwnNs;
    
    public function getInfoPerm() {
        return $this->info_perm;
    }
  
    public function setInfoPerm($info_perm) {
        $this->info_perm = $info_perm;
    }
  
//  versió molt antiga. Les seves succesores estan Gestionades per DokuAction
//    public function isDenied() {
//        return $this->cmdAuthorization->isDenied();
//    }
    
//  Gestionat per DokuAction
//    public function isDenied() {
//        return $this->isDenied;
//    }
//    
//    public function setIsDenied($isDenied) {
//        $this->isDenied = $isDenied;
//    }
  
    public function isReadOnly(){
        return $this->readonly;
    }
    
    public function setReadOnly($readonly){
        $this->readonly = $readonly;
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
