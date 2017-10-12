<?php
/**
 * Permission: la clase gestiona los permisos de usuario en este proyecto
 * @author Rafael Claver
 */
if (!defined('DOKU_INC') ) die();
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_INC . 'lib/plugins/wikiiocmodel/');

require_once (WIKI_IOC_MODEL . 'AbstractPermission.php');

class Permission extends AbstractPermission {

    const ROL_RESPONSABLE = "responsable";
    const ROL_AUTOR = "autor";

    private $info_perm;
    private $author;
    private $responsable;
    private $rol;
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

    public function getRol() {
        return $this->rol;
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

    public function setRol($rol) {
        $this->rol = $rol;
    }

    public function setResourceExist($resourceExist) {
        $this->resourceExist = $resourceExist;
    }
}
