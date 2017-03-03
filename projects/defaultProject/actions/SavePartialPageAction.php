<?php
if (!defined("DOKU_INC")) {
    die();
}
if (!defined('DOKU_PLUGIN')) {
    define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
}
if (!defined('DW_ACT_CRATE')) {
    define('DW_ACT_CREATE', "create");
}
if (!defined('DW_ACT_SAVE')) {
    define('DW_ACT_SAVE', "save");
}

require_once DOKU_PLUGIN . 'wikiiocmodel/projects/defaultProject/actions/SavePageAction.php';
require_once DOKU_PLUGIN."wikiiocmodel/projects/defaultProject/DokuModelExceptions.php";
require_once DOKU_PLUGIN."wikiiocmodel/WikiIocInfoManager.php";

/**
 * Description of SavePartialPageAction
 *
 * @author josep
 */
class SavePartialPageAction extends SavePageAction{
     private $lockStruct;
     
    public function __construct(/*BasicPersistenceEngine*/ $engine) {
        parent::__construct($engine);
        $this->defaultDo = DW_ACT_SAVE;
    }
    
    protected function startProcess() {
        parent::startProcess();
        // $editing=NULL, $selected=NULL, $rev = null)
        $this->dokuPageModel->init($this->params[PageKeys::KEY_ID], 
                $this->params[PageKeys::KEY_EDITING_CHUNKS],
                $this->params[PageKeys::KEY_SECTION_ID],
                $this->params[PageKeys::KEY_REV]);     
    }

    protected function runProcess() {
        parent::runProcess();
        $this->getModel()->removeChunkDraft($this->params[PageKeys::KEY_SECTION_ID]);
//        $this->updateLock();
        $this->lockStruct = $this->updateLock();
    }

    protected function responseProcess(){
//        $response =  parent::responseProcess();
        //$response['structure'] = $this->getStructuredDocument($selected, $pid, $prev, $editing_chunks);
        $response = array_merge($response =  parent::responseProcess(), $this->getModel()->getData());

        // TODO: afegir el 'info' que correspongui
        if (!$response['info']) {
            $response['info'] = $this->generateInfo(
                    "info", 
                    sprintf(WikiIocLangManager::getLang('section_saved'), $this->params[PageKeys::KEY_SECTION_ID]),
                    $response["structure"]["id"],
                    15
            ); // TODO[Xavi] Aquesta info s'afegeix en algún lloc, s'ha de moure aquí i fe la localització
        }

        // TODO: afegir el 'meta' que correspongui
//        $response['meta'] = $this->addMetaTocResponse();
        $this->addMetaTocResponse($response);

        // TODO: afegir les 'revs' que correspongui
        $response['revs'] = $this->getRevisionList();
        $response["lockInfo"] = $this->lockStruct["info"];
//        $this->removeStructuredDraft($pid, $selected);
//        $this->lock($pid);

        return $response;  
    }
}
