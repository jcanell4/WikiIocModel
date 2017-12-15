<?php
/**
 * Description of CancelEditPageAction
 * @author josep
 */
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');

require_once(DOKU_INC . 'inc/common.php');
require_once DOKU_PLUGIN . "wikiiocmodel/projects/defaultProject/actions/PageAction.php";

class CancelEditPageAction extends RenderedPageAction implements ResourceUnlockerInterface {
    protected $resourceLocker;

    public function init($modelManager) {
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
            $response["codeType"]=0;
            return $response;
        }

        $response = parent::responseProcess();

        if ($this->params[PageKeys::DISCARD_CHANGES]) {
            $response['structure']['discard_changes'] = $this->params[PageKeys::DISCARD_CHANGES];
        }

        if ($response['draft'])
            $response ['info'] = $this->generateInfo("warning", WikiIocLangManager::getLang('edition_cancelled'), $response['structure']['id']);
        else
            $response['info'] = $this->generateInfo("info", WikiIocLangManager::getLang('edition_closed'), $response['structure']['id']);
        if($this->params[PageKeys::KEY_AUTO]){
            if($this->params[PageKeys::KEY_KEEP_DRAFT]){
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

    protected function runProcess()
    {
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

        if ($this->params[PageKeys::KEY_REV]) {
            $suffix = $this->params[PageKeys::KEY_REV] ? PageAction::REVISION_SUFFIX : '';
            $response['close']['id'] = WikiPageSystemManager::getContainerIdFromPageId($this->params[PageKeys::KEY_ID]) . $suffix;
        }
    }

   /**
     * Es tracta del mètode que hauran d'executar en iniciar el desbloqueig o també quan l'usuari cancel·la la demanda
     * de bloqueig. Per  defecte no es desbloqueja el recurs, perquè actualment el desbloqueig es realitza internament
     * a les funcions natives de la wiki. Malgrat tot, per a futurs projectes es contempla la possibilitat de fer el
     * desbloqueig directament aquí, si es passa el paràmetre amb valor TRUE. EL mètode retorna una constant amb el
     * resultat obtingut de la petició.
     *
     * @param bool $unlock
     * @return int
     */
    public function leaveResource($unlock = FALSE)
    {
        return $this->resourceLocker->leaveResource($unlock);
    }
}
