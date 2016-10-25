<?php
/**
 * DocumentationModelException
 * - establece las clases de excepciones propias de este proyecto
 * @culpable Rafael Claver
 */
if (!defined('DOKU_INC')) die();
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_INC . 'lib/plugins/wikiiocmodel/');

require_once(WIKI_IOC_MODEL . 'WikiIocModelExceptions.php');
require_once(WIKI_IOC_MODEL . 'WikiIocLangManager.php');

class ProjectExistException extends WikiIocModelException {
    public function __construct($page, $message = "The project %s already exist", $code=2001, $previous=NULL) {
        parent::__construct($message, $code, $previous, $page);
    }
}

class ProjectNotExistException extends WikiIocModelException {
    public function __construct($page, $message = "The project %s not already exist", $code=2002, $previous=NULL) {
        parent::__construct($message, $code, $previous, $page);
    }
}

class UnknownProjectException extends WikiIocModelException {
    public function __construct($page, $message="Unknown project exception", $code=2003, $previous=NULL) {
        parent::__construct($message, $code, $previous, $page);
    }
    
}
