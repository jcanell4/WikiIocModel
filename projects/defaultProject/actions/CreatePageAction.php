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
require_once (DOKU_INC . 'inc/common.php');

/**
 * Description of CreatePageAction
 *
 * @author josep
 */
class CreatePageAction extends SavePageAction {
    public function __construct(/*BasicPersistenceEngine*/ $engine) {
        parent::__construct($engine);
        $this->defaultDo = DW_ACT_CREATE;
    }
    
    protected function responseProcess() {
        $response = array();
        
        $response['structure'] = $this->getModel()->getData();

        if (!$response['info']) {
            $response['info'] = $this->generateInfo("info", $lang['document_created']);
        }

        $response['meta'] = $this->getMetaTocResponse();

        $response['revs'] = $this->getRevisionList();
        
        return $response;
    }

    protected function runProcess() {
        if (WikiIocInfoManager::getInfo("exists")) {
            throw new PageAlreadyExistsException($pid, $lang['pageExists']);
        }
        parent::runProcess();
    }

    protected function startProcess() {
        global $TEXT;
        global $ACT;
        parent::startProcess();
        $ACT = DW_ACT_SAVE;
        if (!$this->params['text']) {
            $TEXT = $this->params['text'] = cleanText(WikiIocLangManager::getLang('createDefaultText'));
        }        
    }
}
