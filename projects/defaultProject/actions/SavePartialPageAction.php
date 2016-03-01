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
    public function __construct(/*BasicPersistenceEngine*/ $engine) {
        parent::__construct($engine);
        $this->defaultDo = DW_ACT_SAVE;
    }
    
    protected function startProcess() {
        parent::startProcess();
        // $editing=NULL, $selected=NULL, $rev = null)
        $this->dokuPageModel->init($this->params['id'], 
                $this->params['editing_chunks'],
                $this->params['section_id'],
                $this->params['rev']);     
    }

        protected function runProcess() {
        parent::runProcess();
        $this->getModel()->removeChunkDraft($selected);

        $this->lock();        
        
    }

    protected function responseProcess(){
//        $response =  parent::responseProcess();
        //$response['structure'] = $this->getStructuredDocument($selected, $pid, $prev, $editing_chunks);
        $response = array_merge($response =  parent::responseProcess(), $this->getModel()->getData());

        // TODO: afegir el 'info' que correspongui
        if (!$response['info']) {
            $response['info'] = $this->generateInfo(
                    "info", 
                    sprintf(WikiIocLangManager::getLang('section_saved'), $selected)
            ); // TODO[Xavi] Aquesta info s'afegeix en algún lloc, s'ha de moure aquí i fe la localització
        }

        // TODO: afegir el 'meta' que correspongui
        $response['meta'] = $this->getMetaTocResponse();


        // TODO: afegir les 'revs' que correspongui
        $response['revs'] = $this->getRevisionList();

//        $this->removeStructuredDraft($pid, $selected);
//
//        $this->lock($pid);

        return $response;  
    }
}
