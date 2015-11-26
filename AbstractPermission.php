<?php
/**
 * Permission: define la clase de permisos
 *
 * @author Rafael Claver
 */
if (!defined('DOKU_INC') ) die();

abstract class AbstractPermission {
    
    protected $cmdAuthorization;        //objecte command
    protected $authenticatedUsersOnly;  //bool (de command_class)
    protected $isSecurityTokenVerified;
    protected $isUserAuthenticated;
    protected $isAuthorized;
    
    public function __construct($cmdAuthorization) {
        $this->cmdAuthorization = $cmdAuthorization;
    }
    
    public function getAuthenticatedUsersOnly() {
        return $this->authenticatedUsersOnly;
    }

    public function getIsSecurityTokenVerified() {
        return $this->isSecurityTokenVerified;
    }

    public function getIsUserAuthenticated() {
        return $this->isUserAuthenticated;
    }

    public function getIsAuthorized() {
        return $this->isAuthorized;
    }
  
    abstract function isDenied();
    
    public function setAuthenticatedUsersOnly($authenticatedUsersOnly) {
        $this->authenticatedUsersOnly = $authenticatedUsersOnly;
    }

    public function setIsSecurityTokenVerified($isSecurityTokenVerified) {
        $this->isSecurityTokenVerified = $isSecurityTokenVerified;
    }

    public function setIsUserAuthenticated($isUserAuthenticated) {
        $this->isUserAuthenticated = $isUserAuthenticated;
    }

    public function setIsAuthorized($isAuthorized) {
        $this->isAuthorized = $isAuthorized;
    }
  
}
