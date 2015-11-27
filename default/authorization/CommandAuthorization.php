<?php
/**
 * CommandAuthorization: define la clase de autorizaciones de los comandos
 *
 * @author Rafael Claver
 */
if (!defined('DOKU_INC')) die();
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_INC . 'lib/plugins/wikiiocmodel/');
if (!defined('DW_ACT_DENIED')) 	define('DW_ACT_DENIED', "denied" );

require_once (DOKU_INC . 'inc/common.php');
require_once (WIKI_IOC_MODEL . 'AbstractCommandAuthorization.php');

class CommandAuthorization extends AbstractCommandAuthorization {

    protected $modelWrapper;    //está pendiente separar completamente el DokuModelAdapter de la Autorización

    public function __construct($params) {
        parent::__construct();
        $this->command = $params;
        $this->modelWrapper = $this->command->getModelWrapper();
        
    }

    /* pendent de convertir a private quan no l'utilitzi ajax.php(duplicat) ni login_command */
    public function isUserAuthenticated() {
        global $_SERVER;
        return $_SERVER['REMOTE_USER'] ? TRUE : FALSE;
    }

    /* pendent de convertir a private quan no l'utilitzi abstract_command_class */
    public function isCommandAllowed() {
        $permissionFor = $this->command->getPermissionFor();
        $grup = $this->getUserGroup();
        $found = sizeof($permissionFor) == 0 || !is_array($grup);
        for($i = 0; !$found && $i < sizeof($grup); $i++) {
            $found = in_array($grup[$i], $permissionFor);
        }
        return $found;
    }

    /**
     * @return string[] hash amb els grups de l'usuari
    */
    private function getUserGroup() {
        global $INFO;
        return $INFO['userinfo']['grps'];
    }
    
    /**
     * Comproba si el token de seguretat està verificat o no fent servir una funció de la DokuWiki.
     * @return bool
    */
    public function isSecurityTokenVerified() {
        return checkSecurityToken();
    }

    /**
     * Si el valor de la variable global $ACT es 'denied' retorna false, en cualsevol altre cas retorna true.
     * @return bool
     */
    public function isDenied() {
	global $ACT;
	$this->modelWrapper->setParams('do', $ACT);
	return $ACT == DW_ACT_DENIED;
    }
}

