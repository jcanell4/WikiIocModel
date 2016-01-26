<?php
/**
 * AbstractPermissionManager: Gestió de permisos
 *
 * @author Rafael Claver
 */
if (!defined('DOKU_INC') ) die();

abstract class AbstractPermissionManager {
    
    protected $efectivePermission;
    
    /**
     * @param $page y $user son obligatorios
     */
    abstract function getPermission( $page, $user );

    /**
     * @param bool $force : true indica que s'ha d'establir estrictament el permís 
     */
    abstract function setPermission( $page, $user, $permis, $force = FALSE );

    abstract function deletePermission( $page, $user );

}
