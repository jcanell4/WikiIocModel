<?php
/**
 * UploadAuthorization: Extensión clase Autorización para los comandos
 * que precisan una autorización mínima de AUTH_UPLOAD
 * @author Rafael Claver
 */
if (!defined('DOKU_INC')) die();

class UploadAuthorization extends BasicCommandAuthorization {

    public function canRun() {
        if ( parent::canRun() && $this->permission->getInfoPerm() < AUTH_UPLOAD) {
            $this->errorAuth[self::ERROR_KEY] = TRUE;
            $this->errorAuth[self::EXCEPTION_KEY] = 'InsufficientPermissionToUploadMediaException';
        }
        return !$this->errorAuth[self::ERROR_KEY];
    }
}
