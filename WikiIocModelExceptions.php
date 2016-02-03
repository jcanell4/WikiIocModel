<?php
/**
 * Define las clases de excepciones
 *
 * @author Rafael Claver
 */
if (!defined('DOKU_INC')) die();
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_INC . 'lib/plugins/wikiiocmodel/');

require_once (WIKI_IOC_MODEL . 'WikiIocLangManager.php');

abstract class WikiIocModelException extends Exception {
    public function __construct($message, $code, $previous=NULL) {
        parent::__construct($message, $code, $previous);
    }
}

class getException {
    
    public function __construct($exception=NULL) {
        $instance = NULL;
        if ($exception) {
            $instance = new $exception();
        }
        return $instance;
    }
}

class UnavailableMethodExecutionException extends Exception {
    public function __construct($method, $message="Unavailable method %s", 
                                                $code=9001, $previous=NULL) {
        parent::__construct(sprintf($message, $method), $code, $previous);
    }
}

class AuthorizationNotTokenVerified extends WikiIocModelException {
    public function __construct($code=1020, $codeMessage='auth_TokenNotVerified', $previous=NULL) {
        //$message="Token not verified";
        $message = WikiIocLangManager::getLang($codeMessage);
        if ($message == NULL) {
            $message = $codeMessage;
        }
        parent::__construct($message, $code, $previous);
    }
}

class AuthorizationNotUserAuthenticated extends WikiIocModelException {
    public function __construct($code=1021, $codeMessage='auth_UserNotAuthenticated', $previous=NULL) {
        //$message="User not authenticated";
        $message = WikiIocLangManager::getLang($codeMessage);
        if ($message == NULL) {
            $message = $codeMessage;
        }
        parent::__construct($message, $code, $previous);
    }
}
class AuthorizationNotCommandAllowed extends WikiIocModelException {
    public function __construct($code=1022, $codeMessage="auth_CommadNotAllowed", $previous=NULL) {
        //$message="Commad not allowed";
        $message = WikiIocLangManager::getLang($codeMessage);
        if ($message == NULL) {
            $message = $codeMessage;
        }
        parent::__construct($message, $code, $previous);
    }
}
