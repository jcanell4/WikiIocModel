<?php
/**
 * FactoryAuthorization crea los objetos de autorización de los comandos
 *
 * @author Rafael Claver
 */
if (!defined('DOKU_INC')) die();
define('WIKI_IOC_PROJECT_AUTH', DOKU_INC . "lib/plugins/wikiiocmodel/projects/documentation/authorization/");

require_once(WIKI_IOC_PROJECT_AUTH . 'CommandAuthorization.php');

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

    public function createAuthorizationManager($str_cmd, $params) {
        
        $fileAuthorization = $this->readFileIn2CaseFormat($str_cmd, 'authorization');
        if ($fileAuthorization === NULL) {
            require_once(WIKI_IOC_PROJECT_AUTH . 'FactoryAuthorizationCfg.php');
            $fileAuthorization = $this->readFileIn2CaseFormat($_AuthorizationCfg[$str_cmd], 'authorization');
            if ($fileAuthorization === NULL) {
                $fileAuthorization = $this->readFileIn2CaseFormat($_AuthorizationCfg['_default'], 'authorization');
            }
        }
        $authorization = new $fileAuthorization($params);
        return $authorization;
    }
    
    private function readFileIn2CaseFormat($str_cmd, $part2) {
        /* Carga el archivo correspondiente al comando.
         * buscando por el nombre en formato convencional y en formato CamelCase
         */
        $name = $this->nameCaseFormat($str_cmd, $part2,'');
        $ret = NULL;
        $authFile = WIKI_IOC_PROJECT_AUTH . "$name.php";
        if (!file_exists($authFile)) {
            $name = $this->nameCaseFormat($str_cmd, $part2,'camel');
            $authFile = WIKI_IOC_PROJECT_AUTH . "$name.php";
        }
        if (file_exists($authFile)) {
            require_once($authFile);
            $ret = $name;
        }
        return $ret;
    }
    
    private function nameCaseFormat($part1, $part2, $case) {
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
