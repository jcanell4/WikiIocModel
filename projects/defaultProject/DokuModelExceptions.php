<?php
/**
 * WikiIocModelException: Excepciones del proyecto 'defaultProject'
 *
 * @author Josep Cañellas <jcanell4@ioc.cat>
 */
if (!defined('DOKU_INC')) die();
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_INC . 'lib/plugins/wikiiocmodel/');

require_once(WIKI_IOC_MODEL . 'WikiIocModelExceptions.php');
require_once(WIKI_IOC_MODEL . 'WikiIocLangManager.php');
require_once(DOKU_INC . 'inc/common.php');

class PageNotFoundException extends WikiIocModelException {
    public function __construct($page, $codeMessage = "pageNotFound", $code=7101, $previous=NULL) {
        parent::__construct($codeMessage, $code, $previous, $page);
    }
}

class PageAlreadyExistsException extends WikiIocModelException {
    public function __construct($page, $message='pageExist', $code=7102, $previous=NULL) {
        parent::__construct($message, $code, $previous, $page);
    }
}

class DateConflictSavingException extends WikiIocModelException {
    public function __construct($page, $codeMessage = "conflictsSaving", $code=7103, $previous=NULL) {
        parent::__construct($codeMessage, $code, $previous, $page);
    }
}

class WordBlockedException extends WikiIocModelException {
    public function __construct($page, $codeMessage = "wordblock", $code=7104, $previous=NULL) {
        parent::__construct($codeMessage, $code, $previous, $page);
    }
}

class CommandAuthorizationNotFound extends WikiIocModelException {
    public function __construct($param='', $message='commandAuthorizationNotFound', $code=7105, $previous=NULL) {
        parent::__construct($message, $code, $previous, $param);
    }
}

class InsufficientPermissionToUploadMediaException extends WikiIocModelException {
    public function __construct($param='', $codeMessage = 'auth_UploadMedia', $code=7106, $previous=NULL) {
        parent::__construct($codeMessage, $code, $previous, $param);
    }
}

class CantCreatePageInProjectException extends WikiIocModelException {
    public function __construct($param='', $message='cantCreatePageInProject', $code=7107, $previous=NULL) {
        parent::__construct($message, $code, $previous, $param);
    }
}

class FailToUploadMediaException extends WikiIocModelException {
    public function __construct($errorCode, $codeMessage = 'uploadfail', $code=7108, $previous=NULL) {
        parent::__construct($codeMessage, $code, $previous, $errorCode);
    }
}

class MaxSizeExcededToUploadMediaException extends WikiIocModelException {
    public function __construct($param='', $codeMessage = 'auth_UploadMedia', $code=7109, $previous=NULL) {
        if(!$codeMessage){
            $codeMessage = sprintf(WikiIocLangManager::getLang('uploadsize'), 
                    filesize_h(php_to_byte(ini_get('upload_max_filesize'))));            
        }
        parent::__construct($codeMessage, $code, $previous, $param);
    }
}