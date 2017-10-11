<?php
/**
 * AbstractPermissionManager: Gestió de permisos. NO LO USA NADIE
 * @author Rafael Claver
 */
if (!defined('DOKU_INC') ) die();

abstract class AbstractPermissionPageForUserManager {

    abstract static function getPermissionPageForUser( $page, $user=NULL );
    abstract static function setPermissionPageForUser( $page, $user, $permis, $force=FALSE );
    abstract static function deletePermissionPageForUser( $page, $user );
    abstract static function updateMyOwnPagePermission( $permission );
}
