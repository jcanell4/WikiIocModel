<?php
/**
 * AbstractAuthorizationManager: define la clase de autorizaciones de los comandos
 *
 * @author Rafael Claver
 */
if (!defined('DOKU_INC') ) die();

abstract class AbstractAuthorizationManager {
    
   const IOC_AUTH_OK = TRUE;
   const IOC_AUTH_FORBIDEN_ACCESS = FALSE;

   public function __construct() {}
   public abstract function canRun($permission = NULL);
}
