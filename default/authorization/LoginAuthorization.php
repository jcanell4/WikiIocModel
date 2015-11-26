<?php
/* 
 * LoginAuthorization: Extiende la clase Autorización para el comando 'login'
 * @author Rafael Claver
 */
if (!defined('DOKU_INC')) die();
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_INC . 'lib/plugins/wikiiocmodel/');
require_once (WIKI_IOC_MODEL . 'default/authorization/CommandAuthorization.php');

class LoginAuthorization extends CommandAuthorization {

    public function isAdminOrManager( $checkIsmanager = TRUE ) {
	global $INFO;
	return $INFO['isadmin'] || $checkIsmanager && $INFO['ismanager'];
    }
    
}
