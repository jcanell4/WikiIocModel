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

class HttpErrorCodeException extends WikiIocModelException
{
    public function __construct($code, $message = "", $previous = NULL) {
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
    public function __construct($codeMessage='auth_TokenNotVerified', $code=1020, $previous=NULL) {
        parent::__construct($codeMessage, $code, $previous);
    }
}

class AuthorizationNotUserAuthenticated extends WikiIocModelException {
    public function __construct($codeMessage='auth_UserNotAuthenticated', $code=1021, $previous=NULL) {
        parent::__construct($codeMessage, $code, $previous);
    }
}
class AuthorizationNotCommandAllowed extends WikiIocModelException {
    public function __construct($codeMessage="auth_CommadNotAllowed", $code=1022, $previous=NULL) {
        parent::__construct($codeMessage, $code, $previous);
    }
}
class FileIsLockedException extends WikiIocModelException {
    public function __construct($id="", $codeMessage="lockedByAlert", $code=1023, $previous=NULL) {
        parent::__construct($codeMessage, $code, $previous, $id);
    }
}

class DraftNotFoundException extends WikiIocModelException{
    public function __construct($id="", $codeMessage = 'DraftNotFoundException', $code = 1024, $previous = NULL)
    {
        parent::__construct($codeMessage, $code, $previous, $id);
    }
}

class UnexpectedLockCodeException extends WikiIocModelException{
    public function __construct($id="", $codeMessage = 'UnexpectedLockCode', $code = 1025, $previous = NULL)
    {
        parent::__construct($codeMessage, $code, $previous, $id);
    }
}

