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
	$acl_class->who = $user;
//	$permis_actual  = auth_quickaclcheck( $page );
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

    public function setUserPagePermission($page, $user, $acl_level) {
        global $conf;
        include_once(WIKI_IOC_MODEL . 'conf/default.php');
        $namespace = substr($page, 0, strrpos($page, ":"));
        $userpage_ns = ":" . $namespace;
        $user_name = substr($userpage_ns, strrpos($userpage_ns, ":") + 1);
        $ret = FALSE;
        if (WikiIocInfoManager::getInfo('isadmin')
            || WikiIocInfoManager::getInfo('ismanager')
            || (WikiIocInfoManager::getInfo('namespace') == $namespace
                && $user_name == $user
                && $conf['userpage_allowed'] === 1
                && ($userpage_ns == $conf['userpage_ns'] . $user ||
                    $userpage_ns == $conf['userpage_discuss_ns'] . $user)
            )
        ) {
            $ret = $this->setPermissionPageForUser($page, $user, $acl_level, TRUE);
            WikiIocInfoManager::setInfo('perm', $ret);
        }
        return $ret;
    }

    public static function updatePermission($permission, $user) {
        /*Comprovar si l'usuari no té permisos (No hi ha entrada al fitxer ) i és MyOwnPage.
                Si => actualitzar el fitxer de permisos amb ns del usuari
         * SI NO ACONSEGUEIX EL PERMIS ES LLANÇA UNA EXCEPCIÓ
         */
    }

}
