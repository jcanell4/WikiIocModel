<?php
/**
 * PermissionManager: Gestió de permisos
 *
 * @author Rafael Claver
 */
if (!defined('DOKU_INC') ) die();
require_once(DOKU_INC . 'lib/plugins/wikiiocmodel/AbstractPermissionPageForUserManager.php');

class PermissionPageForUserManager extends AbstractPermissionPageForUserManager {
    
    /**
     * @param $page y $user son obligatorios
     */
    public static function getPermissionPageForUser( $page, $user ) {
//	$acl_class = new admin_plugin_acl();
//	$acl_class->handle();
	$permis = auth_quickaclcheck( $page );  //hace referencia al usuario de la sesión actual
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
    public static function setPermissionPageForUser( $page, $user, $permis, $force = FALSE ) {
	$acl_class = new admin_plugin_acl();
	$acl_class->handle();
	$acl_class->who = $user;
	$permis_actual  = auth_quickaclcheck( $page );

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

}
