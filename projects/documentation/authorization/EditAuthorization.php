<?php
/**
 * ReadAuthorization: Extensión clase Autorización para los comandos 
 * que precisan una autorización mínima de AUTH_READ y que el usuario sea Autor o Responsable
 * 
 * @author Rafael Claver
 */
if (!defined('DOKU_INC')) die();
define('WIKI_IOC_PROJECT', DOKU_INC . "lib/plugins/wikiiocmodel/projects/documentation/");

require_once (DOKU_INC . 'inc/auth.php');
require_once (WIKI_IOC_PROJECT . 'authorization/PageCommandAuthorization.php');

class EditAuthorization extends PageCommandAuthorization {
    
    public function getPermissionException() {
        if ($this->permission->getPageExist() && $this->permission->getInfoPerm() < AUTH_READ) {
            $exception = 'InsufficientPermissionToEditPageException';
        }elseif (!$this->isResponsable() && !$this->isAuthor()) {
            $exception = 'UserNotAuthorizedException';
        }
        return $exception;
    }

}
