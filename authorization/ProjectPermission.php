<?php
/**
 * Permission: la clase gestiona los permisos de usuario en este proyecto
 * @author Rafael Claver
 */
if (!defined('DOKU_INC') ) die();

class ProjectPermission extends BasicPermission {

    const ROL_RESPONSABLE = "responsable";
    const ROL_AUTOR = "autor";

    private $author;
    private $responsable;
    private $rol;

    public function getAuthor() {
        return $this->author;
    }

    public function getResponsable() {
        return $this->responsable;
    }

    public function getRol() {
        return $this->rol;
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

}
