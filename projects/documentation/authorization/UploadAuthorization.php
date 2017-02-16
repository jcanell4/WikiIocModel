<?php
/**
 * UploadAuthorization: Extensión clase Autorización para los comandos 
 * que precisan una autorización mínima de AUTH_UPLOAD y que el usuario sea Autor o Responsable
 * 
 * @author Rafael Claver
 */
if (!defined('DOKU_INC')) die();
define('WIKI_IOC_PROJECTS', DOKU_INC . "lib/plugins/wikiiocmodel/projects/");

require_once (DOKU_INC . 'inc/auth.php');
require_once (WIKI_IOC_PROJECTS . 'documentation/authorization/CommandAuthorization.php');

class UploadAuthorization extends CommandAuthorization {

    public function canRun() {
        if (parent::canRun()) {
            if ($this->permission->getInfoPerm() < AUTH_UPLOAD) {
                $this->errorAuth['error'] = TRUE;
                $this->errorAuth['exception'] = 'InsufficientPermissionToUploadMediaException';
            }
            elseif (!$this->isResponsable() && !$this->isAuthor()) {
                $this->errorAuth['error'] = TRUE;
                $this->errorAuth['exception'] = 'UserNotAuthorizedException';
            }
        }
        return !$this->errorAuth['error'];        
    }
}
