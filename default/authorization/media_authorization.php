<?php
/**
 * MediaCommandAuthorization: define la clase de autorizaciones de los media commands
 *
 * @author Rafael Claver
 */
if (!defined('DOKU_INC')) die();

require_once (DOKU_INC . 'lib/plugins/wikiiocmodel/default/authorization/CommandAuthorization.php');

class media_authorization extends CommandAuthorization {

    public function canRun($permission = NULL) {
        $ret = parent::canRun($permission);
        $ret = $ret && $this->permission->getInfoPerm() >= AUTH_READ;
        return $ret;
    }
}
