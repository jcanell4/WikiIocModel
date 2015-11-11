<?php
/**
 * Define las clases de excepciones
 *
 * @author Rafael Claver
 */
abstract class AbstractWikiIocModelException extends Exception {
    public function __construct($message, $code, $previous=NULL) {
        parent::__construct($message, $code, $previous);
    }
}
