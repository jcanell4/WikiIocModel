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

class ProjectExistException extends WikiIocProjectException {
    public function __construct($page, $message='projectExist', $code=7201) {
        parent::__construct($message, $code, $page);
    }
}

class ProjectNotExistException extends WikiIocProjectException {
    public function __construct($page, $message='projectNotExist', $code=7202) {
        parent::__construct($message, $code, $page);
    }
}

class UnknownProjectException extends WikiIocProjectException {
    public function __construct($page, $message='unknownProject', $code=7203) {
        parent::__construct($message, $code, $page);
    }
}

class UserNotAuthorizedException extends WikiIocProjectException {
    public function __construct($page, $message='userNotAuthorized', $code=7204) {
        parent::__construct($message, $code, $page);
    }
}

class AuthorNotVerifiedException extends WikiIocProjectException {
    public function __construct($page, $message='authorNotVerified', $code=7205) {
        parent::__construct($message, $code, $page);
    }
}

class ResponsableNotVerifiedException extends WikiIocProjectException {
    public function __construct($page, $message='responsableNotVerified', $code=7206) {
        parent::__construct($message, $code, $page);
    }
}

class InsufficientPermissionToEditProjectException extends WikiIocProjectException {
    public function __construct($page, $message='insufficientPermissionToEditProject', $code=7207) {
        parent::__construct("$message$page", $code, $page);
    }
}

class InsufficientPermissionToCreateProjectException extends WikiIocProjectException {
    public function __construct($page, $message='insufficientPermissionToCreateProject', $code=7208) {
        parent::__construct($message, $code, $page);
    }
}

class InsufficientPermissionToDeleteProjectException extends WikiIocProjectException {
    public function __construct($page, $message='insufficientPermissionToDeleteProject', $code=7209) {
        parent::__construct($message, $code, $page);
    }
}

class InsufficientPermissionToGenerateProjectException extends WikiIocProjectException {
    public function __construct($page, $message='insufficientPermissionToGenerateProject', $code=7210) {
        parent::__construct("$message$page", $code, $page);
    }
}

class MissingGroupFormBuilderException extends WikiIocProjectException {
    public function __construct($page='', $message='MissingGroupFormBuilder', $code=7301) {
        parent::__construct($message, $code, $page);
    }
}

class MissingValueFormBuilderException extends WikiIocProjectException {
    public function __construct($page='', $message='MissingValueFormBuilder', $code=7302) {
        parent::__construct($message, $code, $page);
    }
}

class WrongNumberOfColumnsFormBuilderException extends WikiIocProjectException {
    public function __construct($page='', $message='nombre incorrecte de', $code=7303) {
        parent::__construct("Has indicat $message columnes i el nombre de columnes admés està entre 1 i 12", $code, $page);
    }
}
