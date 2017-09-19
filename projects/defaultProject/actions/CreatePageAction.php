<?php
/**
 * Description of CreatePageAction
 * @author josep
 */
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');

require_once (DOKU_INC.'inc/common.php');
require_once (DOKU_PLUGIN.'wikiiocmodel/projects/defaultProject/actions/SavePageAction.php');
require_once (DOKU_PLUGIN."wikiiocmodel/projects/defaultProject/DokuModelExceptions.php");
require_once (DOKU_PLUGIN."wikiiocmodel/WikiIocInfoManager.php");
require_once (DOKU_PLUGIN."wikiiocmodel/WikiIocLangManager.php");
require_once (DOKU_PLUGIN."ajaxcommand/defkeys/PageKeys.php");
require_once (DOKU_INC . 'inc/common.php');

if (!defined('DW_ACT_CRATE')) define('DW_ACT_CREATE', "create");
if (!defined('DW_ACT_SAVE')) define('DW_ACT_SAVE', "save");

class CreatePageAction extends SavePageAction {
    public function __construct(BasicPersistenceEngine $engine) {
        parent::__construct($engine);
        $this->defaultDo = DW_ACT_CREATE;
    }

    protected function responseProcess() {

        $response = RenderedPageAction::staticResponseProcess($this);

        if (!$response['info']) {
            $id = str_replace(":", "_", $this->params[PageKeys::KEY_ID]);
            $response['info'] = $this->generateInfo("info", WikiIocLangManager::getLang('document_created'), $id);
        }


        return $response;
    }

    protected function runProcess() {
        if (WikiIocInfoManager::getInfo("exists")) {
            throw new PageAlreadyExistsException($this->params[PageKeys::KEY_ID], 'pageExists');
        }
        parent::runProcess();
        $this->resourceLocker->leaveResource(TRUE);
    }

    protected function startProcess() {
        global $TEXT;
        global $ACT;
        parent::startProcess();
        $ACT = DW_ACT_SAVE;
        if (!$this->params[PageKeys::KEY_WIKITEXT]) {
            if ($this->params[PageKeys::KEY_TEMPLATE]) {
                //[TO DO] JOSEP: La forma aquí seria $this->getModel()->getRawTemplate(ID template) i getRawTemplate implementar-lo a PageDokuModel o potser a WikiRenderizableDataModel.
                //$this->params[PageKeys::KEY_WIKITEXT] = $this->getModel()->getPageDataQuery()->getRaw($this->params[PageKeys::KEY_TEMPLATE]);
                if(WikiIocLangManager::isTemplate($this->params[PageKeys::KEY_TEMPLATE])){
                    $this->params[PageKeys::KEY_WIKITEXT] = WikiIocLangManager::getRawTemplate($this->params[PageKeys::KEY_TEMPLATE]);
                }else if(WikiIocLangManager::isKey($this->params[PageKeys::KEY_TEMPLATE])){
                    $this->params[PageKeys::KEY_WIKITEXT] = WikiIocLangManager::getLang($this->params[PageKeys::KEY_TEMPLATE]);
                }else{
                    $this->params[PageKeys::KEY_WIKITEXT] = cleanText(WikiIocLangManager::getLang('createDefaultText'));
                }
            }else {
                $this->params[PageKeys::KEY_WIKITEXT] = cleanText(WikiIocLangManager::getLang('createDefaultText'));
            }
            $this->params[PageKeys::KEY_WIKITEXT] = str_replace(":%nom_d_usuari%", ":".$this->params['user_id'], $this->params[PageKeys::KEY_WIKITEXT]);
            $TEXT = $this->params[PageKeys::KEY_WIKITEXT];
        }
    }
}
