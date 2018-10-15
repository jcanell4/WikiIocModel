<?php
/**
 * WriteAuthorization: Extensión clase Autorización para los comandos
 * que precisan una autorización mínima de AUTH_EDIT
 * @author Rafael Claver
 */
if (!defined('DOKU_INC')) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . "lib/plugins/");
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_PLUGIN . "wikiiocmodel/");
require_once (DOKU_INC . "inc/auth.php");
require_once (WIKI_IOC_MODEL . "projects/defaultProject/authorization/PageCommandAuthorization.php");

class WriteAuthorization extends PageCommandAuthorization {

    public function setPermission($command) {
        parent::setPermission($command);
        $this->permission->setIsEmptyText($command->isEmptyText());
    }

    public function getPermissionException() {
        if ($this->permission->getResourceExist() && $this->permission->getInfoPerm() < AUTH_EDIT) {
            $exception = 'InsufficientPermissionToWritePageException';
        }
        //eliminar todo el contenido de una página equivale a eliminar una página y precisa autorización AUTH_DELETE
        elseif ($this->permission->getIsEmptyText() && $this->permission->getInfoPerm() < AUTH_DELETE) {
            $exception = 'InsufficientPermissionToDeletePageException';
        }
        return $exception;
    }
}
