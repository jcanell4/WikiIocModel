<?php
/**
 * PermissionManager: Gestión de permisos, de los usuarios sobre las páginas, en el fichero acl.auth.php
 * @author Rafael Claver
 */
if (!defined('DOKU_INC') ) die();
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_INC . 'lib/plugins/wikiiocmodel/');
require_once(WIKI_IOC_MODEL . 'WikiIocInfoManager.php');

class PermissionPageForUserManager {

    /**
     * @return int : valor del permiso actual del usuario $user sobre la página $page
     */
    public static function getPermissionPageForUser( $page, $user=NULL ) {
        global $auth;
        if (!$user) $user = $_SERVER['REMOTE_USER'];
	$info = $auth->getUserData($user);
        $permis = auth_aclcheck($page, $user, $info['grps']);
	/* $permis = auth_quickaclcheck( $page );  //hace referencia al usuario de la sesión actual
	// este bucle obtiene el mismo resultado que auth_quickaclcheck()
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
     * @return int : valor del permiso actualmente establecido
     */
    public static function setPermissionPageForUser( $page, $user, $permis, $force=FALSE ) {
        global $auth;
	$acl_class = new admin_plugin_acl();
	$acl_class->handle();
	$info = $auth->getUserData($user);
        $permis_actual = auth_aclcheck($page, $user, $info['grps']);

        if ($force || $permis > $permis_actual) {
            $ret = $acl_class->_acl_add($page, $user, $permis);
            if ($ret) {
		if (strpos($page, '*') === FALSE) {
                    if ($permis > AUTH_EDIT) {
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
	if ( $page && $user ) {
            $acl_class = new admin_plugin_acl();
            $ret = $acl_class->_acl_del( $page, $user );
	}
	return $ret;
    }

    public static function updateMyOwnPagePermission($permission) {

        if ($permission->getIsMyOwnNs()) {  //si es tracta de la pròpia pàgina de l'usuari ...
            $user = WikiIocInfoManager::getInfo('client');
            $page = $permission->getIdPage();
            $ns = substr($page, 0, strrpos($page, ":")) . ':*';
//            if ( !self::existPageUser($ns, $user) ) {
//                // La pàgina de l'usuari no existeix en el fitxer de permissos
//                $ret = self::setPermissionPageForUser($ns, $user, AUTH_DELETE, TRUE);
//                WikiIocInfoManager::setInfo('perm', $ret);
//            }
            $ret = self::updatePagePermission($ns, $user, AUTH_DELETE);
            if ($ret) WikiIocInfoManager::setInfo('perm', $ret);
        }
        return $ret;
    }

    public static function updatePagePermission($page, $user, $permission, $force=TRUE) {

        $level = self::levelPageUser($page, $user);
        $exist = ($level) ? TRUE : FALSE;

        if ($exist && $level < $permission) {
            //No consta el permís suficient de la pàgina per a aquest usuari
            $ret = self::deletePermissionPageForUser($page, $user);
        }
        if (!$exist || $ret) {
            //No existeix el registre, o bé, no consta el permís suficient de la pàgina per a aquest usuari
            $ret = self::setPermissionPageForUser($page, $user, $permission, $force);
        }
        return ($ret) ? $ret : TRUE;
    }

    /**
     * Mira si en el fichero acl.auth.php existe un permiso sobre la página para el usuario
     * @return boolean : retorna true si ya existe una entrada para $page $user
     */
    public static function existPageUser($page, $user) {
        $ret = self::levelPageUser($page, $user);
        return ($ret) ? TRUE : FALSE;
    }

    /**
     * Obtiene del fichero acl.auth.php el nivel de permiso sobre la página para el usuario
     * @param string $page : wikiRuta de la página
     * @param string $user : nombre del usuario
     * @return int : retorna el nivel de permiso correspondiente a la entrada $page $user
     */
    public static function levelPageUser($page, $user) {
        $acl_class = new admin_plugin_acl();
        $acl_class->handle();
        return $acl_class->acl[$page][$user];
    }

}