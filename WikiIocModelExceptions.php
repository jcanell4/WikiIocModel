<?php
/**
 * Define las clases de excepciones
 *
 * @author Rafael Claver
 */
if (!defined('DOKU_INC')) die();
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_INC . 'lib/plugins/wikiiocmodel/');

require_once (WIKI_IOC_MODEL . 'WikiIocLangManager.php');
require_once (DOKU_INC . 'inc/inc_ioc/Logger.php');

abstract class WikiIocModelException extends Exception {
    public function __construct($codeMessage, $code, $previous=NULL, $target=NULL) {
        $message = WikiIocLangManager::getLang($codeMessage);
        if ($message == NULL) {
            $message = $codeMessage;
        }
        if ($target) {
            $message = sprintf($message, $target);
        }
        Logger::debug("Params, codemessage: $codeMessage message: $message code: $code, previous: $previous, target: $target", 0, 0, "", 0);
        parent::__construct($message, $code, $previous);
    }
}

class HttpErrorCodeException extends WikiIocModelException {
    public function __construct($message, $code, $previous=NULL) {
        parent::__construct($message, $code, $previous);
    }
}

class UnavailableMethodExecutionException extends WikiIocModelException {
    public function __construct($method, $message="Unavailable method %s", $code=9001, $previous=NULL) {
        parent::__construct($message, $code, $previous, $method);
    }
}

class AuthorizationNotTokenVerified extends WikiIocModelException {
    public function __construct($codeMessage='auth_TokenNotVerified', $code=9020, $previous=NULL) {
        parent::__construct($codeMessage, $code, $previous);
    }
}

class AuthorizationNotUserAuthenticated extends WikiIocModelException {
    public function __construct($codeMessage='auth_UserNotAuthenticated', $code=9021, $previous=NULL) {
        parent::__construct($codeMessage, $code, $previous);
    }
}
class AuthorizationNotCommandAllowed extends WikiIocModelException {
    public function __construct($codeMessage="auth_CommadNotAllowed", $code=9022, $previous=NULL) {
        parent::__construct($codeMessage, $code, $previous);
    }
}
class FileIsLockedException extends WikiIocModelException {
    public function __construct($id="", $codeMessage="lockedByAlert", $code=9023, $previous=NULL) {
        parent::__construct($codeMessage, $code, $previous, $id);
    }
}

class DraftNotFoundException extends WikiIocModelException {
    public function __construct($id="", $codeMessage='DraftNotFoundException', $code=9024, $previous=NULL) {
        parent::__construct($codeMessage, $code, $previous, $id);
    }
}

class UnexpectedLockCodeException extends WikiIocModelException {
    public function __construct($id="", $codeMessage='UnexpectedLockCode', $code=9025, $previous=NULL) {
        parent::__construct($codeMessage, $code, $previous, $id);
    }
}

/**
 * Excepciones propias de los proyectos
 */
abstract class WikiIocProjectException extends Exception {
    public function __construct($codeMessage, $code, $target=NULL) {
        $message = WikiIocLangManager::getLang('projectException')[$codeMessage];
        if ($message == NULL) {
            $message = $codeMessage;
        }
        if ($target) {
            $message = sprintf($message, $target);
        }
        parent::__construct($message, $code, NULL);
    }
}

class InsufficientPermissionToCreatePageException extends WikiIocProjectException {
    public function __construct($page, $codeMessage='auth_CreatePage', $code=7001) {
        parent::__construct($codeMessage, $code, $page);
    }
}

class InsufficientPermissionToViewPageException extends WikiIocProjectException {
    public function __construct($page, $codeMessage='auth_ViewPage', $code=7002) {
        parent::__construct($codeMessage, $code, $page);
    }
}

class InsufficientPermissionToEditPageException extends WikiIocProjectException {
    public function __construct($page, $codeMessage='auth_EditPage', $code=7003) {
        parent::__construct($codeMessage, $code, $page);
    }
}

class InsufficientPermissionToWritePageException extends WikiIocProjectException {
    public function __construct($page, $codeMessage='auth_WritePage', $code=7004) {
        parent::__construct($codeMessage, $code, $page);
    }
}

class InsufficientPermissionToDeletePageException extends WikiIocProjectException {
    public function __construct($page, $codeMessage='auth_DeletePage', $code=7005) {
        parent::__construct($codeMessage, $code, $page);
    }
}

class UnknownPojectTypeException extends WikiIocProjectException {
    public function __construct($page, $codeMessage='UnknownPojectType', $code=7006) {
        parent::__construct($codeMessage, $code, $page);
    }
}
