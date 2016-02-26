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
    public function __construct($codeMessage, $code, $previous=NULL, $target=NULL) {
        $message = WikiIocLangManager::getLang($codeMessage);
        if ($message == NULL) {
            $message = $codeMessage;
        }
        if($target){
            $message = sprintf($message, $target);
        }
        parent::__construct($message, $code, $previous);
    }
}

class UnavailableMethodExecutionException extends Exception {
    public function __construct($method, $message="Unavailable method %s", 
                                                $code=9001, $previous=NULL) {
        parent::__construct($message, $code, $previous, $method);
    }
}

class AuthorizationNotTokenVerified extends WikiIocModelException {
    public function __construct($code=1020, $codeMessage='auth_TokenNotVerified', $previous=NULL) {
        parent::__construct($codeMessage, $code, $previous);
    }
}

class AuthorizationNotUserAuthenticated extends WikiIocModelException {
    public function __construct($code=1021, $codeMessage='auth_UserNotAuthenticated', $previous=NULL) {
        parent::__construct($codeMessage, $code, $previous);
    }
}
class AuthorizationNotCommandAllowed extends WikiIocModelException {
    public function __construct($code=1022, $codeMessage="auth_CommadNotAllowed", $previous=NULL) {
        parent::__construct($codeMessage, $code, $previous);
    }
}
