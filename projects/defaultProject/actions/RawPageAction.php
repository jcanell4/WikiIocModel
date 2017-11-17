<?php
/**
 * Description of RawPageAction
 * @author josep
 */
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
if (!defined('WIKI_IOC_DEFAULT_PROJECT')) define('WIKI_IOC_DEFAULT_PROJECT', DOKU_PLUGIN . 'wikiiocmodel/projects/defaultProject/');

require_once(DOKU_INC . 'inc/common.php');
require_once(DOKU_INC . 'inc/actions.php');
require_once(DOKU_INC . 'inc/template.php');
require_once WIKI_IOC_DEFAULT_PROJECT . "actions/PageAction.php";
require_once WIKI_IOC_DEFAULT_PROJECT . "DokuModelExceptions.php";
require_once DOKU_PLUGIN . "wikiiocmodel/persistence/WikiPageSystemManager.php";
require_once DOKU_PLUGIN . "ajaxcommand/defkeys/PageKeys.php";
require_once DOKU_PLUGIN . "wikiiocmodel/ResourceUnlockerInterface.php";
require_once DOKU_PLUGIN . "wikiiocmodel/ResourceLockerInterface.php";

if (!defined('DW_ACT_EDIT')) define('DW_ACT_EDIT', "edit");
if (!defined('DW_ACT_DENIED')) define('DW_ACT_DENIED', "denied");
if (!defined('DW_DEFAULT_PAGE')) define('DW_DEFAULT_PAGE', "start");

class RawPageAction extends PageAction implements ResourceLockerInterface /*,ResourceUnlockerInterface*/ {
//    protected $engine;
    protected $lockStruct;

    public function __construct(BasicPersistenceEngine $engine) {
        parent::__construct($engine);
        $this->defaultDo = DW_ACT_EDIT;
        //Indica que la resposta es renderitza i caldrà llançar l'esdeveniment quan calgui
        $this->setRenderer(TRUE);
    }

    protected function startProcess()
    {
        if ($this->params[PageKeys::KEY_DO] === PageKeys::KEY_TO_REQUIRE) {
            $this->params[PageKeys::KEY_TO_REQUIRE] = TRUE;
        } else if ($this->params[PageKeys::KEY_DO] === PageKeys::KEY_RECOVER_LOCAL_DRAFT) {
            $this->params[PageKeys::KEY_RECOVER_LOCAL_DRAFT] = TRUE;
        } else if ($this->params[PageKeys::KEY_DO] === PageKeys::KEY_RECOVER_LOCAL_DRAFT) {
            $this->params[PageKeys::KEY_RECOVER_LOCAL_DRAFT] = TRUE;
        }

        parent::startProcess();

        if (!$this->params[PageKeys::KEY_SUM]) {
            if ($this->params[PageKeys::KEY_REV]) {
                $this->params[PageKeys::KEY_SUM] = sprintf(WikiIocLangManager::getLang('restored'), dformat($this->params[PageKeys::KEY_REV]));
            } elseif (!WikiIocInfoManager::getInfo('exists')) {
                $this->params[PageKeys::KEY_SUM] = WikiIocLangManager::getLang('created');
            }
        }

        if (!$this->params[PageKeys::KEY_DATE]) {
            global $DATE;
            $DATE = $this->params[PageKeys::KEY_DATE] = WikiIocInfoManager::getInfo("meta")["date"]["modified"];

        }
    }


