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
    private $author;
    private $responsable;
    private $resourceExist;
    
    public function getInfoPerm() {
        return $this->info_perm;
    }
  
    public function getAuthor() {
        return $this->author;
    }

    public function getResponsable() {
        return $this->responsable;
    }

    public function getResourceExist() {
        return $this->resourceExist;
    }
  
    public function isReadOnly(){
        return ($this->getInfoPerm() < AUTH_EDIT);
    }
    
    public function setInfoPerm($info_perm) {
        $this->info_perm = $info_perm;
    }
  
    public function setAuthor($author) {
        $this->author = $author;
    }

    public function setResponsable($responsable) {
        $this->responsable = $responsable;
    }

    public function setResourceExist($resourceExist) {
        $this->resourceExist = $resourceExist;
    }
}
