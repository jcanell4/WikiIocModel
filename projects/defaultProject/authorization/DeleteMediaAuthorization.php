<?php
/**
 * DeleteMediaAuthorization: Extensión clase Autorización para los comandos 
 * que precisan una autorización mínima de AUTH_DELETE
 * 
 * @author Rafael Claver
 */
if (!defined('DOKU_INC')) die();
define('WIKI_IOC_PROJECT', DOKU_INC . "lib/plugins/wikiiocmodel/projects/defaultProject/");

require_once (DOKU_INC . 'inc/auth.php');
require_once (WIKI_IOC_PROJECT . 'authorization/DeleteAuthorization.php');

class DeleteMediaAuthorization extends DeleteAuthorization {

    public function getPermissionException() {
        if ($this->permission->getOverwriteRequired() && $this->permission->getInfoPerm() < AUTH_DELETE) {
            $exception = 'InsufficientPermissionToDeleteResourceException';
        }
        return $exception;
    }
    
    public function setPermission($command) {
        parent::setPermission($command);
        $mediaExists = WikiIocInfoManager::getMediaInfo('mediaexists') && $command->getParams('do')==='media' && $command->getParams('image');
        $this->permission->setResourceExist($mediaExists);
        $overwrite = ($command->getParams('ow') === "1");
        $this->permission->setOverwriteRequired($overwrite);
    }
}
