<?php

if (!defined("DOKU_INC")) {
    die();
}
if (!defined('DOKU_PLUGIN')) {
    define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
}

require_once DOKU_PLUGIN . "wikiiocmodel/WikiIocModelExceptions.php";
require_once DOKU_PLUGIN . "wikiiocmodel/authorization/PagePermissionManager.php";

/**
 * Description of UserListAction
 *
 * @author josep
 */
class UserListAction  extends AbstractWikiAction{
    const OF_A_PROJECT = "ofAProject";
    const BY_PAGE_PERMSION = "byPagePermision";
    const BY_NAME = "byName";
    
    public function get($paramsArr = array()) {
        $ret = null;
        switch ($paramsArr[PageKeys::KEY_DO]){
            case self::OF_A_PROJECT:
                //JOSEP: TODO. Falta fer una funció que retorno tots els usuaris d'un projecte
                break;
            case self::BY_PAGE_PERMSION:
                $ret = PagePermissionManager::getListUsersPagePermission($paramsArr[PageKeys::KEY_ID], AUTH_EDIT);
                break;
            case self::BY_NAME:
                $ret = PagePermissionManager::getUserList($paramsArr[PageKeys::KEY_FILTER]);
                break;
            default :
                //error;
                throw new IncorrectParametersException();
        }
        return $ret;
    }
}
