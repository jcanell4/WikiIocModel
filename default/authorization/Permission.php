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
    private $info_isadmin;
    private $info_ismanager;
    
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
  
    public function getInfoIsadmin() {
        return $this->info_isadmin;
    }
  
    public function setInfoIsadmin($info_isadmin) {
        $this->info_isadmin = $info_isadmin;
    }
  
    public function getInfoIsmanager() {
        return $this->info_ismanager;
    }
  
    public function setInfoIsmanager($info_ismanager) {
        $this->info_ismanager = $info_ismanager;
    }
  
    public function isDenied() {
        return $this->cmdAuthorization->isDenied();
    }
    
}
