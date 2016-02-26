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

require_once(WIKI_IOC_MODEL . 'WikiIocModelExceptions.php');
require_once(WIKI_IOC_MODEL . 'WikiIocLangManager.php');

class PageNotFoundException extends WikiIocModelException
{
    public function __construct($page, $codeMessage = "pageNotFound",
                                $code = 1001, $previous = NULL)
    {
        //parent::__construct(sprintf($message, $page), $code, $previous);
        parent::__construct($codeMessage, $code, $previous, $page);
    }
}

class PageAlreadyExistsException extends WikiIocModelException
{
    public function __construct($page, $message = "The page %s already exists",
                                $code = 1002, $previous = NULL)
    {
        parent::__construct($message, $code, $previous, $page);
    }
}

class DateConflictSavingException extends WikiIocModelException
{
    public function __construct($page, $codeMessage = "conflictsSaving",
                                $code = 1003, $previous = NULL)
    {
//        $message = WikiIocLangManager::getLang($codeMessage);
//        if ($message == NULL) {
//            $message = $codeMessage;
//        }
//        parent::__construct(sprintf($message, $page), $code, $previous);
        parent::__construct($codeMessage, $code, $previous, $page);
    }
}

class WordBlockedException extends WikiIocModelException
{
    public function __construct($page, $codeMessage = "wordblock",
                                $code = 1004, $previous = NULL)
    {
//        $message = WikiIocLangManager::getLang($codeMessage);
//        if ($message == NULL) {
//            $message = $codeMessage;
//        }
//        parent::__construct(sprintf($message, $page), $code, $previous);
        parent::__construct($codeMessage, $code, $previous, $page);

    }
}

class InsufficientPermissionToCreatePageException extends WikiIocModelException
{
//    public function __construct($page, $message="You don't have enough permission to create page %s.", $code=1005, $previous=NULL) {
    public function __construct($page, $codeMessage = 'auth_CreatePage', $code = 1005, $previous = NULL)
    {
        parent::__construct($codeMessage, $code, $previous, $page);
    }
}

class InsufficientPermissionToViewPageException extends WikiIocModelException
{
//    public function __construct($page, $message="You don't have enough permission to view page %s.", $code=1006, $previous=NULL) {
    public function __construct($page, $codeMessage = 'auth_ViewPage', $code = 1006, $previous = NULL)
    {
        parent::__construct($codeMessage, $code, $previous, $page);
    }
}

class InsufficientPermissionToEditPageException extends WikiIocModelException
{
//    public function __construct($page, $message="You don't have enough permission to edit page %s.", $code=1007, $previous=NULL) {
    public function __construct($page, $codeMessage = 'auth_EditPage', $code = 1007, $previous = NULL)
    {
        parent::__construct($codeMessage, $code, $previous, $page);
    }
}

class InsufficientPermissionToWritePageException extends WikiIocModelException
{
    public function __construct($page, $codeMessage = 'auth_WritePage', $code = 1009, $previous = NULL)
    {
        parent::__construct($codeMessage, $code, $previous, $page);
    }
}

class HttpErrorCodeException extends WikiIocModelException
{
    public function __construct($code, $message = "", $previous = NULL)
    {
        parent::__construct($message, $code, $previous);
    }
}

class AuthorizationCommandNotFound extends WikiIocModelException
{
    public function __construct($code = 1008, $message = "Authorization command not found", $previous = NULL)
    {
        parent::__construct($message, $code, $previous);
    }
}

class InsufficientPermissionToDeletePageException extends WikiIocModelException
{
//    public function __construct($page, $message="You don't have enough permission to create page %s.", $code=1005, $previous=NULL) {
    public function __construct($page, $codeMessage = 'auth_DeletePage', $code = 1010, $previous = NULL)
    {
        parent::__construct($codeMessage, $code, $previous, $page);
    }
}

class InsufficientPermissionToUploadMediaException extends WikiIocModelException
{
//    public function __construct($page, $message="You don't have enough permission to create page %s.", $code=1005, $previous=NULL) {
    public function __construct($codeMessage = 'auth_UploadMedia', $code = 1011, $previous = NULL)
    {
        parent::__construct($codeMessage, $code, $previous);
    }
}

