<?php
/**
 * PermissionManager: Gestió de permisos
 *
 * @author Rafael Claver
 */
if (!defined('DOKU_INC') ) die();

class PermissionManager extends AbstractPermissionManager{
    
    /**
     * @param $page y $user son obligatorios
     */
    public function getPermission( $page, $user ) {
	$acl_class = new admin_plugin_acl();
	$acl_class->handle();
	$acl_class->who = $user;
	$permis         = auth_quickaclcheck( $page );

	/* este bucle obtiene el mismo resultado que auth_quickaclcheck()
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
    public function setPermission( $page, $user, $permis, $force = FALSE ) {
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

    public function deletePermission( $page, $user ) {
	$acl_class = new admin_plugin_acl();
	//$acl_class->handle();
	//$acl_class->who = $user;
	if ( $page && $user ) {
            $ret = $acl_class->_acl_del( $page, $user );
	}
	return $ret;
    }

}
