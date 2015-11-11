<?php
/**
 * CommandAuthorization: define la clase de autorizaciones de los comandos
 *
 * @author Rafael Claver
 */
if (!defined('DOKU_INC') ) die();
require_once (DOKU_INC . 'lib/plugins/wikiiocmodel/AbstractAuthorizationManager.php');

class CommandAuthorization extends AbstractAuthorizationManager {
    private $permission = array();
    private $params = array();
    private $permissionFor = array();
    private $authenticatedUsersOnly = TRUE;
    
    public function __construct($aParams) {
        parent::__construct();
        $this->authenticatedUsersOnly = $aParams['authenticatedUsersOnly'];
        $this->params = $aParams['params'];
        $this->permission = $aParams['permission'];
        $this->permissionFor = $aParams['permissionFor'];
    }

    public function canRun() {
        $ret = NULL;
        if(!$this->authenticatedUsersOnly
            || $dokuModel->isSecurityTokenVerified()
            && $dokuModel->isUserAuthenticated()
            && $dokuModel->isAuthorized()
        ) {
            $ret = $dokuModel->getResponse();

            if($dokuModel->modelWrapper->isDenied()) {
                $dokuModel->error        = 403;
                $dokuModel->errorMessage = "permission denied";
            }
        } else {
            //TODO[xavi] Per poder fer proves deshabilitem la comprovació
            $dokuModel->error        = 403;
            $dokuModel->errorMessage = "permission denied";

        }
        if($dokuModel->error && $dokuModel->throwsException) {
            throw new Exception($dokuModel->errorMessage);
        }
        return $ret;
    }

}

