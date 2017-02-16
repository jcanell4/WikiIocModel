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
    
    private $info_perm;
    private $resourceExist;
    private $overwriteRequired;
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
    
    public function getResourceExist() {
        return $this->resourceExist;
    }
  
    public function setResourceExist($resourceExist) {
        $this->resourceExist = $resourceExist;
    }

    public function getOverwriteRequired() {
        return $this->overwriteRequired;
    }
  
    public function setOverwriteRequired($overwriteRequired) {
        $this->overwriteRequired = $overwriteRequired;
    }
  
    public function getIsMyOwnNs() {
        return $this->isMyOwnNs;
    }

    public function setIsMyOwnNs($isMyOwnNs) {
        $this->isMyOwnNs = $isMyOwnNs;
    }
}
