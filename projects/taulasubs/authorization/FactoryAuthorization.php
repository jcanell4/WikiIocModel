<?php
/**
 * FactoryAuthorization: carga las clases de autorización de los comandos del proyecto "taulasubs"
 * @author Rafael Claver
 */
if (!defined('DOKU_INC')) die();
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_INC . "lib/plugins/wikiiocmodel/");
require_once(WIKI_IOC_MODEL . "authorization/AbstractFactoryAuthorization.php");

class FactoryAuthorization extends AbstractFactoryAuthorization {

    const PROJECT_AUTH = WIKI_IOC_MODEL . "projects/taulasubs/authorization/";

    public function __construct() {
        parent::__construct(self::PROJECT_AUTH);
    }

}
