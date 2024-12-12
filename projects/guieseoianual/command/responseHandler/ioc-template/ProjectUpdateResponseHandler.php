<?php
/**
 * ProjectUpdateResponseHandler: Construye los datos para la respuesta de la parte servidora para el comando 'update'
 * @culpable Rafael Claver
 */
defined('DOKU_INC') || die();
require_once(DOKU_TPL_INCDIR."cmd_response_handler/ProjectResponseHandler.php");

class ProjectUpdateResponseHandler extends ProjectResponseHandler {

    function __construct($cmd) {
        parent::__construct(end(explode("_", $cmd)));
    }

    protected function response($requestParams, $responseData, &$ajaxCmdResponseGenerator) {
        $this->remoteViewResponse($requestParams, $responseData, $ajaxCmdResponseGenerator);
    }

}
