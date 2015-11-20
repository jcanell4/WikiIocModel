<?php
/**
 * AbstractAuthorizationManager: define la clase de autorizaciones de los comandos
 *
 * @author Rafael Claver
 */
if (!defined('DOKU_INC') ) die();

//[TO DO josep]: Reanomenar a AbstractAuthorization
abstract class AbstractAuthorizationManager {
    
   const IOC_AUTH_OK = TRUE;
   const IOC_AUTH_FORBIDEN_ACCESS = FALSE;

   public function __construct() {}

   abstract protected function canRun();
}
