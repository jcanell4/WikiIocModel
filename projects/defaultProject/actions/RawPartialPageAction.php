<?php


if (!defined("DOKU_INC")) {
    die();
}
if (!defined('DOKU_PLUGIN')) {
    define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
}

require_once(DOKU_INC . 'inc/common.php');
require_once(DOKU_INC . 'inc/actions.php');
require_once(DOKU_INC . 'inc/template.php');
require_once DOKU_PLUGIN . "ownInit/WikiGlobalConfig.php";
require_once DOKU_PLUGIN . "wikiiocmodel/WikiIocInfoManager.php";
require_once DOKU_PLUGIN . "wikiiocmodel/WikiIocLangManager.php";
require_once DOKU_PLUGIN . "wikiiocmodel/projects/defaultProject/actions/PageAction.php";
require_once DOKU_PLUGIN . "wikiiocmodel/projects/defaultProject/DokuModelExceptions.php";
require_once(DOKU_PLUGIN . 'ajaxcommand/requestparams/PageKeys.php');
require_once DOKU_PLUGIN . "wikiiocmodel/ResourceUnlockerInterface.php";
require_once DOKU_PLUGIN . "wikiiocmodel/ResourceLockerInterface.php";

if (!defined('DW_ACT_EDIT')) {
    define('DW_ACT_EDIT', "edit");
}

if (!defined('DW_ACT_DENIED')) {
    define('DW_ACT_DENIED', "denied");
}

if (!defined('DW_DEFAULT_PAGE')) {
    define('DW_DEFAULT_PAGE', "start");
}

/**
 * Description of RawPageAction
 *
 * @author josep
 */
class RawPartialPageAction extends PageAction  implements ResourceLockerInterface, ResourceUnlockerInterface
{
    public function __construct(/*BasicPersistenceEngine*/
        $engine)
    {
        parent::__construct($engine);
        $this->defaultDo = DW_ACT_EDIT;
    }

    protected function startProcess()
    {
        parent::startProcess();
        $this->dokuPageModel->init($this->params[PageKeys::KEY_ID],
            $this->params[PageKeys::KEY_EDITING_CHUNKS],
            $this->params[PageKeys::KEY_SECTION_ID],
            $this->params[PageKeys::KEY_REV],
            $this->params[PageKeys::KEY_RECOVER_DRAFT]);
    }

    /**
     * És un mètode per sobrescriure. Per defecte no fa res, però la
     * sobrescriptura permet processar l'acció i emmagatzemar totes aquelles
     * dades  intermèdies que siguin necessàries per generar la resposta final:
     * DokuAction#responseProcess.
     */
    protected function runProcess()
    {
        if (!WikiIocInfoManager::getInfo("exists")) {
            throw new PageNotFoundException($this->params[PageKeys::KEY_ID], WikiIocLangManager::getLang('pageNotFound'));
        }
    }

