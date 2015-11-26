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
    private $info_writable;
    
    public function getInfoPerm() {
        return $this->info_perm;
    }
  
    public function setInfoPerm($info_perm) {
        $this->info_perm = $info_perm;
    }
  
    public function getInfoWritable() {
        return $this->info_writable;
    }
  
    public function setInfoWritable($info_writable) {
        $this->info_writable = $info_writable;
    }
  
    public function isDenied() {
        return $this->cmdAuthorization->isDenied();
    }
    
}
