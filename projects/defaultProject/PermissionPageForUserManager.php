<?php
/**
 * PermissionManager: Gestió de permisos
 *
 * @author Rafael Claver
 */
if (!defined('DOKU_INC') ) die();
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_INC . 'lib/plugins/wikiiocmodel/');

require_once(WIKI_IOC_MODEL . 'AbstractPermissionPageForUserManager.php');
require_once(WIKI_IOC_MODEL . 'WikiIocInfoManager.php');

class PermissionPageForUserManager extends AbstractPermissionPageForUserManager {
    
    /**
     * @param $page y $user son obligatorios
     */
    public static function getPermissionPageForUser( $page, $user=NULL ) {
        global $USERINFO;
        if (!$user) $user = $_SERVER['REMOTE_USER'];
//	$permis = auth_quickaclcheck( $page );  //hace referencia al usuario de la sesión actual
        $permis = auth_aclcheck($page, $user, $USERINFO['grps']);
	/* este bucle obtiene el mismo resultado que auth_quickaclcheck()
	$acl_class = new admin_plugin_acl();
	$acl_class->handle();
	$permis = NULL;
	$sub_page = $page;
	while (!$permis && $sub_page) {
		$acl_class->ns = $sub_page;
		$permis = $acl_class->_get_exact_perm();
		$sub_page = substr($sub_page, 0, strrpos($sub_page, ':'));
	}
	*/
	return $permis;
    }

    /**
     * @param bool $force : true indica que s'ha d'establir estrictament el permís 
     */
    public static function setPermissionPageForUser( $page, $user, $permis, $force=FALSE ) {
        global $USERINFO;
	$acl_class = new admin_plugin_acl();
	$acl_class->handle();
	//$acl_class->who = $user;
        $permis_actual  = auth_aclcheck($page, $user, $USERINFO['grps']);

        if ( $force || $permis > $permis_actual ) {
            $ret = $acl_class->_acl_add( $page, $user, $permis );
            if ( $ret ) {
		if ( strpos( $page, '*' ) === FALSE ) {
                    if ( $permis > AUTH_EDIT ) {
                        $permis_actual = AUTH_EDIT;
                    }
		} else {
                    $permis_actual = $permis;
		}
            }
	}

	return $permis_actual;
    }

    public static function deletePermissionPageForUser( $page, $user ) {
	$acl_class = new admin_plugin_acl();
	//$acl_class->handle();
	//$acl_class->who = $user;
	if ( $page && $user ) {
            $ret = $acl_class->_acl_del( $page, $user );
	}
	return $ret;
    }

    public static function updatePermission($permission) {

        if ($permission->getIsMyOwnNs()) {  //si es tracta de la pròpia pàgina de l'usuari ...
            $acl_class = new admin_plugin_acl();
            $acl_class->handle();

            $user = WikiIocInfoManager::getInfo('client');
            $page = $permission->getIdPage();
            $ns = substr($page, 0, strrpos($page, ":")) . ':*';
            
            if ( !$acl_class->acl[$ns][$user] ) {  
                // La pàgina de l'usuari no existeix al fitxer de permissos
                $ret = self::setPermissionPageForUser($ns, $user, AUTH_DELETE, TRUE);
                WikiIocInfoManager::setInfo('perm', $ret);
            }
        }
        return $ret;
    }

}