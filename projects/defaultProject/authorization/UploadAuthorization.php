<?php
/**
 * UploadAuthorization: Extensión clase Autorización para los comandos
 * que precisan una autorización mínima de AUTH_UPLOAD
 * @author Rafael Claver
 */
if (!defined('DOKU_INC')) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . "lib/plugins/");
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_PLUGIN . "wikiiocmodel/");
require_once (DOKU_INC . "inc/auth.php");
require_once (WIKI_IOC_MODEL . "projects/defaultProject/authorization/CommandAuthorization.php");

class UploadAuthorization extends CommandAuthorization {

    public function canRun() {
        if ( parent::canRun() && $this->permission->getInfoPerm() < AUTH_UPLOAD) {
            $this->errorAuth[self::ERROR_KEY] = TRUE;
            $this->errorAuth[self::EXCEPTION_KEY] = 'InsufficientPermissionToUploadMediaException';
        }
        return !$this->errorAuth[self::ERROR_KEY];
    }
}
