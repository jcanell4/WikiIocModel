<?php
/**
 * FactoryAuthorization: carga las clases de autorización de los comandos del proyecto "ptfct"
 * @author Rafael Claver
 */
if (!defined('DOKU_INC')) die();

class FactoryAuthorization extends ProjectFactoryAuthorization {

    const PROJECT_AUTH = __DIR__ . "/";

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
