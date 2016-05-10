<?php

if (!defined("DOKU_INC")) {
    die();
}
if (!defined('DOKU_PLUGIN')) {
    define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
}

require_once(DOKU_INC . 'inc/common.php');
require_once DOKU_PLUGIN . "wikiiocmodel/WikiIocInfoManager.php";
require_once DOKU_PLUGIN . "wikiiocmodel/WikiIocLangManager.php";
require_once DOKU_PLUGIN . "wikiiocmodel/projects/defaultProject/actions/PageAction.php";
require_once DOKU_PLUGIN . "wikiiocmodel/projects/defaultProject/DokuModelExceptions.php";
require_once DOKU_PLUGIN . "ajaxcommand/requestparams/PageKeys.php";
require_once DOKU_PLUGIN . "wikiiocmodel/ResourceUnlockerInterface.php";
require_once DOKU_PLUGIN . "wikiiocmodel/ResourceLockerInterface.php";

if (!defined('DW_ACT_DRAFTDEL')) {
    define('DW_ACT_DRAFTDEL', "draftdel");
}

/**
 * Description of CancelEditPageAction
 *
 * @author josep
 */
class CancelEditPageAction extends PageAction implements ResourceLockerInterface, ResourceUnlockerInterface
{
    //protected $draftQuery;

    protected $resourceLocker;

    public function __construct(/*BasicPersistenceEngine*/
        $engine)
    {
        parent::__construct($engine);
        //$this->draftQuery = $engine->createDraftDataQuery();
        $this->defaultDo = DW_ACT_DRAFTDEL;

    }

    protected function startProcess()
    {
        if (!isset($this->params[PageKeys::KEY_KEEP_DRAFT])) {
            //$this->params[PageKeys::KEY_KEEP_DRAFT] = false;
            $this->params[PageKeys::KEY_KEEP_DRAFT] = TRUE; //[JOSEP] Alerta [Xavi]! si es maté a FALSE elimina el draft sempre per defecte!
        }
        parent::startProcess();
        $this->dokuPageModel->init($this->params[PageKeys::KEY_ID], $this->params[PageKeys::KEY_EDITING_CHUNKS], NULL, $this->params[PageKeys::KEY_REV]);
    }

    protected function responseProcess()
    {
//    $response = array();


//    $response['structure']  = $this->getModel()->getData();
        $response = $this->getModel()->getData();

        if ($this->params[PageKeys::DISCARD_CHANGES]) {
            $response['structure']['discard_changes'] = $this->params[PageKeys::DISCARD_CHANGES];
        }


        $response ['info'] = $this->generateInfo("warning", WikiIocLangManager::getLang('edition_cancelled'));

        $response['meta'] = $this->getMetaTocResponse();
        $response['revs'] = $this->getRevisionList();


        return $response;
    }

    protected function runProcess()
    {
        // Si es passa keep_draft = true no s'esborra
        if (!$this->params[PageKeys::KEY_KEEP_DRAFT]) {
            $this->clearFullDraft();
            $this->clearPartialDraft();
        }

        $this->leaveResource(TRUE);
        //unlock($this->params[PageKeys::KEY_ID]);


        if (!WikiIocInfoManager::getInfo("exists")) {
            throw new PageNotFoundException($this->params[PageKeys::KEY_ID], WikiIocLangManager::getLang('pageNotFound'));
        }
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
        throw new UnavailableMethodExecutionException('CancelEditPageAction#requireResource');
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
