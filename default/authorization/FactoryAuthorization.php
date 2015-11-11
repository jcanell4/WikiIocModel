<?php
/**
 * FactoryAuthorization crea los objetos de autorización de los comandos
 *
 * @author Rafael Claver
 */
if (!defined('DOKU_INC')) die();
if (!defined('DOKU_IOCMODELAUTH'))
    define('DOKU_IOCMODELAUTH', DOKU_INC . 'lib/plugins/wikiiocmodel/default/authorization/');
require_once(DOKU_IOCMODELAUTH . 'CommandAuthorization.php');

class FactoryAuthorization {
    /* SINGLETON CLASS */
    private function __construct() {}

    public static function Instance(){
        static $inst = null;
        if ($inst === null) {
            $inst = new FactoryAuthorization();
        }
        return $inst;
    }

    public function CreateAuthorizationManager($str_command) {
        
        $str_authorization = $this->readFileIn2CaseFormat($str_command, 'authorization');
        if ($str_authorization !== NULL) {
            $authorization = new $str_authorization();
        }else {
            $authorization = NULL;
            //throw new AuthorizationCommandNotFound();
        }
        return $authorization;
    }
    
    private function readFileIn2CaseFormat($str_command, $part2) {
        /* Carga el archivo correspondiente al comando.
         * buscando por el nombre en formato convencional y en formato CamelCase
         */
        $name = $this->NameCaseFormat($str_command, $part2,'');
        $ret = NULL;
        $authFile = DOKU_IOCMODELAUTH . $name . '.php';
        if (!file_exists($authFile)) {
            $name = $this->NameCaseFormat($str_command, $part2,'camel');
            $authFile = DOKU_IOCMODELAUTH . $name . '.php';
        }
        if (file_exists($authFile)) {
            require_once($authFile);
            $ret = $name;
        }
        return $ret;
    }
    
    private function NameCaseFormat($part1, $part2, $case) {
        /* Devuelve un nombre compuesto en el formato solicitado:
         * 'guion_bajo' o CamelCase
         */
        if ($case === 'camel') {
            $ret = strtoupper(substr($part1, 0, 1)) . strtolower(substr($part1, 1)) 
                 . strtoupper(substr($part2, 0, 1)) . strtolower(substr($part2, 1));
        }else {
            $ret = $part1 . '_' . $part2;
        }
        return $ret;
    }
}