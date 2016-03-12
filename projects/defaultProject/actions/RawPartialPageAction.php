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
class RawPartialPageAction extends PageAction
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

        /*
         * Casos:
         *  NO Existeix KEY_RECOVER_DRAFT I:
         *      - Existeix DRAFT_FULL? -> Enviar dialeg per continuar amb edició parcial o completa: $response['full_draft'] = true;
         *      - SINO -> Existeix DRAFT_PARTIAL? -> Mostrar dialog seleccionar esborrany o document: $response['show_draft_dialog'] = true;
         *
         * KEY_RECOVER_DRAFT existeix, es cert i no hi ha cap esborrany:
         *      - Es retorna l'esborrany
         *
         * SINO: KEY_RECOVER_DRAFT existeix però es fals, o no hi ha cap esborrany
         *      - Es retorna el document
         *
         *
         */


        // ALERTA[Xavi] Nova implementació
        $response = $this->getModel()->getData(TRUE);
        $locked = $this->lock($this->params[PageKeys::KEY_ID]); // Alerta[Xavi] el document ha d'estar bloquejat en qualsevol cas
        $response['timeout'] = $locked['timeout'];


        //TOOD[Xavi] No funciona correctament, peta quan es selecciona continuar amb la edició parcial! , error 500, alguna cosa es crida que no es correcte
        if (!isset($this->params[PageKeys::KEY_RECOVER_DRAFT]) && in_array(DokuPageModel::FULL_DRAFT, $response['draftTypesFound'])) { // TODO[Xavi] Replantejar això, aquest s'afegeix però el parcial no

            // No existeix el KEY_RECOVER_DRAFT però hi ha un full draft
            // Acció: mostrar dialeg continuar amb edició parcial (es perd l'esborrany) o passar a edició completa

            $response['original_call'] = $this->generateOriginalCall();
            $response['id'] = $this->params[PageKeys::KEY_ID];
            $response['full_draft'] = true;
            $response['info'] = $this->generateInfo('warning', WikiIocLangManager::getLang('draft_found'));


        } else if (!isset($this->params[PageKeys::KEY_RECOVER_DRAFT]) && in_array(DokuPageModel::PARTIAL_DRAFT, $response['draftTypesFound'])) { // TODO[Xavi] Replantejar això, aquest no s'afegeix però el parcial sí
            // No existeix el KEY_RECOVER_DRAFT però hi ha un partial_draft
            // Acció: mostrar dialeg seleccionar document o esborrany


            $response['show_draft_dialog'] = true;

            // TODO[Xavi] això s'ha de comprovar al recuperar l'esborrany si es posible, no te sentit que es faci aquí aquesta gestió
            if ($response['draft']['content'] === $response['content']['editing']) {
                // L'esborrany es identic al contingut, es pot descartar l'esborrany
                $this->removeStructuredDraft($this->params[PageKeys::KEY_ID], $this->params[PageKeys::KEY_SECTION_ID]);
                unset($response['draft']);
                $response['show_draft_dialog'] = false;
            }

            $response['original_call'] = $this->generateOriginalCall();
            $response['info'] = $this->generateInfo('warning', WikiIocLangManager::getLang('partial_draft_found'));

        } else if ($this->params[PageKeys::KEY_RECOVER_DRAFT]) {
            // Existeix el KEY_RECOVER_DRAFT i es cert
            // Acció: recuperar esborrany

            $this->setContentForChunkByHeader($response['structure'], $response["draft"]);
            $response['info'] = $this->generateInfo('warning', WikiIocLangManager::getLang('draft_editing'));

        } else {
            // Acció: recuperar el document

            if ($locked['timeout'] < 0) {
                $response['info'] = $locked['info'];
            } else {
                $response['info'] = $this->generateInfo('success', WikiIocLangManager::getLang('chunk_editing') . $this->params[PageKeys::KEY_ID] . ':' . $this->params[PageKeys::KEY_SECTION_ID]);
            }
        }
        // FI del a nova implementació











//        $response = $this->getModel()->getData(TRUE);

//        $locked = $this->lock($this->params[PageKeys::KEY_ID]); // Alerta[Xavi] el document ha d'estar bloquejat en qualsevol cas

//        if ($this->params[PageKeys::KEY_RECOVER_DRAFT]) { // TODO[Xavi] Incorrecte, aquesta comprovació no ha de ser així
//            // El client ha enviat una petició per recuperar el draft, i aquest ha de ser forçosament parcial perquè s'ha cridat a aquesta classe
//
////            if ($response["draftType"] == DokuPageModel::PARTIAL_DRAFT) {
//
//                $this->setContentForChunkByHeader($response['structure'], $response["draft"]);
////            }
//
//            $response['info'] = $this->generateInfo('warning', WikiIocLangManager::getLang('draft_editing'));

