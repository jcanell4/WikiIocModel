<?php
/**
 * Description of RawPartialPageAction
 * @author josep
 */
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');

require_once(DOKU_INC . 'inc/common.php');
require_once(DOKU_INC . 'inc/actions.php');
require_once(DOKU_INC . 'inc/template.php');
require_once DOKU_PLUGIN . "ajaxcommand/defkeys/PageKeys.php";
require_once DOKU_PLUGIN . "wikiiocmodel/projects/defaultProject/actions/PageAction.php";
require_once DOKU_PLUGIN . "wikiiocmodel/projects/defaultProject/DokuModelExceptions.php";

if (!defined('DW_ACT_EDIT')) define('DW_ACT_EDIT', "edit");
if (!defined('DW_ACT_DENIED')) define('DW_ACT_DENIED', "denied");
if (!defined('DW_DEFAULT_PAGE')) define('DW_DEFAULT_PAGE', "start");

class RawPartialPageAction extends PageAction implements ResourceLockerInterface, ResourceUnlockerInterface
{
    private $lockStruct;

    public function __construct(BasicPersistenceEngine $engine) {
        parent::__construct($engine);
        $this->defaultDo = DW_ACT_EDIT;
    }

    protected function startProcess() {
        parent::startProcess();

        // TODO[Xavi] Actualitzar al client les crides dels esborranys per fer servir el KEY_DO
        if ($this->params[PageKeys::KEY_DO] === PageKeys::KEY_TO_REQUIRE) {
            $this->params[PageKeys::KEY_TO_REQUIRE] = TRUE;
        } else if ($this->params[PageKeys::KEY_DO] === PageKeys::KEY_RECOVER_LOCAL_DRAFT) {
            $this->params[PageKeys::KEY_RECOVER_LOCAL_DRAFT] = TRUE;
        } else if ($this->params[PageKeys::KEY_DO] === PageKeys::KEY_RECOVER_LOCAL_DRAFT) {
            $this->params[PageKeys::KEY_RECOVER_LOCAL_DRAFT] = TRUE;
        }

        $this->dokuPageModel->init($this->params[PageKeys::KEY_ID],
            $this->params[PageKeys::KEY_EDITING_CHUNKS],
            $this->params[PageKeys::KEY_SECTION_ID],
            $this->params[PageKeys::KEY_REV],
            $this->params[PageKeys::KEY_RECOVER_DRAFT]);

        // Abans de generar la resposta s'ha d'eliminar l'esborrany complet si escau
        if ($this->params[PageKeys::KEY_DISCARD_DRAFT]) {
            $this->getModel()->removeFullDraft($this->params[PageKeys::KEY_ID]);
        }
    }

