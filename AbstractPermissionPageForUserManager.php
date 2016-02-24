<?php
/**
 * AbstractPermissionManager: Gestió de permisos
 *
 * @author Rafael Claver
 */
if (!defined('DOKU_INC') ) die();

abstract class AbstractPermissionPageForUserManager {
    
    protected $efectivePermission;
    
    /**
     * @param $page y $user son obligatorios
     */
    abstract static function getPermissionPageForUser( $page, $user );

    /**
     * @param bool $force : true indica que s'ha d'establir estrictament el permís 
     */
    abstract static function setPermissionPageForUser( $page, $user, $permis, $force = FALSE );

    abstract static function deletePermissionPageForUser( $page, $user );

}