//        } else if ($response['draftType'] == DokuPageModel::PARTIAL_DRAFT) {
//            // S'ha trobat un esborrany parcial i es demana si volem carregar el chunk original o l'esborrany
//
//            $response['show_draft_dialog'] = true;
//
//            // ALERTA[Xavi] Aquest valors ja hi eren al entrar, s'ha produit cap canvi? <-- hi han casos en que no existeixen?
//            //$response['content'] = $this->getChunkFromStructureById($response['structure']);
//            //$response['draft'] = $this->getStructuredDraftForHeader($this->params[PageKeys::KEY_ID], $this->params[PageKeys::KEY_SECTION_ID]);
//            // ALERTA[Xavi] Fi alerta
//
//            if ($response['draft']['content'] === $response['content']['editing']) {
//                // L'esborrany es identic al contingut, es pot descartar l'esborrany
//                $this->removeStructuredDraft($this->params[PageKeys::KEY_ID], $this->params[PageKeys::KEY_SECTION_ID]);
//                unset($response['draft']);
//                $response['show_draft_dialog'] = false;
//            }
//
//            $response['original_call'] = $this->generateOriginalCall();
//            $response['info'] = $this->generateInfo('warning', WikiIocLangManager::getLang('partial_draft_found'));
//
//        } else if ($response['draftType'] == DokuPageModel::FULL_DRAFT) {
//            // ALERTA[Xavi] això no ha de passar, quan es selecciona el esborrany complet va per la edició completa, no aquesta
//
//            $response['original_call'] = $this->generateOriginalCall();
//            $response['id'] = $this->params[PageKeys::KEY_ID];
//            $response['full_draft'] = true;
//            $response['info'] = $this->generateInfo('warning', WikiIocLangManager::getLang('draft_found'));
//
//        } else if ($this->thereIsStructuredDraftFor($response['structure'])) {
//            // S'ha trobat un draft parcial, s'envia el dialog per demanar si volem recuperar-lo
//
//            $response['show_draft_dialog'] = true;
//            $response['content'] = $this->getChunkFromStructureById($response['structure']);
//            $response['draft'] = $this->getStructuredDraftForHeader($this->params[PageKeys::KEY_ID], $this->params[PageKeys::KEY_SECTION_ID]);
//
//            // Si el contingut del draft es el mateix que el del contingut no cal conservar ni enviar el draft ni conservar-lo
//            if ($response['draft']['content'] === $response['content']['editing']) {
//                $this->removeStructuredDraft($this->params[PageKeys::KEY_ID], $this->params[PageKeys::KEY_SECTION_ID]);
//                unset($response['draft']);
//                $response['show_draft_dialog'] = false;
//            }
//
//            $response['original_call'] = $this->generateOriginalCall();
//            $response['info'] = $this->generateInfo('warning', WikiIocLangManager::getLang('partial_draft_found'));
//
//        } else {
//            // Obrim el document
//
//
//            if ($locked['timeout'] < 0) {
//                $response['info'] = $locked['info'];
//            } else {
//                $response['info'] = $this->generateInfo('success', WikiIocLangManager::getLang('chunk_editing') . $this->params[PageKeys::KEY_ID] . ':' . $this->params[PageKeys::KEY_SECTION_ID]);
//            }
//        }


        return $response;
    }

    private function setContentForChunkByHeader(&$structure, $content)
    {
        for ($i = 0; $i < count($structure['chunks']); $i++) {
            if ($structure['chunks'][$i]['header_id'] == $this->params[PageKeys::KEY_SECTION_ID]) {
                $structure['chunks'][$i]['text']['editing'] = $content['content'];
                break;
            }
        }
        return $structure;
    }

    // ALERTA[Xavi] Recuperats del DokuModelAdapter, segurament ara s'han d'obtenir del DraftDataQuery
        private function getChunkFromStructureById($structure)
    {
        $chunks = $structure['chunks'];
        foreach ($chunks as $chunk) {
            if ($chunk['header_id'] == $this->params[PageKeys::KEY_SECTION_ID]) {
                return $chunk['text'];
            }
        }
        return null;
    }

    // TODO[Xavi] això ha de venir del DraftDataQuery?
        public function getStructuredDraftForHeader($id, $header)
    {
        return DraftManager::getStructuredDraftForHeader($id, $header);
    }

    // TODO[Xavi] això ha de venir del DraftDataQuery?
        public function removeStructuredDraft($id, $header_id)
    {
        DraftManager::removeStructuredDraft($id, $header_id);
    }

    // TODO[Xavi] Això es pot reemplaçar directament, a més no calen paràmetres
        private function generateOriginalCall()
    {
        // ALERTA[Xavi] Cal afegir el el ns, ja que aquest no forma part dels params
        $originalCall = $this->params;
        $originalCall['ns'] = $this->params[PageKeys::KEY_ID];
        return $originalCall;
    }

// Hi ha draft pel chunk a editar?
    private function thereIsStructuredDraftFor($document)
    {
        if (!$this->params[PageKeys::KEY_SECTION_ID]) {
            return false;
        }

        $draft = $this->getStructuredDraft($this->params[PageKeys::KEY_ID]);

        for ($i = 0; $i < count($document['chunks']); $i++) {
            if (array_key_exists($document['chunks'][$i]['header_id'], $draft)
                && $document['chunks'][$i]['header_id'] == $this->params[PageKeys::KEY_SECTION_ID]
            ) {

                // Si el contingut del draft i el propi es igual, l'eliminem
                if ($document['chunks'][$i]['text'] . ['editing'] == $draft[$this->params[PageKeys::KEY_SECTION_ID]]['content']) {
                    $this->removeStructuredDraft($this->params[PageKeys::KEY_ID], $this->params[PageKeys::KEY_SECTION_ID]);
                } else {
                    return true;
                }

            }

        }

        return false;
    }

        public function getStructuredDraft($id)
    {
        return DraftManager::getStructuredDraft($id);
    }

}