    /**
     * És un mètode per sobrescriure. Per defecte no fa res, però la
     * sobrescriptura permet processar l'acció i emmagatzemar totes aquelles
     * dades  intermèdies que siguin necessàries per generar la resposta final:
     * DokuAction#responseProcess.
     *
     * ALERTA[Xavi] Identic al RawPageAction#runProcess();
     */
    protected function runProcess()
    {
        if (!WikiIocInfoManager::getInfo(WikiIocInfoManager::KEY_EXISTS)) {
            throw new PageNotFoundException($this->params[PageKeys::KEY_ID]);
        }

        $ACT = act_permcheck($this->defaultDo);

        if ($ACT == DW_ACT_DENIED) {
            throw new InsufficientPermissionToEditPageException($this->params[PageKeys::KEY_ID]);
        }

        if (!$this->params[PageKeys::KEY_REV]) {
            $this->lockStruct = $this->requireResource(TRUE);
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
        $response = [];
        $data = $this->getModel()->getData(TRUE);

        // 1) Ja s'ha recuperat el draft local
        if ($this->params[PageKeys::KEY_RECOVER_LOCAL_DRAFT]) {

            $response = $this->_getLocalDraftResponse($data);

        } else if($this->lockState()==ST_LOCKED_BEFORE){
            //-1 L'usuari te obert el document en una altra sessio

            // ALERTA[Xavi] Copiat de "bloquejat" el missatge enviat es l'únic que canvia.
            //  No es pot editar. Cal esperar que s'acabi el bloqueig
            $response = $this->_getSelfLockedDialog($data); // <-- acció equivalent al RawPageAction
//            $response['meta'] = $this->addMetaTocResponse();
            $this->addMetaTocResponse($response);
            $response['revs'] = $this->getRevisionList();

        } else {

            // 2.1) Es demana recuperar el draft?
            if ($this->params[PageKeys::KEY_RECOVER_DRAFT] === TRUE) {

                $response = $this->_getDraftResponse($data); // ALERTA[Xavi] Els drafts sempre es recuperaran localment, això ja no s'haurà de cridar mai

                // 2.2) Es troba desbloquejat?
            } else if (!$data['structure']['locked']) { //

                if ($this->params[PageKeys::KEY_RECOVER_DRAFT] === FALSE) {

                    // 2.2.1) S'ha especificat recuperar el document
                    $response = $this->_getDocumentResponse($data);
                } else {




                    // 2.2.1) Es generarà el dialog de draft pertinent, o el document si no hi ha cap draft per enviar
                   $response = $this->_getDialogOrDocumentResponse($data);

                   if($this->params[PageKeys::KEY_TO_REQUIRE]){
                        // TODO: afegir el 'meta' que correspongui perquè si ve del requiring dialog, el content tool es crerà de nou
//                       $response['meta'][] = $this->addMetaTocResponse();
                       $this->addMetaTocResponse($response);
                        // TODO: afegir les revisions
                       $response['revs'] = $this->getRevisionList();
                   }
                }

                // 2.3) El document es troba bloquejat
            } else {

                // TODO[Xavi]El document està bloquejat
                //  No es pot editar. Cal esperar que s'acabi el bloqueig
                 $response = $this->_getWaitingUnlockDialog($data); // <-- acció equivalent al RawPageAction
                // TODO: afegir el 'meta' que correspongui perquè si va al requiring dialog, el content tool es crerà de nou
//                $response['meta'][] = $this->addMetaTocResponse();
                $this->addMetaTocResponse($response);
                // TODO: afegir les revisions
                $response['revs'] = $this->getRevisionList();

            }
        }

        $response["lockInfo"] = $this->lockStruct["info"];

        $ns = isset($response['ns']) ? $response['ns'] : $response['structure']['ns'];
        //$response['meta'][] = $this->addNotificationsMetaToResponse($response, $ns);
        $this->addNotificationsMetaToResponse($response, $ns);

        return $response;
    }


    private function generateOriginalCall()
    {
        // ALERTA[Xavi] Cal afegir el  ns, ja que aquest no forma part dels params

        $originalCall['ns'] = $this->params[PageKeys::KEY_ID];
        $originalCall['id'] = WikiPageSystemManager::getContainerIdFromPageId($this->params[PageKeys::KEY_ID]);
        $originalCall['rev'] = $this->params[PageKeys::KEY_REV];
        $originalCall['section_id'] = $this->params[PageKeys::KEY_SECTION_ID];
        $originalCall['editing_chunks'] = $this->params[PageKeys::KEY_EDITING_CHUNKS];

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

    // TODO[Xavi] copiat de RawPageAction
    private function generateLockInfo($lockState, $mes)
    {
        $info = null;
        $infoType = 'message';
        $message = null;

        switch ($lockState) {

            case self::LOCKED:
                // El fitxer no estava bloquejat
                $message = WikiIocLangManager::getLang('chunk_editing'). $this->params[PageKeys::KEY_ID] . ':' . $this->params[PageKeys::KEY_SECTION_ID];
                $infoType = 'info';
                break;

            case self::REQUIRED:
                // S'ha d'afegir una notificació per l'usuari que el te bloquejat
                $lockingUser = WikiIocInfoManager::getInfo(WikiIocInfoManager::KEY_LOCKED);
                $message = WikiIocLangManager::getLang('lockedby') . ' ' . $lockingUser;
                $infoType = 'error';
                break;

            case self::LOCKED_BEFORE:
                // El teniem bloquejat nosaltres
                $message = WikiIocLangManager::getLang('alreadyLocked');
                $infoType = 'warning';
                break;

            default:
                throw new UnknownTypeParamException($lockState);

        }
        if ($mes && $message) {
            $message = $this->addInfoToInfo(self::generateInfo($infoType, $mes, $this->params[PageKeys::KEY_ID]),
                self::generateInfo($infoType, $message, $this->params[PageKeys::KEY_ID]));
        } else if ($mes) {
            $message = self::generateInfo($infoType, $mes, $this->params[PageKeys::KEY_ID]);
        } else{
            $message = self::generateInfo($infoType, $message, $this->params[PageKeys::KEY_ID]);
        }
        return $message;
    }

    // TODO[Xavi] copiat de RawPageAction

    private function lockState()
    {
        return $this->lockStruct["state"];
    }

    private function _getLocalDraftResponse($data)
    {
        $response = $data;
        $response[PageKeys::KEY_RECOVER_LOCAL_DRAFT] = true;
        $response['info'] = $this->generateInfo('warning', WikiIocLangManager::getLang('local_draft_editing'));

        return $response;
    }

    private function _getDraftResponse($data)
    {
        // Existeix el KEY_RECOVER_DRAFT i es cert
        // Acció: recuperar esborrany

        $response = $data;

        $this->getModel()->replaceContentForChunk($response['structure'], $this->params[PageKeys::KEY_SECTION_ID],
            $response["draft"]['content']);
        $response['info'] = $this->generateInfo('warning', WikiIocLangManager::getLang('draft_editing'));

        return $response;
    }

    private function _getConflictDialogResponse($response)
    {
        $response['original_call'] = $this->generateOriginalCall();
        $response['id'] = WikiPageSystemManager::getContainerIdFromPageId($this->params[PageKeys::KEY_ID]);
        $response['show_draft_conflict_dialog'] = true;
        $response['info'] = $this->generateInfo('warning', WikiIocLangManager::getLang('draft_found'));

        return $response;
    }

    private function _getDraftInfo($data)
    {

        $draftInfo ['draftType'] = $data['draftType'];
        $draftInfo['local'] = false;

        // ALERTA[Xavi] QUE FEM: Calcular la data dels esborranys locals
        $fullLastLocalDraftTime = $this->params[PageKeys::FULL_LAST_LOCAL_DRAFT_TIME];
        $structuredLastLocalDraftTime = $this->params[PageKeys::STRUCTURED_LAST_LOCAL_DRAFT_TIME];

        // Si l'esborrany estructurad local es més recent que l'esborrany complet local, ignorem l'esborrany local complet
        // ALERTA[Xavi] QUE FEM: Descartar la data del esborrany complet local si el parcial es més recent
        if ($structuredLastLocalDraftTime >= $fullLastLocalDraftTime) {
            $fullLastLocalDraftTime = null;
        }

        // ALERTA[Xavi] QUE FEM: No existeix el KEY_RECOVER_DRAFT, ni KEY_DISCARD_DRAFT, però existeix un FULL LOCAL DRAFT, comprovem si es més recent el FULL REMOT
        if (!isset($this->params[PageKeys::KEY_RECOVER_DRAFT]) && !$this->params[PageKeys::KEY_DISCARD_DRAFT]) {
            if ($fullLastLocalDraftTime) {
                // obtenir la data del draft full local
                $fullLastSavedDraftTime = $this->dokuPageModel->getFullDraftDate();
                if ($fullLastLocalDraftTime > $fullLastSavedDraftTime) { // local es més recent
                    $draftInfo ['local'] = true;
                    $draftInfo ['draftType'] = PageKeys::LOCAL_FULL_DRAFT;
                }

                // ALERTA[Xavi] QUE FEM: Igual que l'anterior però amb STRUCTURED LOCAL
            } else if ($structuredLastLocalDraftTime) {
                $structuredLastSavedDraftTime = $this->dokuPageModel->getStructuredDraftDate();

//            $structuredLastSavedDraftTime = 1558822524; // TODO[Xavi] Forçant la comprovació, ELIMINAR!

                if ($structuredLastLocalDraftTime > $structuredLastSavedDraftTime) { // local es més recent
                    $draftInfo ['local'] = true;
                    $draftInfo ['draftType'] = PageKeys::LOCAL_PARTIAL_DRAFT;
                }
            }

        }

        return $draftInfo;
    }

    private function _getDraftDialogResponse($data)
    {
        $response = $this->generateOriginalCall();
        $response['show_draft_dialog'] = true;
        $response['title'] = $data['structure']['title'];
        $response['info'] = $this->generateInfo('warning', WikiIocLangManager::getLang('partial_draft_found'));
        $response['lastmod'] = $data['structure']['date'];
        $response['content'] = $data['content']['editing'];
        $response['draft'] = $data['draft'];

        return $response;
    }

    private function _getDocumentResponse($data) {
        $response = $data;
        $response['info'] = $this->generateLockInfo($this->lockState(), $response['info']);
        return $response;
    }

    private function _getDialogOrDocumentResponse($data)
    {
        $draftInfo = $this->_getDraftInfo($data);

        switch ($draftInfo['draftType']) {
            // Conflicte de drafts
            case PageKeys::LOCAL_FULL_DRAFT:
            case PageKeys::FULL_DRAFT:
                // Conflict
                $response = $this->_getConflictDialogResponse($data);
                break;

            // Existeix un draft parcial
            case PageKeys::LOCAL_PARTIAL_DRAFT:
            case PageKeys::PARTIAL_DRAFT:
                $response = $this->_getDraftDialogResponse($data);
                $response['local'] = $draftInfo['local'];
                break;

            // No hi ha draft, es mostrarà el document
            case PageKeys::NO_DRAFT:
                $response = $this->_getDocumentResponse($data);
                break;

            default:
                throw new UnknownTypeParamException($draftInfo['draftType']);


        }

        if ($draftInfo['draftType'] === PageKeys::FULL_DRAFT || $draftInfo['draftType'] === PageKeys::PARTIAL_DRAFT) {
            // TODO: Afegir a la resposta els esborranys remots per actualitzar els locals (
                    $response['update_drafts'][$draftInfo['draftType']] = $data['draft'];
        }


        return $response;
    }

    private function _getWaitingUnlockDialog($data)
    {
        $resp = $this->_getDocumentResponse($data);
        //TODO [Josep][Xavi] Cal implementar quan estigui fet el sistema de diàlegs al client.
        //Aquí caldrà avisar que no és possible editar l'esborrany perquè hi ha algú editant prèviament el document
        // i es podrien perdre dades. També caldrà demanar si vol que l'avisin quan acabi el bloqueig
        return $resp;
    }

    private function _getSelfLockedDialog($data)
    {
        $resp = $this->_getDialogOrDocumentResponse($data);
        $resp["structure"]["locked_before"]=true;
//        $resp['structure']['locked'] = true;

        //TODO [Josep] Cal implementar quan estigui fet el sistema de diàlegs al client.
        //Aquí caldrà avisar que no és possible editar l'esborrany perquè hi ha algú editant prèviament el document
        // i es podrien perdre dades. També caldrà demanar si vol que l'avisin quan acabi el bloqueig
        return $resp;
    }
}


