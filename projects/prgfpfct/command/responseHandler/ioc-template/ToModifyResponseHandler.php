<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ToModifyResponseHandler
 *
 * @author josep
 */
if (!defined("DOKU_INC")) die();
require_once(DOKU_TPL_INCDIR."cmd_response_handler/ProjectResponseHandler.php");

class ToModifyResponseHandler extends ProjectResponseHandler {

    function __construct($cmd) {
        parent::__construct(end(explode("_", $cmd)));
    }

    protected function response($requestParams, $responseData, &$ajaxCmdResponseGenerator) {
        $this->_responseEditResponse($requestParams, $responseData, $ajaxCmdResponseGenerator, JsonGenerator::PROJECT_EDIT_TYPE);
    }

}
