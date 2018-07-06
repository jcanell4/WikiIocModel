<?php
/**
 * FactoryAuthorization: carga las clases de autorización de los comandos del proyecto "taulasubs"
 * @author Rafael Claver
 */
if (!defined('DOKU_INC')) die();
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_INC . "lib/plugins/wikiiocmodel/");
require_once(WIKI_IOC_MODEL . "authorization/ProjectFactoryAuthorization.php");

class FactoryAuthorization extends ProjectFactoryAuthorization {

    const PROJECT_AUTH = WIKI_IOC_MODEL . "projects/taulasubs/authorization/";

    public function __construct($projectType=NULL) {
        if ($projectType===NULL) $projectType = "defaultProject";
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
