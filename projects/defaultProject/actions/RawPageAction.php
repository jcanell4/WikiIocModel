<?php


if (!defined("DOKU_INC")) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');

require_once(DOKU_INC . 'inc/common.php');
require_once(DOKU_INC . 'inc/actions.php');
require_once(DOKU_INC . 'inc/template.php');
require_once DOKU_PLUGIN . "ownInit/WikiGlobalConfig.php";
require_once DOKU_PLUGIN . "wikiiocmodel/WikiIocInfoManager.php";
require_once DOKU_PLUGIN . "wikiiocmodel/WikiIocLangManager.php";
require_once DOKU_PLUGIN . "wikiiocmodel/projects/defaultProject/actions/PageAction.php";
require_once DOKU_PLUGIN . "wikiiocmodel/projects/defaultProject/DokuModelExceptions.php";
require_once DOKU_PLUGIN . "ajaxcommand/requestparams/PageKeys.php";
require_once DOKU_PLUGIN . "wikiiocmodel/ResourceUnlockerInterface.php";
require_once DOKU_PLUGIN . "wikiiocmodel/ResourceLockerInterface.php";

if (!defined('DW_ACT_EDIT')) define('DW_ACT_EDIT', "edit");
if (!defined('DW_ACT_DENIED')) define('DW_ACT_DENIED', "denied");
if (!defined('DW_DEFAULT_PAGE')) define('DW_DEFAULT_PAGE', "start");

/**
 * Description of RawPageAction
 *
 * @author josep
 */
class RawPageAction extends PageAction implements ResourceLockerInterface, ResourceUnlockerInterface
{
    //protected $draftQuery;

    protected $engine;

    public function __construct(/*BasicPersistenceEngine*/
        $engine)
    {
        parent::__construct($engine);
        //$this->draftQuery = $engine->createDraftDataQuery();
        $this->defaultDo = DW_ACT_EDIT;
        $this->engine = $engine;

    }

