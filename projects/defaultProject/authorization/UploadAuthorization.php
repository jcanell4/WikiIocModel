<?php
/**
 * UploadAuthorization: Extensión clase Autorización para los comandos 
 * que precisan una autorización mínima de AUTH_UPLOAD
 * 
 * @author Rafael Claver
 */
if (!defined('DOKU_INC')) die();
define('WIKI_IOC_PROJECT', DOKU_INC . "lib/plugins/wikiiocmodel/projects/defaultProject/");

require_once (DOKU_INC . 'inc/auth.php');
require_once (WIKI_IOC_PROJECT . 'authorization/CommandAuthorization.php');

class UploadAuthorization extends CommandAuthorization {

    public function canRun() {
        if ( parent::canRun() && $this->permission->getInfoPerm() < AUTH_UPLOAD) {
            $this->errorAuth[self::ERROR_KEY] = TRUE;
            $this->errorAuth[self::EXCEPTION_KEY] = 'InsufficientPermissionToUploadMediaException';
        }
        return !$this->errorAuth[self::ERROR_KEY];        
    }
}