    /**
     * És un mètode per sobrescriure. Per defecte no fa res, però la
     * sobrescriptura permet processar l'acció i emmagatzemar totes aquelles
     * dades  intermèdies que siguin necessàries per generar la resposta final:
     * DokuAction#responseProcess.
     */
    protected function runProcess()
    {
        if (!WikiIocInfoManager::getInfo(WikiIocInfoManager::KEY_EXISTS)) {
            throw new PageNotFoundException($this->params[PageKeys::KEY_ID]);
        }

        $ACT = act_permcheck($this->params[PageKeys::KEY_ID]);

        if ($ACT == DW_ACT_DENIED) {
            throw new InsufficientPermissionToEditPageException($this->params[PageKeys::KEY_ID]);
        }

        // ALERTA[Xavi] Les revisions no bloquejen el document
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

        //Casos
        // 1) Ja s'ha recuperat el draft local
        if ($this->params[PageKeys::KEY_RECOVER_LOCAL_DRAFT]) {
            $response = $this->_getLocalDraftResponse();

            // ALERTA[Xavi] Afegit per enviar el contingut actual i determinar si hi ha canvis a l'esborrany
            $response['content'] = $this->getModel()->getRawData()['content'];


        } else if($this->lockState()==ST_LOCKED_BEFORE){
            //-1 L'usuari te obert el document en una altra sessio
            $response = $this->_getSelfLockedDialog($this->getModel()->getRawData());
        } else
            // 2) Es demana recuperar el draft
            if ($this->params[PageKeys::KEY_RECOVER_DRAFT]) {
                $response = $this->_getDraftResponse();
                // ALERTA[Xavi] Afegit per enviar el contingut actual i determinar si hi ha canvis a l'esborrany
                $response['content'] = $this->getModel()->getRawData()['content'];
            } else { //
                $rawData = $this->getModel()->getRawData();
                $rawData["draftType"] = $this->_getDraftType($rawData["draftType"]);
                // 3) No hi ha draft
                if ($rawData["draftType"] == PageKeys::NO_DRAFT
                    || isset($this->params[PageKeys::KEY_RECOVER_DRAFT])
                ) {
                    $response = $this->_getRawDataContent($rawData);
                } else
                    //4) Hi ha draft però no hi ha bloqueig (!locked)
                    if ($rawData["draftType"] == PageKeys::FULL_DRAFT && !$rawData["locked"]) {
                        //Enviar diàleg
                        $response = $this->_getDraftDialog($rawData);
                    } else
                        //5) no hi ha bloqueig (!locked) i el draft local és més nou
                        if ($rawData["draftType"] == PageKeys::LOCAL_FULL_DRAFT && !$rawData["locked"]) {
                            //Enviar diàleg
                            $response = $this->_getLocalDraftDialog($rawData);
                        } else { // 6) Hi ha draft però el recurs està blquejat per un altre usuari
                            //No es pot editar. Cal esperar que s'acabi el bloqueig
                            $response = $this->_getWaitingUnlockDialog($rawData);
                        }
            }

        $response["lockInfo"] = $this->lockStruct["info"];

        // ALERTA: Control d'edició per revisions

        if ($response['rev']) {
            // ALERTA[Xavi] Les revisións no bloquejan el document. Per altra banda afegeixen un suffix al id per diferenciar-se del document original
            $response['id'] .= PageAction::REVISION_SUFFIX;
        } else {
            // ALERTA: Es bloqueja correctament??
            $response['info'] = $this->generateLockInfo($this->lockState(), $response['info']);
        }






        $this->addNotificationsMetaToResponse($response, $response['ns']);

        // Corregim els ids de les metas per indicar que és una revisió
        if ($response['rev']) {
            $this->addRevisionSuffixIdToArray($response['meta']);
        }

        return $response;
    }

