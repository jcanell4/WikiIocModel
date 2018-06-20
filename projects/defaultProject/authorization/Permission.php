<?php
/**
 * Permission: define la clase de permisos para este proyecto
 * @author Rafael Claver
 */
if (!defined('DOKU_INC') ) die();

class Permission extends BasicPermission {

    private $overwriteRequired;
    private $isMyOwnNs;
    private $isEmptyText;

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

    public function getIsEmptyText() {
        return $this->isEmptyText;
    }

    public function setIsEmptyText($isEmptyText) {
        $this->isEmptyText = $isEmptyText;
    }
}
