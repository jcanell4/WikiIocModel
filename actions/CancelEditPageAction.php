<?php
/**
 * Description of CancelEditPageAction
 * @author josep
 */
if (!defined("DOKU_INC")) die();
require_once(DOKU_INC . 'inc/common.php');

class CancelEditPageAction extends RenderedPageAction {

    public function init($modelManager=NULL) {
        parent::init($modelManager);
        $this->defaultDo = PageKeys::DW_ACT_DRAFTDEL;
    }

    protected function startProcess()
    {
        if (isset($this->params[PageKeys::KEY_DO]) && $this->params[PageKeys::KEY_DO]==="leaveResource") {
            $this->params[PageKeys::KEY_NO_RESPONSE] = TRUE;
        }
        if (!isset($this->params[PageKeys::KEY_KEEP_DRAFT])) {
            $this->params[PageKeys::KEY_KEEP_DRAFT] = TRUE; //[JOSEP] Alerta [Xavi]! si es manté a FALSE elimina el draft sempre per defecte!
        }
        parent::startProcess();
        $this->dokuPageModel->init($this->params[PageKeys::KEY_ID], $this->params[PageKeys::KEY_EDITING_CHUNKS], NULL, $this->params[PageKeys::KEY_REV]);
    }

    protected function responseProcess()
    {
        if($this->params[PageKeys::KEY_NO_RESPONSE]){
            $response[PageKeys::KEY_CODETYPE]=0;
            return $response;
        }

        $response = parent::responseProcess();

        // Duplicat al HtmlPageAction
        $meta = WikiIocInfoManager::getInfo('meta');
        $response['structure']['partialDisabled'] = isset($meta['partialDisabled']) ? $meta['partialDisabled'] : FALSE;



        if ($this->params[PageKeys::DISCARD_CHANGES]) {
            $response['structure']['discard_changes'] = $this->params[PageKeys::DISCARD_CHANGES];
        }

        if ($response['draft'])
            $response ['info'] = $this->generateInfo("warning", WikiIocLangManager::getLang('edition_cancelled'), $response['structure']['id']);
        else
            $response['info'] = $this->generateInfo("info", WikiIocLangManager::getLang('edition_closed'), $response['structure']['id']);

        if ($this->params[PageKeys::KEY_AUTO]) {
            if ($this->params[PageKeys::KEY_KEEP_DRAFT]) {
                $response ['info'] = $this->addInfoToInfo($response['info'], $this->generateInfo("warning", WikiIocLangManager::getLang('draft_saved'), $response['structure']['id']));
            }
            $response ['info'] = $this->addInfoToInfo($response['info'], $this->generateInfo("warning", WikiIocLangManager::getLang('auto_cancelled'), $response['structure']['id']));
        }

        if (isset($this->params[PageKeys::KEY_REV])) {
            $response['structure']['id'] .= PageAction::REVISION_SUFFIX;
            // Corregim els ids de les metas per indicar que és una revisió
            $this->addRevisionSuffixIdToArray($response['meta']);
        }

        return $response;
    }

    protected function runProcess() {
        // Si es passa keep_draft = true no s'esborra
        if (!$this->params[PageKeys::KEY_KEEP_DRAFT]) {
            $this->clearFullDraft();
            $this->clearPartialDraft();
        }

        $unlockDocument = isset($this->params[PageKeys::KEY_UNLOCK]) ? $this->params[PageKeys::KEY_UNLOCK] : TRUE;

        if ($unlockDocument) {
            $this->leaveResource(TRUE);
        }

        if (!WikiIocInfoManager::getInfo("exists")) {
            throw new PageNotFoundException($this->params[PageKeys::KEY_ID]);
        }
    }

}
