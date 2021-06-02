<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ToReviseResponseHandler
 *
 * @author josep
 */
if (!defined("DOKU_INC")) die();
require_once(DOKU_TPL_INCDIR."cmd_response_handler/ProjectResponseHandler.php");

class ToReviseResponseHandler extends ProjectResponseHandler {

    function __construct($cmd) {
        parent::__construct(end(explode("_", $cmd)));
    }

    protected function response($requestParams, $responseData, &$ajaxCmdResponseGenerator) {
        $this->remoteViewResponse($requestParams, $responseData, $ajaxCmdResponseGenerator);
        if ($responseData['notifications']) {
            foreach ($responseData['notifications'] as $notification) {
                $ajaxCmdResponseGenerator->addNotificationResponse($notification['action'], $notification['params']);
            }
        }
    }
}
