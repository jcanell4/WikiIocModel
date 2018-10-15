<?php
/**
 * FactoryAuthorization: carga las clases de autorización de los comandos del defaultProject
 * @author Rafael Claver
 */
if (!defined('DOKU_INC')) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . "lib/plugins/");
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_PLUGIN . "wikiiocmodel/");
require_once(WIKI_IOC_MODEL . "authorization/AbstractFactoryAuthorization.php");

class FactoryAuthorization extends AbstractFactoryAuthorization {

    const PROJECT_AUTH = WIKI_IOC_MODEL . "projects/defaultProject/authorization/";

    public function __construct($projectType=NULL) {
        if ($projectType===NULL) $projectType = "defaultProject";
        parent::__construct(self::PROJECT_AUTH);
    }

}
