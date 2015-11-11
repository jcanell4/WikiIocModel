<?php
/**
 * CommandAuthorization: define la clase de autorizaciones de los comandos
 *
 * @author Rafael Claver
 */
if (!defined('DOKU_INC') ) die();
require_once (DOKU_INC . 'lib/plugins/wikiiocmodel/AbstractAuthorizationManager.php');

//namespace dokuwikibase {
    abstract class CommandAuthorization extends AbstractAuthorizationManager {
    
        public function __construct() {}

    }
//}
