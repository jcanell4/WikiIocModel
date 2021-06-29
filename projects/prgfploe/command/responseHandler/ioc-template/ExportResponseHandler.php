<?php
/**
 * ExportResponseHandler
 * @culpable rafael <rclaver@xtec.cat>
 */
if (!defined("DOKU_INC")) die();
require_once(DOKU_TPL_INCDIR."cmd_response_handler/ProjectResponseHandler.php");

class ExportResponseHandler extends ProjectResponseHandler {

    function __construct($cmd) {
        parent::__construct(end(explode("_", $cmd)));
    }

    protected function response($requestParams, $responseData, &$ajaxCmdResponseGenerator) {
        if ($responseData) {
            $responseData[ProjectKeys::PROJECT_TYPE] = $requestParams[ProjectKeys::PROJECT_TYPE];
            $title = WikiIocLangManager::getLang("metadata_export_title");
            $pageId = $responseData[ProjectKeys::KEY_ID];
            $ajaxCmdResponseGenerator->addExtraMetadata($pageId, $pageId."_iocexport", $title, $responseData["meta"]);
        }else {
            $ajaxCmdResponseGenerator->addError(1000, "EXPORTACIÓ NO REALITZADA");
        }
    }

}
