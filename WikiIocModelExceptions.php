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
class WikiIocModelException extends Exception {
    public function __construct($message, $code, $previous=NULL) {
        parent::__construct($message, $code, $previous);
    }
}
class PageNotFoundException extends WikiIocModelException {
    public function __construct($page, $message="Page %s not found", 
                                                $code=1001, $previous=NULL) {
        parent::__construct(sprintf($message, $page), $code, $previous);
    }
}
class PageAlreadyExistsException extends WikiIocModelException {
    public function __construct($page, $message="The page %s already exists", 
                                                $code=1002, $previous=NULL) {
        parent::__construct(sprintf($message, $page), $code, $previous);
    }
}

class DateConflictSavingException extends WikiIocModelException {
    public function __construct($page, $message="There are date conflicts saving the page %s. Changes will be lost", 
                                                $code=1003, $previous=NULL) {
        parent::__construct(sprintf($message, $page), $code, $previous);
    }
}

class WordBlockedException extends WikiIocModelException {
    public function __construct($page, $message="Your change was not saved because it contains blocked text (spam). We connot save changes in page %s. Changes will be lost", 
                                                $code=1004, $previous=NULL) {
        parent::__construct(sprintf($message, $page), $code, $previous);
    }
}

class InsufficientPermissionToCreatePageException extends WikiIocModelException {
    public function __construct($page, $message="You don't have enough permission to create page %s.", 
                                                $code=1005, $previous=NULL) {
        parent::__construct(sprintf($message, $page), $code, $previous);
    }
}

class HttpErrorCodeException extends WikiIocModelException {
    public function __construct($code, $message="", $previous=NULL) {
        parent::__construct($message, $code, $previous);
    }
}

class AnotherErrorCodeException extends WikiIocModelException {
    public function __construct($page, $message="AnotherErrorCodeException", $code=1007, $previous=NULL) {
        parent::__construct(sprintf($message, $page), $code, $previous);
    }
}

