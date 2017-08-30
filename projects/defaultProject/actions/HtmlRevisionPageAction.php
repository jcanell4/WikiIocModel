<?php
/**
 * Description of HtmlRevisionPageAction
 * @author josep
 */
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');

require_once DOKU_PLUGIN."wikiiocmodel/projects/defaultProject/actions/HtmlPageAction.php";
require_once DOKU_PLUGIN."wikiiocmodel/projects/defaultProject/DokuModelExceptions.php";

if (!defined('DW_ACT_SHOW')) define('DW_ACT_SHOW', "show");
if (!defined('DW_DEFAULT_PAGE')) define('DW_DEFAULT_PAGE', "start");

class HtmlRevisionPageAction extends HtmlPageAction {

    public function __construct(BasicPersistenceEngine $engine) {
        parent::__construct($engine);
        $this->defaultDo = DW_ACT_SHOW;
    }

    protected function startProcess() {
        parent::startProcess();
    }

    protected function responseProcess() {
        $response = $this->getModel()->getData();

        $revisionInfo = WikiIocLangManager::getXhtml('showrev');

        // ALERTA[Xavi] Canvis per fer servir una pestanya per revisions
        $response['structure']['id'] .= PageAction::REVISION_SUFFIX;

        // ALERTA[Xavi] Fi Canvis

        $response['structure']['html'] = str_replace($revisionInfo, '', $response['structure']['html']);

        // Si no s'ha especificat cap altre missatge mostrem el de carrega
        if (!$response['info']) {
            $response['info'] = $this->generateInfo("warning", strip_tags($revisionInfo), $response['structure']['id']);
        } else {
            $this->addInfoToInfo($response['info'], $this->generateInfo("info", strip_tags($revisionInfo), $response['structure']['id']));
        }

//        $response['meta'] = $this->addMetaTocResponse();
        $this->addMetaTocResponse($response);




        $response['revs'] = $this->getRevisionList();

        $this->addNotificationsMetaToResponse($response, $response['ns']);

        // Corregim els ids de les metas per indicar que és una revisió
        $this->addRevisionSuffixIdToArray($response['meta']);


        return $response;
    }


}
