<?php

/**
 * Component: Project / MetaData
 * Status: @@Development
 * Purposes:
 * - File to contain all Exceptions types in Project / Metadata
 * @author Miguel Àngel Lozano Márquez <mlozan54@ioc.cat>
 */
if (!defined('DOKU_INC'))
    die();

require_once DOKU_INC . 'lib/plugins/wikiiocmodel/WikiIocModelExceptions.php';

class MalFormedJSON extends WikiIocModelException {

    public function __construct($code = 5100, $message = "Malformed JSON to decode", $previous = NULL) {
        parent::__construct($message, $code, $previous);
    }

}

class NotAllEntityMandatoryProperties extends WikiIocModelException {

    public function __construct($code = 5110, $message = "Set de propietats de l'entitat és incomplet", $previous = NULL) {
        parent::__construct($message, $code, $previous);
    }

}


