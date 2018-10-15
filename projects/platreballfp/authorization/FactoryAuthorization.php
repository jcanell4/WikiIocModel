<?php
/**
 * FactoryAuthorization: carga las clases de autorización de los comandos del proyecto "platreballfp"
 * @author Rafael Claver
 */
if (!defined('DOKU_INC')) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC."lib/plugins/");
require_once(DOKU_PLUGIN."wikiiocmodel/authorization/ProjectFactoryAuthorization.php");

class FactoryAuthorization extends ProjectFactoryAuthorization {

    const PROJECT_AUTH = DOKU_PLUGIN."wikiiocmodel/projects/platreballfp/authorization/";

    public function __construct() {
        parent::__construct(self::PROJECT_AUTH);
    }

    public function setAuthorizationCfg() {
        if (empty($this->authCfg)) {
            parent::setAuthorizationCfg();
        }
        $aCfg = ['new_documentProject' => "notAllowedCommand",
                 'new_folderProject'   => "notAllowedCommand"
                ];
        $this->authCfg = array_merge($this->authCfg, $aCfg);
    }

}
