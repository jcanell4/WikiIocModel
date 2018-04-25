<?php
/**
 * projectRevertResponseHandler: Construye los datos para la respuesta de la parte servidora para el comando 'revert'
 * @culpable Rafael Claver
 */
if (!defined("DOKU_INC")) die();
require_once(DOKU_TPL_INCDIR."cmd_response_handler/ProjectResponseHandler.php");

class projectRevertResponseHandler extends ProjectResponseHandler {

    function __construct($cmd) {
        WikiIocResponseHandler::__construct(end(explode("_", $cmd)));
    }

    protected function postResponse($requestParams, $responseData, &$ajaxCmdResponseGenerator) {
        parent::postResponse($requestParams, $responseData, $ajaxCmdResponseGenerator);

        $id = $responseData[ProjectKeys::KEY_ID];

        if (!$responseData['projectExtraData'][ProjectKeys::PROJECT_TYPE]) {
            $ajaxCmdResponseGenerator->addExtraContentStateResponse($id, ProjectKeys::PROJECT_TYPE,
                                                                    $requestParams[ProjectKeys::PROJECT_TYPE]);
        }
        if ($responseData['info']) {
            $ajaxCmdResponseGenerator->addInfoDta($responseData['info']);
        }
        $ajaxCmdResponseGenerator->addProcessFunction(true, "ioc/dokuwiki/processCloseTab",
                                                      ['id' => $id.ProjectKeys::REVISION_SUFFIX,
                                                       'idToShow' => $id]
                                                     );
    }

    protected function response($requestParams, $responseData, &$ajaxCmdResponseGenerator) {
        $id = $responseData[ProjectKeys::KEY_ID];
        $rev = $responseData[ProjectKeys::KEY_REV];
        $pType = $requestParams[ProjectKeys::KEY_PROJECT_TYPE];

        $this->viewResponse($requestParams, $responseData, $ajaxCmdResponseGenerator);

        //afegir la metadata de revisions com a resposta
        if ($rev && count($rev) > 0) {
            $responseData[ProjectKeys::KEY_REV]['call_diff'] = "project&do=diff&projectType=$pType";
            $responseData[ProjectKeys::KEY_REV]['call_view'] = "project&do=view&projectType=$pType";
            $responseData[ProjectKeys::KEY_REV]['urlBase'] = "lib/exe/ioc_ajax.php?call=".$rev['call_diff'];
            $ajaxCmdResponseGenerator->addRevisionsTypeResponse($id, $rev);
        }else {
            $xtr = ['id' => $id,
                    'idr' => "{$id}_revisions",
                    'txt' => "No hi ha revisions",
                    'html' => "<h3>Aquest projecte no té revisions</h3>"
                   ];
            $ajaxCmdResponseGenerator->addExtraMetadata($xtr['id'], $xtr['idr'], $xtr['txt'], $xtr['html']);
        }
    }

    protected function viewResponse($requestParams, $responseData, &$ajaxCmdResponseGenerator) {
        $id = $responseData[ProjectKeys::KEY_ID];
        $ns = $requestParams[ProjectKeys::KEY_ID];
        $title = "Projecte $ns";
        $outValues = [];

        $form = $this->buildForm($id, $ns, $responseData['projectMetaData']['structure'], $responseData['projectViewData'], $outValues);

        $ajaxCmdResponseGenerator->addViewProject($id, $ns, $title, $form,
            $outValues
//            $responseData['projectMetaData']['values']
        );
        $this->addMetadataResponse($id, $ns, $ajaxCmdResponseGenerator);
    }

}
