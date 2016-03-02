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
require_once DOKU_PLUGIN."wikiiocmodel/projects/defaultProject/actions/CancelEditPageAction.php";
require_once DOKU_PLUGIN."wikiiocmodel/projects/defaultProject/DokuModelExceptions.php";
require_once DOKU_PLUGIN."ajaxcommand/requestparams/PageKeys.php";

/**
 * Description of CancelPartialEditPageAction
 *
 * @author josep
 */
class CancelPartialEditPageAction extends CancelEditPageAction {
    public function __construct(/*BasicPersistenceEngine*/ $engine) {
        parent::__construct($engine);
    }

    protected function runProcess() {
        // Si es passa keep_draft = true no s'esborra
        if (!$this->params[PageKeys::KEY_KEEP_DRAFT]) {
            $this->getModel()->removeChunkDraft($this->params[PageKeys::KEY_SECTION_ID]);
        }
    }
    
    protected function responseProcess() {
//        $response = array();
        //$response['structure'] = $this->getStructuredDocument(null, $pid, NULL, $editing_chunks);
        $response = $this->getModel()->getData();
        $response['structure']['cancel'] = [$this->params[PageKeys::KEY_SECTION_ID]];
        $response['info'] = $this->generateInfo("info", WikiIocLangManager::getLang('chunk_closed'));
        return $response;
    }
}
