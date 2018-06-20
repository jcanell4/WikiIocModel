<?php
/**
 * CommandAuthorization: define la clase de autorizaciones de los comandos
 * @author Rafael Claver
 */
if (!defined('DOKU_INC')) die();
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_INC . "lib/plugins/wikiiocmodel/");
require_once(WIKI_IOC_MODEL."authorization/BasicCommandAuthorization.php");

class CommandAuthorization extends BasicCommandAuthorization {

    public function __construct() {
        parent::__construct();
    }

}