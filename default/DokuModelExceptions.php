<?php
//namespace ioc_dokuwiki; //[TO DO Josep] Adaptar la classe a  l'espai de noms

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of WikiIocModelException
 *
 * @author Josep Cañellas <jcanell4@ioc.cat>
 */
if (!defined('DOKU_INC')) die();
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_INC . 'lib/plugins/wikiiocmodel/');

require_once (WIKI_IOC_MODEL . 'WikiIocModelExceptions.php');
require_once (WIKI_IOC_MODEL . 'WikiIocLangManager.php');

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
//    public function __construct($page, $message="You don't have enough permission to create page %s.", $code=1005, $previous=NULL) {
    public function __construct($page, $codeMessage='auth_CreatePage', $code=1005, $previous=NULL) {
        $message = WikiIocLangManager::getLang($codeMessage);
        if ($message == NULL) {
            $message = $codeMessage;
        }
        parent::__construct(sprintf($message, $page), $code, $previous);
    }
}

class InsufficientPermissionToViewPageException extends WikiIocModelException {
//    public function __construct($page, $message="You don't have enough permission to view page %s.", $code=1006, $previous=NULL) {
    public function __construct($page, $codeMessage='auth_ViewPage', $code=1006, $previous=NULL) {
        $message = WikiIocLangManager::getLang($codeMessage);
        if ($message == NULL) {
            $message = $codeMessage;
        }
        parent::__construct(sprintf($message, $page), $code, $previous);
    }
}

class InsufficientPermissionToEditPageException extends WikiIocModelException {
//    public function __construct($page, $message="You don't have enough permission to edit page %s.", $code=1007, $previous=NULL) {
    public function __construct($page, $codeMessage='auth_EditPage', $code=1007, $previous=NULL) {
        $message = WikiIocLangManager::getLang($codeMessage);
        if ($message == NULL) {
            $message = $codeMessage;
        }
        parent::__construct(sprintf($message, $page), $code, $previous);
    }
}

class HttpErrorCodeException extends WikiIocModelException {
    public function __construct($code, $message="", $previous=NULL) {
        parent::__construct($message, $code, $previous);
    }
}

class AuthorizationCommandNotFound extends WikiIocModelException {
    public function __construct($code=1008, $message="Authorization command not found", $previous=NULL) {
        parent::__construct($message, $code, $previous);
    }
}

