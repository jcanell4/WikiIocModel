<?php
/**
 * AbstractPermission: define la clase abstracta de permisos
 *
 * @author Rafael Claver
 */
if (!defined('DOKU_INC') ) die();

abstract class AbstractPermission {
    
    protected $cmdAuthorization;        //objecte command
    protected $authenticatedUsersOnly;  //bool (de command_class)
    protected $isSecurityTokenVerified;
    protected $isUserAuthenticated;
    protected $isCommandAllowed;
    
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

    public function getIsCommandAllowed() {
        return $this->isCommandAllowed;
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

    public function setIsCommandAllowed($isCommandAllowed) {
        $this->isCommandAllowed = $isCommandAllowed;
    }
  
}
