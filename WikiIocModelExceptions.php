<?php
/**
 * Define las clases de excepciones
 *
 * @author Rafael Claver
 */

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
    public function __construct($code=1020, $message="Token not verified", $previous=NULL) {
        parent::__construct($message, $code, $previous);
    }
}

class AuthorizationNotUserAuthenticated extends WikiIocModelException {
    public function __construct($code=1021, $message="Token not verified", $previous=NULL) {
        parent::__construct($message, $code, $previous);
    }
}
class AuthorizationNotCommandAllowed extends WikiIocModelException {
    public function __construct($code=1022, $message="Token not verified", $previous=NULL) {
        parent::__construct($message, $code, $previous);
    }
}