    private function _cleanResponse($text)
    {

        $pattern = "/^(?:(?!<div class=\"editBox\").)*/s";// Captura tot el contingut abans del div que contindrá l'editor

        preg_match($pattern, $text, $match);
        $info = $match[0];

        $text = preg_replace($pattern, "", $text);

        // Eliminem les etiquetes no desitjades
        $pattern = "/<div id=\"size__ctl\".*?<\/div>\\s*/s";
        $text = preg_replace($pattern, "", $text);

        // Eliminem les etiquetes no desitjades
        $pattern = "/<div class=\"editButtons\".*?<\/div>\\s*/s";
        $text = preg_replace($pattern, "", $text);

        // Copiem el license
        $pattern = "/<div class=\"license\".*?<\/div>\\s*/s";
        preg_match($pattern, $text, $match);
        $license = $match[0];

        // Eliminem l'etiqueta
        $text = preg_replace($pattern, "", $text);

        //eliminem el text de la textarea
        $pattern = "/(<textarea.*?>)(.*?)(<\/textarea>)/s";
        $text = preg_replace($pattern, "$1$3", $text);

        // Copiem el wiki__editbar
        $pattern = "/<div id=\"wiki__editbar\".*?<\/div>\\s*<\/div>\\s*/s";
        preg_match($pattern, $text, $match);
        $meta = $match[0];

        // Eliminem la etiqueta
        $text = preg_replace($pattern, "", $text);

        // Capturem el id del formulari
        $pattern = "/<form id=\"(.*?)\"/";
        //$form = "dw__editform";
        preg_match($pattern, $text, $match);
        $form = $match[1];

        $pattern = "/<form id=\"" . $form . "\"/";
        $replace = "/<form id=\"form_" . $this->params[PageKeys::KEY_ID] . "\"/";
        $text = preg_replace($pattern, $replace, $text);

        // Afegim el id del formulari als inputs
        $pattern = "/<input/";
        $replace = "<input form=\"form_" . $this->params[PageKeys::KEY_ID] . "\"";
        $meta = preg_replace($pattern, $replace, $meta);

        // Netegem el valor
        $pattern = "/value=\"string\"/";
        $replace = "value=\"\"";
        $meta = preg_replace($pattern, $replace, $meta);

        $response["htmlForm"] = $text;
        $response["info"] = [$info];

        if ($license) {
            $response["info"][] = $license;
        }

        $metaId = str_replace(":", "_", $this->params[PageKeys::KEY_ID]) . '_metaEditForm';
        $response["meta"] = [
            ($this->getCommonPage($metaId,
                    WikiIocLangManager::getLang('metaEditForm'),
                    $meta) + ['type' => 'summary'])
        ];

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

//    public function leaveResource($unlock = FALSE)
//    {
//        throw new UnavailableMethodExecutionException('CancelEditPageAction#leaveResource');
//    }

    private function generateLockInfo($lockState, $mes)
    {
        $info = null;
        $infoType = 'message';
        $message = null;

        switch ($lockState) {

            case self::LOCKED:
                // El fitxer no estava bloquejat
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
        } else {
            $message = self::generateInfo($infoType, $message, $this->params[PageKeys::KEY_ID]);
        }
        return $message;
    }

    private function _getLocalDraftResponse()
    {
        if ($this->lockState() == self::REQUIRED) {
            //No ha de ser possible aquest cas. LLancem excepció si arriba aquí.
            throw new FileIsLockedException($this->params[PageKeys::KEY_ID]);
        }
        $resp = $this->_getBaseDataToSend();

        $resp["recover_local"] = true;

        $resp = array_merge($resp, $this->_getStructuredHtmlForm($this->getModel()->getRawData()["content"]));

        //ALERTA [Josep]: De moment cal retornar $resp[recover_local]=true, però cal valorar
        //si cal fer-ho així.
        $resp[PageKeys::KEY_RECOVER_LOCAL_DRAFT] = true;
        //$resp["meta"]
        $info = $this->generateInfo('warning', WikiIocLangManager::getLang('local_draft_editing'));

        if (array_key_exists('info', $resp)) {
            $resp['info'] = $this->addInfoToInfo($resp['info'], $info);
        }
        return $resp;
    }

    protected function lockState()
    {
        return $this->lockStruct["state"];
    }

    private function _getDraftResponse()
    {
        if (!$this->dokuPageModel->hasDraft()) {
            throw new DraftNotFoundException($this->params[PageKeys::KEY_ID]);
        }
        if ($this->lockState() == self::REQUIRED) {
            //No ha de ser possible aquest cas. LLancem excepció si arriba aquí.
            throw new FileIsLockedException($this->params[PageKeys::KEY_ID]);
        }

        $resp = $this->_getBaseDataToSend();

        $resp["draft"] = $this->dokuPageModel->getFullDraft();
        $resp = array_merge($resp, $this->_getStructuredHtmlForm($resp["draft"]["content"]));

        $resp["recover_draft"] = TRUE;

        $info = $this->generateInfo("warning", WikiIocLangManager::getLang('draft_editing'));

        if (array_key_exists('info', $resp)) {
            $info = $this->addInfoToInfo($resp['info'], $info);
        }

        $resp["info"] = $info;

        return $resp;
    }

    private function _getRawDataContent($rawData)
    {
        $resp = $this->_getBaseDataToSend();
        $resp = array_merge($resp, $this->_getStructuredHtmlForm($rawData["content"]));
        $resp["content"] = $rawData["content"];
        $resp["locked"] = $rawData["locked"];
        return $resp;
    }

    private function _getStructuredHtmlForm($ptext)
    {
        global $DATE;
        global $SUM;
        global $TEXT;

        $auxText = $TEXT;
        $TEXT = $ptext;
        $auxDate = $DATE;
        $DATE = $this->params[PageKeys::KEY_DATE];
        $auxSum = $SUM;
        $SUM = $this->params[PageKeys::KEY_SUM];
        ob_start();
        html_edit();
        $form = ob_get_clean();
        $TEXT = $auxText;
        $SUM = $auxSum;
        $DATE = $auxDate;
        return $this->_cleanResponse($form);
    }

    private function _getLocalDraftDialog($rawData)
    {
        $resp = $this->_getRawDataContent($rawData);
        $resp["type"] = "full_document";
        $resp["local"] = TRUE;
        $resp["lastmod"] = WikiPageSystemManager::extractDateFromRevision(WikiIocInfoManager::getInfo("lastmod"));
        $resp['show_draft_dialog'] = TRUE;

        return $resp;
    }

    private function _getDraftDialog($rawData)
    {
        $resp = $this->_getLocalDraftDialog($rawData);
        $resp["draft"] = $this->dokuPageModel->getFullDraft();
        $resp["local"] = FALSE;

        return $resp;
    }

    private function _getWaitingUnlockDialog($rawData)
    {
        $resp = $this->_getBaseDataToSend();
        //TODO [Josep] Cal implementar quan estigui fet el sistema de diàlegs al client.
        //Aquí caldrà avisar que no és possible editar l'esborrany perquè hi ha algú editant prèviament el document
        // i es podrien perdre dades. També caldrà demanar si vol que l'avisin quan acabi el bloqueig
        return $resp;
    }

    private function _getSelfLockedDialog($rawData)
    {
        $resp = $this->_getRawDataContent($rawData);
        $resp["locked_before"]=true;

        //TODO [Josep] Cal implementar quan estigui fet el sistema de diàlegs al client.
        //Aquí caldrà avisar que no és possible editar l'esborrany perquè hi ha algú editant prèviament el document
        // i es podrien perdre dades. També caldrà demanar si vol que l'avisin quan acabi el bloqueig
        return $resp;
    }

    private function _getBaseDataToSend()
    {
        $pageTitle = tpl_pagetitle($this->params[PageKeys::KEY_ID], TRUE);
        $id = $this->params[PageKeys::KEY_ID];
        $contentData = array(
            'id' => str_replace(":", "_", $id),
            'ns' => $id,
            'title' => $pageTitle,
            'rev' => $this->params[PageKeys::KEY_REV],
        );

        return $contentData;
    }

    private function _getDraftType($dt = PageKeys::NO_DRAFT) {
        if ($dt === PageKeys::NO_DRAFT && !$this->params[PageKeys::FULL_LAST_LOCAL_DRAFT_TIME]) {
            return PageKeys::NO_DRAFT;
        }
        $fullLastSavedDraftTime = $this->dokuPageModel->getFullDraftDate();
        $structuredLastSavedDraftTime = $this->dokuPageModel->getStructuredDraftDate();
//        $fullLastLocalDraftTime = intval(substr($this->params[PageKeys::FULL_LAST_LOCAL_DRAFT_TIME], 0, 10));
        $fullLastLocalDraftTime = $this->params[PageKeys::FULL_LAST_LOCAL_DRAFT_TIME];

        // Només pot existir un dels dos, i el draft que arriba aquí ja es el complet si existeix algun dels dos
        $savedDraftTime = max($fullLastSavedDraftTime, $structuredLastSavedDraftTime);

        if ($savedDraftTime > -1 && $fullLastLocalDraftTime < $savedDraftTime) {
            $ret = PageKeys::FULL_DRAFT;
        } else if ($fullLastLocalDraftTime > 0) {
            $ret = PageKeys::LOCAL_FULL_DRAFT;
        }
        return $ret;
    }
}
