<?php

if (!defined("DOKU_INC")) {
    die();
}
if (!defined('DOKU_PLUGIN')) {
    define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
}

require_once (DOKU_INC . 'inc/common.php');
require_once DOKU_PLUGIN."wikiiocmodel/WikiIocInfoManager.php";
require_once DOKU_PLUGIN."wikiiocmodel/WikiIocLangManager.php";
require_once DOKU_PLUGIN."wikiiocmodel/projects/defaultProject/actions/PageAction.php";
require_once DOKU_PLUGIN."wikiiocmodel/projects/defaultProject/DokuModelExceptions.php";

if (!defined('DW_ACT_DRAFTDEL')) {
    define('DW_ACT_DRAFTDEL', "draftdel");
}
/**
 * Description of CancelEditPageAction
 *
 * @author josep
 */
class CancelEditPageAction extends PageAction {
      //protected $draftQuery;

      public function __construct(/*BasicPersistenceEngine*/ $engine) {
        parent::__construct($engine);
        //$this->draftQuery = $engine->createDraftDataQuery();
        $this->defaultDo = DW_ACT_DRAFTDEL;
  }
  
  protected function startProcess() {
      if(!isset($this->params['keep_draft'])){
          $this->params['keep_draft'] = false;
      }
      parent::startProcess();
     $this->dokuPageModel->init($this->params['id'], $this->params['editingChunks'], NULL, $this->params['rev']);                
  }

  protected function responseProcess() {
    $response = array();


    $response['structure']  = $this->getModel()->getData();


    $response ['info'] = $this->generateInfo("warning", WikiIocLangManager::getLang('edition_cancelled'));

    $response['meta'] = $this->getMetaTocResponse();
    $response['revs'] = $this->getRevisionList();        


    return $response;
  }

  protected function runProcess() {
    // Si es passa keep_draft = true no s'esborra
    if (!$this->params['keep_draft']) {
        $this->clearFullDraft();
        $this->clearPartialDraft();
    }
    unlock($this->params['id']);
  }

  private function clearFullDraft(){
    global $ACT;
    WikiIocInfoManager::setInfo('draft', $this->getModel()->getDraftFileName());
    $ACT = act_draftdel($ACT);

  }

    
  private function clearPartialDraft(){
    $this->getModel()->removePartialDraft();
  }
}