    /**
     * És un mètode per sobrescriure. Per defecte no fa res, però la
     * sobrescriptura permet generar la resposta a enviar al client. Aquest
     * mètode ha de retornar la resposa o bé emmagatzemar-la a l'atribut
     * DokuAction#response.
     */
    protected function responseProcess()
    {
        // Abans de generar la resposta s'ha d'eliminar l'esborrany complet si escau
        if ($this->params[PageKeys::KEY_DISCARD_DRAFT]) {
            $this->getModel()->removeFullDraft($this->params[PageKeys::KEY_ID]);
        }

        $response = $this->getModel()->getData(TRUE);

//        $locked = $this->lock($this->params[PageKeys::KEY_ID]); // Alerta[Xavi] el document ha d'estar bloquejat en qualsevol cas
//        $response['timeout'] = $locked['timeout'];

        // ALERTA[Xavi] Nova gestió del lock

        /* ALERTA[Josep] Ja no serveix. Ara arriba l'estat amb la resposta de getModel()->rawData() [Xavi] Comprovar com funciona pel parcial
        $response[PageKeys::KEY_LOCK_STATE] = $this->requireResource();




        /*
        TODO[Xavi] En lloc de les constants LOCAL_FULL_DRAFT pot ser suficient detecgar el valor de la resposta 'local'


        FULL DRAFT:
            Si existeix LOCAL_FULL_DRAFT, comparem data:
                LOCAL_FULL_DRAFT es més recent (major), canviem a LOCAL_FULL_DRAFT, afegir response 'LOCAL'=true

            Si no existeix FULL (la data es -1) s'estableix el local


        sino STRUCTURED_DRAFT:

            Si existeix LOCAL_STRUCTURED:
                STRUCTURED_DRAFT es més recent (data major), sense canvis
                LOCAL_STRUCTURED_DRAFT es més recent (data major), canviem a LOCAL_STRUCTURED_DRAFT, afegir response 'LOCAL'=true

        sino NO_DRAFT:
            Si existeix LOCAL_FULL_DRAFT:
                canviem a LOCAL_FULL_DRAFT, afegir response 'LOCAL'=true

            Sino, si existeix LOCAL_STRUCTURED:
                canviem a LOCAL_STRUCTURED_DRAFT, afegir response 'LOCAL'=true

       */

        $fullLastLocalDraftTime = intval(substr($this->params[PageKeys::FULL_LAST_LOCAL_DRAFT_TIME], 0, 10));
        $structuredLastLocalDraftTime = intval(substr($this->params[PageKeys::STRUCTURED_LAST_LOCAL_DRAFT_TIME], 0, 10));

        // Si l'esborrany estructurad local es més recent que l'esborrany complet local, ignorem l'esborrany local complet
        if ($structuredLastLocalDraftTime>$fullLastLocalDraftTime) {
            $fullLastLocalDraftTime = null;
        }

        if (!isset($this->params[PageKeys::KEY_RECOVER_DRAFT]) && !$this->params[PageKeys::KEY_DISCARD_DRAFT] && $fullLastLocalDraftTime) {
            // obtenir la data del draft full local
            $fullLastSavedDraftTime = $this->dokuPageModel->getFullDraftDate();
            if ($fullLastLocalDraftTime > $fullLastSavedDraftTime) { // local es més recent
                $response['local'] = true;
                $response['draftType'] = DokuPageModel::LOCAL_FULL_DRAFT;
            }
        } else if (!isset($this->params[PageKeys::KEY_RECOVER_DRAFT]) && !$this->params[PageKeys::KEY_DISCARD_DRAFT] && $structuredLastLocalDraftTime) {
            $structuredLastSavedDraftTime = $this->dokuPageModel->getStructuredDraftDate();

//            $structuredLastSavedDraftTime = 1558822524; // TODO[Xavi] Forçant la comprovació, ELIMINAR!

            if ($structuredLastLocalDraftTime > $structuredLastSavedDraftTime) { // local es més recent
                $response['local'] = true;
                $response['draftType'] = DokuPageModel::LOCAL_PARTIAL_DRAFT;
            }


        }


        if ($this->params[PageKeys::KEY_RECOVER_LOCAL_DRAFT] ==='true') {
            // TODO[Xavi] Moure aqui la recuperació del draft? si s'ha demanat recuperar es que ja s'han mostrat els dialogs que tocaven i no cal comprovar els 'isset'
            $response[PageKeys::KEY_RECOVER_LOCAL_DRAFT] = true;
            $response['info'] = $this->generateInfo('warning', WikiIocLangManager::getLang('local_draft_editing'));


        } else if (!isset($this->params[PageKeys::KEY_RECOVER_DRAFT]) && ($response['draftType'] === DokuPageModel::FULL_DRAFT
                || $response['draftType'] === DokuPageModel::LOCAL_FULL_DRAFT)) {

            // No existeix el KEY_RECOVER_DRAFT però hi ha un full draft
            // Acció: mostrar dialeg continuar amb edició parcial (es perd l'esborrany) o passar a edició completa

            $response['original_call'] = $this->generateOriginalCall();
            $response['id'] = WikiPageSystemManager::getContainerIdFromPageId($this->params[PageKeys::KEY_ID]);
            $response['show_draft_conflict_dialog'] = true;
            $response['info'] = $this->generateInfo('warning', WikiIocLangManager::getLang('draft_found'));


        } else if (!isset($this->params[PageKeys::KEY_RECOVER_DRAFT]) && ($response['draftType'] === DokuPageModel::PARTIAL_DRAFT
                || $response['draftType'] === DokuPageModel::LOCAL_PARTIAL_DRAFT)) {
            // No existeix el KEY_RECOVER_DRAFT però hi ha un partial_draft
            // Acció: mostrar dialeg seleccionar document o esborrany

            $response['show_draft_dialog'] = true;
            $response['original_call'] = $this->generateOriginalCall();
            $response['info'] = $this->generateInfo('warning', WikiIocLangManager::getLang('partial_draft_found'));

        } else if ($this->params[PageKeys::KEY_RECOVER_DRAFT]==='true') {
            // Existeix el KEY_RECOVER_DRAFT i es cert
            // Acció: recuperar esborrany

            $this->getModel()->replaceContentForChunk($response['structure'], $this->params[PageKeys::KEY_SECTION_ID],
                $response["draft"]['content']);
            $response['info'] = $this->generateInfo('warning', WikiIocLangManager::getLang('draft_editing'));

        } else {
            // Acció: recuperar el document

            if ($locked['timeout'] < 0) {
                $response['info'] = $locked['info'];
            } else {
                $response['info'] = $this->generateInfo('success', WikiIocLangManager::getLang('chunk_editing') . $this->params[PageKeys::KEY_ID] . ':' . $this->params[PageKeys::KEY_SECTION_ID]);
            }
        }


        return $response;
    }

    private function generateOriginalCall()
    {
        // ALERTA[Xavi] Cal afegir el el ns, ja que aquest no forma part dels params
        $originalCall = $this->params;
        $originalCall['ns'] = $this->params[PageKeys::KEY_ID];
        $originalCall['id'] = WikiPageSystemManager::getContainerIdFromPageId($this->params[PageKeys::KEY_ID]);
        return $originalCall;
    }


    /**
     * Es tracta del mètode que hauran d'executar en iniciar el bloqueig. Per  defecte no bloqueja el recurs, perquè
     * actualment el bloqueig es realitza internament a les funcions natives de la wiki. Malgrat tot, per a futurs
     * projectes es contempla la possibilitat de fer el bloqueig directament aquí, si es passa el paràmetre amb valor
     * TRUE. EL mètode comprova si algú està bloquejant ja el recurs i en funció d'això, retorna una constant amb el
     * resultat obtingut de la petició.
     *
     * @param bool $lock
     * @return int
     */
    public function requireResource($lock = FALSE)
    {
        return $this->resourceLocker->requireResource($lock);
    }

    public function leaveResource($unlock = FALSE)
    {
        throw new UnavailableMethodExecutionException('CancelEditPageAction#leaveResource');
    }
}