    /**
     * És un mètode per sobrescriure. Per defecte no fa res, però la
     * sobrescriptura permet processar l'acció i emmagatzemar totes aquelles
     * dades  intermèdies que siguin necessàries per generar la resposta final:
     * DokuAction#responseProcess.
     */
    protected function runProcess()
    {
        global $ACT;
        global $ID;

        if (!WikiIocInfoManager::getInfo(WikiIocInfoManager::KEY_EXISTS)) {
            throw new PageNotFoundException($ID, WikiIocLangManager::getLang('pageNotFound'));
        }

        $ACT = act_edit($ACT);
        $ACT = act_permcheck($ACT);

        if ($ACT == DW_ACT_DENIED) {
            throw new InsufficientPermissionToEditPageException($ID);
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

        $pageToSend = $this->cleanResponse($this->_getCodePage());

        $resp = $this->getContentPage($pageToSend["content"]);
        $resp['meta'] = $pageToSend['meta'];


        // ALERTA[Xavi] Nova gestió del lock
        $resp[PageKeys::KEY_LOCK_STATE] = $this->requireResource();

        $resp['info'] = $this->generateLockInfo($resp[PageKeys::KEY_LOCK_STATE], $pageToSend['info']);


//        $infoType = 'info';
//        if ($resp[PageKeys::KEY_LOCK_STATE]===100) {// Substituida la coprovació pel nou sistema, 200 es l'estat bloquejat
//            $infoType = 'error';
//            $pageToSend['info'] = WikiIocLangManager::getLang('lockedby') . ' ' . WikiIocInfoManager::getInfo(WikiIocInfoManager::KEY_LOCKED);
//        }
//        $resp['info'] = self::generateInfo($infoType, $pageToSend['info']);
//        $resp[WikiIocInfoManager::KEY_LOCKED] = WikiIocInfoManager::getInfo(WikiIocInfoManager::KEY_LOCKED);




        // només tenim en compte el temps del full draft local, perquè no es pot reconstruir el document localment

        $fullLastSavedDraftTime = $this->dokuPageModel->getFullDraftDate();
        $structuredLastSavedDraftTime = $this->dokuPageModel->getStructuredDraftDate();
        $fullLastLocalDraftTime = intval(substr($this->params[PageKeys::FULL_LAST_LOCAL_DRAFT_TIME], 0, 10));

        // Només pot existir un dels dos, i el draft que arriba aquí ja es el complet si existeix algun dels dos
        $savedDraftTime = max($fullLastSavedDraftTime, $structuredLastSavedDraftTime);

        if ($savedDraftTime > -1 && $fullLastLocalDraftTime < $savedDraftTime) {
            // El desat es més recent, no cal fer res
            $resp['draftType'] = DokuPageModel::FULL_DRAFT; // ALERTA[Xavi] El valor no es fa servir per a res en especial
        } else if ($fullLastLocalDraftTime > 0) {
            $resp['local'] = true;
            $resp['draftType'] = DokuPageModel::LOCAL_FULL_DRAFT;  // ALERTA[Xavi] El valor no es fa servir per a res en especial
        }


        if ($this->params[PageKeys::KEY_RECOVER_LOCAL_DRAFT] === 'true') {

            $resp[PageKeys::KEY_RECOVER_LOCAL_DRAFT] = true;
            $resp['info'] = $this->generateInfo('warning', WikiIocLangManager::getLang('local_draft_editing'));

        } else if ($this->params[PageKeys::KEY_RECOVER_DRAFT] != NULL) {

            // S'ha seleccionat si volem recuperar o no l'esborrany
            $resp['recover_draft'] = $this->params[PageKeys::KEY_RECOVER_DRAFT];


            if ($this->params[PageKeys::KEY_RECOVER_DRAFT] == 'true') {
                $info = $this->generateInfo("warning", WikiIocLangManager::getLang('draft_editing'));

                if (array_key_exists('info', $resp)) {
                    $info = $this->addInfoToInfo($resp['info'], $info);
                }

                $resp["info"] = $info;
            }

        } else if (isset($resp['draftType'])) {
            // Mostrar el dialog que es mostrava al command
//            $this->getModel()->getDraftDialog($this->params);
            $resp['show_draft_dialog'] = TRUE;
        }

        return $resp;
    }

    protected function getContentPage($pageToSend)
    {
        global $REV;

        $pageTitle = tpl_pagetitle($this->params[PageKeys::KEY_ID], TRUE);

        $pattern = '/^.*Aquesta és una revisió.*<hr \/>\\n\\n/mis';
        $count = 0;
        $info = NULL;
        $pageToSend = preg_replace($pattern, '', $pageToSend, -1, $count);

        if ($count > 0) {
            $info = self::generateInfo("warning",
                WikiIocLangManager::getLang('document_revision_loaded')
                . ' <b>' . WikiPageSystemManager::extractDateFromRevision($REV, self::$SHORT_FORMAT) . '</b>' // TODO[Xavi] aquesta constant ja no existeix
                , $this->params[PageKeys::KEY_ID]);
        }

        $id = $this->params[PageKeys::KEY_ID];
        $contentData = array(
            'id' => str_replace(":", "_", $id),
            'ns' => $id,
            'title' => $pageTitle,
            'content' => $pageToSend,
            'rev' => $REV,
            'info' => $info,
            'type' => 'html',
            'draft' => $this->getModel()->getDraftAsFull()
        );

        return $contentData;
    }

    private function cleanResponse($text)
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

        $response["content"] = $text;
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

    private function _getCodePage()
    {
        global $ACT;
        ob_start();
        trigger_event('TPL_ACT_RENDER', $ACT, array($this, 'onCodeRender'));
        $html_output = ob_get_clean();
        ob_start();
        trigger_event('TPL_CONTENT_DISPLAY', $html_output, 'ptln');
        $html_output = ob_get_clean();

        return $html_output;
    }

    /**
     * Segons el valor de $data activa la edició del document('edit' i 'recover'), la previsualització ('preview') o mostra
     * el missatge de denegat ('denied').
     *
     * @param string $data els valors admessos son 'edit', 'recover', 'preview' i 'denied'
     */
    function onCodeRender($data)
    {
        global $TEXT;

        switch ($data) {
            case WikiIocInfoManager::KEY_LOCKED:
            case 'edit':
            case 'recover':
                html_edit();
                break;
            case 'preview':
                html_edit();
                html_show($TEXT);
                break;
            case 'denied':
                print p_locale_xhtml('denied');
                break;
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
        return $this->resourceLocker->requireResource($lock);
    }

    public function leaveResource($unlock = FALSE)
    {
        throw new UnavailableMethodExecutionException('CancelEditPageAction#leaveResource');
    }

    private function generateLockInfo($lockState, $message)
    {
        $info = null;
        $infoType = 'message';

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
        return self::generateInfo($infoType, $message);
    }
}
