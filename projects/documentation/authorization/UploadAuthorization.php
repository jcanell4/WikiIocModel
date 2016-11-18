<?php
/**
 * UploadAuthorization: Extensión clase Autorización para los comandos 
 * que precisan una autorización mínima de AUTH_UPLOAD
 * 
 * @author Rafael Claver
 */
if (!defined('DOKU_INC')) die();
if (!defined('DOKU_IOC_MODEL')) define('DOKU_IOC_MODEL', DOKU_INC . "lib/plugins/wikiiocmodel/projects/defaultProject/");

require_once (DOKU_INC . 'inc/auth.php');
require_once (DOKU_IOC_MODEL . 'authorization/CommandAuthorization.php');

class UploadAuthorization extends CommandAuthorization {

    public function canRun() {
        if ( parent::canRun() && $this->permission->getInfoPerm() < AUTH_UPLOAD) {
            $this->errorAuth['error'] = TRUE;
            $this->errorAuth['exception'] = 'InsufficientPermissionToUploadMediaException';
        }
        return !$this->errorAuth['error'];        
    }
}
