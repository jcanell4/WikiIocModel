<?php
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');

require_once(DOKU_PLUGIN . "wikiiocmodel/utility/ValidateWithPermission.php");

class ValidationByRoles extends ValidateWithPermission {

    function validate($data)
    {
        $role = $this->permission->getRol();
        $ret = TRUE;

        if(is_array($role)){
            for ($i=0; $i<count($role) && $ret; $i++){
                $ret = !in_array($role[$i], $data);
            }
        }else if(strpos($role, ',') !== false){
            $arole = explode(',', $role);
            for ($i=0; $i<count($arole) && $ret; $i++){
                $ret = !in_array($arole[$i], $data);
            }
        }else{
            $ret = !in_array($role, $data);
        }
        return $ret;
    }
}