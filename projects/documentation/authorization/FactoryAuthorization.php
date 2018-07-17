<?php
/**
 * FactoryAuthorization: carga las clases de autorización de los comandos del proyecto "documentation"
 * @author Rafael Claver
 */
if (!defined('DOKU_INC')) die();
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_INC . "lib/plugins/wikiiocmodel/");
require_once(WIKI_IOC_MODEL . "authorization/ProjectFactoryAuthorization.php");

class FactoryAuthorization extends ProjectFactoryAuthorization {

    const PROJECT_AUTH = WIKI_IOC_MODEL . "projects/documentation/authorization/";
    const DEFAULT_AUTH = WIKI_IOC_MODEL . "projects/defaultProject/authorization/";

    public function __construct($projectType=NULL) {
        parent::__construct( ($projectType!==NULL) ? self::PROJECT_AUTH : self::DEFAULT_AUTH);
    }

    public function setAuthorizationCfg() {
        if (empty($this->authCfg)) {
            parent::setAuthorizationCfg();
        }
        $aCfg = ['revertProject' => "editProject"];
        $this->authCfg = array_merge($this->authCfg, $aCfg);
    }

}
