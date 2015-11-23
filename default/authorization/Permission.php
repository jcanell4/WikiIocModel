<?php
/**
 * Permission: define la clase de permisos
 *
 * @author Rafael Claver
 */
if (!defined('DOKU_INC') ) die();


class Permission {
    
    private $modelWrapper;
    private $authenticatedUsersOnly;    //bool (de command_class)
    private $isSecurityTokenVerified;
    private $isUserAuthenticated;
    private $isAuthorized;
    private $isDenied;
    
    public function __construct($modelWrapper) {
        $this->modelWrapper = $modelWrapper;
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
  
    public function isDenied() {
        return $this->modelWrapper->isDenied();
    }
    
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
