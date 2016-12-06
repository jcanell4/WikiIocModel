<?php
/**
 * AdminAuthorization: Extensión clase Autorización para los comandos 
 * que precisan una autorización mínima de AUTH_ADMIN
 * 
 * @author Rafael Claver
 */
if (!defined('DOKU_INC')) die();
define('WIKI_IOC_PROJECT', DOKU_INC . "lib/plugins/wikiiocmodel/projects/documentation/");

require_once (DOKU_INC . 'inc/auth.php');
require_once (WIKI_IOC_PROJECT . 'authorization/CommandAuthorization.php');

class AdminAuthorization extends CommandAuthorization {

    public function canRun() {
        if ( parent::canRun() && $this->permission->getInfoPerm() < AUTH_ADMIN) {
            $this->errorAuth['error'] = TRUE;
            $this->errorAuth['exception'] = 'AuthorizationNotCommandAllowed';
            $this->errorAuth['extra_param'] = $this->permission->getIdPage();
        }
        return !$this->errorAuth['error'];
    }

}
