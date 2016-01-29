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

class UnavailableMethodExecutionException extends Exception {
    public function __construct($method, $message="Unavailable method %s", 
                                                $code=9001, $previous=NULL) {
        parent::__construct(sprintf($message, $method), $code, $previous);
    }
}
