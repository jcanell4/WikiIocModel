<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of WikiIocModelException
 *
 * @author Josep Cañellas <jcanell4@ioc.cat>
 */
class WikiIocModelException extends Exception {}
class PageNotFoundException extends WikiIocModelException {
    public function __construct($page, $message="Page %s not found", 
                                                $code=1001, $previous=NULL) {
        parent::__construct(sprintf($message, $page), $code, $previous);
    }
}
class PageAlreadyExistsException extends WikiIocModelException {
    public function __construct($page, $message="The page %s already exists", 
                                                $code=1001, $previous=NULL) {
        parent::__construct(sprintf($message, $page), $code, $previous);
    }
}

