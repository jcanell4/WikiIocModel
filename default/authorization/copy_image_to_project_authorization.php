<?php
/**
 * copy_image_to_project_authorization: Valida el nivel de autorización de este comando
 *
 * @author Rafael Claver
 */
if (!defined('DOKU_INC')) die();
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_INC . 'lib/plugins/wikiiocmodel/');
require_once (DOKU_INC . 'inc/auth.php');
require_once (WIKI_IOC_MODEL . 'default/authorization/CommandAuthorization.php');

class copy_image_to_project_authorization extends CommandAuthorization {

    public function canRun($permission = NULL) {
        $ret = parent::canRun($permission);
        $ret = $ret && $this->permission->getInfoPerm() >= AUTH_UPLOAD;
        return $ret;
    }
}
