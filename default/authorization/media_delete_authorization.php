<?php
/**
 * MediaCommandAuthorization: define la clase de autorizaciones de los media commands
 *
 * @author Rafael Claver
 */
if (!defined('DOKU_INC')) die();

require_once (DOKU_INC . 'lib/plugins/wikiiocmodel/default/authorization/media_authorization.php');

class media_delete_authorization extends media_authorization {

    public function canRun($permission = NULL) {
        $ret = parent::canRun($permission);
        $ret = $ret && $this->permission->getInfoPerm() >= AUTH_DELETE;
        return $ret;
    }
}
