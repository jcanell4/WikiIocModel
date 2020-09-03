<?php
/**
 * FactoryAuthorization: carga las clases de autorización de los comandos de 'defaultProject'
 * @author Rafael Claver
 */
if (!defined('DOKU_INC')) die();

class FactoryAuthorization extends AbstractFactoryAuthorization {

    const PROJECT_AUTH = __DIR__ . "/";

    public function __construct() {
        parent::__construct(self::PROJECT_AUTH);
    }

}
