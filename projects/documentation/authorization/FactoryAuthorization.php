<?php
/**
 * FactoryAuthorization crea los objetos de autorización de los comandos del proyecto "documentation"
 * @author Rafael Claver
 */
if (!defined('DOKU_INC')) die();

class FactoryAuthorization {
    /* SINGLETON CLASS */
    const PROJECT_AUTH = DOKU_INC . "lib/plugins/wikiiocmodel/projects/documentation/authorization/";
    
    private function __construct() {}

    public static function Instance(){
        static $inst = null;
        if ($inst === null) {
            $inst = new FactoryAuthorization();
        }
        return $inst;
    }

    public function createAuthorizationManager($str_cmd) {
        
        $fileAuthorization = $this->readFileIn2CaseFormat($str_cmd, 'authorization');
        if ($fileAuthorization === NULL) {
            static $_AuthorizationCfg = array();
            require_once(self::PROJECT_AUTH . 'FactoryAuthorizationCfg.php');
            $fileAuthorization = $this->readFileIn2CaseFormat($_AuthorizationCfg[$str_cmd], 'authorization');
            if ($fileAuthorization === NULL) {
                $fileAuthorization = $this->readFileIn2CaseFormat($_AuthorizationCfg['_default'], 'authorization');
            }
        }
        $authorization = new $fileAuthorization();
        return $authorization;
    }
    
    private function readFileIn2CaseFormat($str_cmd, $part2) {
        /* Carga el archivo correspondiente al comando,
         * buscándolo por el nombre en formato convencional y en formato CamelCase
         */
        $name = $this->nameCaseFormat($str_cmd, $part2,'_');
        $ret = NULL;
        $authFile = self::PROJECT_AUTH . "$name.php";
        if (!file_exists($authFile)) {
            $name = $this->nameCaseFormat($str_cmd, $part2,'camel');
            $authFile = self::PROJECT_AUTH . "$name.php";
        }
        if (!file_exists($authFile)) {
            $name = $this->nameCaseFormat($str_cmd, $part2,'camel2');
            $authFile = self::PROJECT_AUTH . "$name.php";
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
            $ret = strtoupper(substr($part1, 0, 1)) . substr($part1, 1) 
                 . strtoupper(substr($part2, 0, 1)) . substr($part2, 1);
        }elseif ($case === 'camel2') {
            $ret = strtoupper(substr($part1, 0, 1)) . strtolower(substr($part1, 1)) 
                 . strtoupper(substr($part2, 0, 1)) . strtolower(substr($part2, 1));
        }else {
            $ret = $part1 . '_' . $part2;
        }
        return $ret;
    }
}
