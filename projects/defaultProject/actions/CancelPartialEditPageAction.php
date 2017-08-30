<?php
/**
 * Description of CancelPartialEditPageAction
 * @author josep
 */
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');

require_once (DOKU_INC.'inc/common.php');
require_once DOKU_PLUGIN."ajaxcommand/defkeys/PageKeys.php";
require_once DOKU_PLUGIN."wikiiocmodel/WikiIocInfoManager.php";
require_once DOKU_PLUGIN."wikiiocmodel/WikiIocLangManager.php";
require_once DOKU_PLUGIN."wikiiocmodel/projects/defaultProject/actions/CancelEditPageAction.php";
require_once DOKU_PLUGIN."wikiiocmodel/projects/defaultProject/DokuModelExceptions.php";
require_once DOKU_PLUGIN."wikiiocmodel/ResourceUnlockerInterface.php";
require_once DOKU_PLUGIN."wikiiocmodel/ResourceLockerInterface.php";

class CancelPartialEditPageAction extends CancelEditPageAction implements ResourceLockerInterface, ResourceUnlockerInterface {

    public function __construct(BasicPersistenceEngine $engine) {
        parent::__construct($engine);
    }

    protected function runProcess() {
        // Si es passa keep_draft = true no s'esborra
        if (!$this->params[PageKeys::KEY_KEEP_DRAFT]) {
            $this->getModel()->removeChunkDraft($this->params[PageKeys::KEY_SECTION_ID]);
        }

        $unlock = isset($this->params[PageKeys::KEY_UNLOCK]) ? $this->params[PageKeys::KEY_UNLOCK] : FALSE; // ALERTA[Xavi] Canviat a false, si no sempre s'alliberà

        if (count($this->params[PageKeys::KEY_EDITING_CHUNKS])==0 || $unlock) {
            $this->leaveResource(TRUE);
//            unlock($this->params[PageKeys::KEY_ID]);
        }

    }

    protected function responseProcess() {
//        $response = array();
        //$response['structure'] = $this->getStructuredDocument(null, $pid, NULL, $editing_chunks);
        $response = $this->getModel()->getData();
        $response['structure']['cancel'] = [$this->params[PageKeys::KEY_SECTION_ID]];

        if ($this->params[PageKeys::DISCARD_CHANGES]) {
            $response['structure']['discard_changes_partial'] =$this->params[PageKeys::DISCARD_CHANGES];
        }

        if($this->params[PageKeys::KEY_TO_REQUIRE]){
                // TODO: afegir el 'meta' que correspongui perquè si va al requiring dialog, el content tool es crerà de nou 
//                $response['meta'] = $this->addMetaTocResponse();
                $this->addMetaTocResponse($response);
                // TODO: afegir les revisions
                $response['revs'] = $this->getRevisionList();
        }
        $response['info'] = $this->generateInfo("info", WikiIocLangManager::getLang('chunk_closed'), $this->params[PageKeys::KEY_ID]);
        return $response;
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
