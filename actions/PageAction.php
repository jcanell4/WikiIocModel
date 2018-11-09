<?php
/**
 * Description of PageAction
 * @author josep
 */
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');

require_once DOKU_PLUGIN . "wikiiocmodel/LockManager.php";
require_once DOKU_PLUGIN . "wikiiocmodel/authorization/PagePermissionManager.php";

abstract class PageAction extends DokuAction implements ResourceLockerInterface,ResourceUnlockerInterface {
    protected $dokuPageModel;
    protected $resourceLocker;
    protected $persistenceEngine;

    const REVISION_SUFFIX= '-rev-';



    public function init($modelManager) {
        parent::init($modelManager);
        $this->persistenceEngine = $modelManager->getPersistenceEngine();
        $this->dokuPageModel = $this->instantiateModel();
//        $this->dokuPageModel = new DokuPageModel($this->persistenceEngine);
        $this->resourceLocker = new ResourceLocker($this->persistenceEngine);
    }

    protected function instantiateModel() {
        switch ($this->params['format']){
            case 'html':
                return new HtmlPageModel($this->persistenceEngine);
            default:
                return new DokuPageModel($this->persistenceEngine);
        }
    }


    /** @override */
    public function get($paramsArr = array()) {
        $this->resourceLocker->init($paramsArr);
        return parent::get($paramsArr);
    }

    /**
     * És un mètode per sobrescriure. Per defecte no fa res, però la
     * sobrescriptura permet fer assignacions a les variables globals de la
     * wiki a partir dels valors de DokuAction#params.
     */
    protected function startProcess() {
        global $ID;
        global $ACT;
        global $REV;
        global $RANGE;
        global $DATE;
        global $PRE;
        global $TEXT;
        global $SUF;
        global $SUM;

        $ACT = $this->params[PageKeys::KEY_DO] = $this->defaultDo;
        $ACT = act_clean($ACT);

        if (!$this->params[PageKeys::KEY_ID]) {
            $this->params[PageKeys::KEY_ID] = WikiGlobalConfig::getConf(DW_DEFAULT_PAGE);
        }
        $ID = $this->params[PageKeys::KEY_ID];
        if ($this->params[PageKeys::KEY_REV]) {
            $REV = $this->params[PageKeys::KEY_REV];
        }
        if ($this->params[PageKeys::KEY_RANGE]) {
            $RANGE = $this->params[PageKeys::KEY_RANGE];
        }
        if ($this->params[PageKeys::KEY_DATE]) {
            $DATE = $this->params[PageKeys::KEY_DATE];
        }
        if ($this->params[PageKeys::KEY_PRE]) {
            $PRE = $this->params[PageKeys::KEY_PRE]
                = cleanText(substr($this->params[PageKeys::KEY_PRE], 0, -1));
        }
        if ($this->params['text']) {
            $TEXT = $this->params[PageKeys::KEY_WIKITEXT] = $this->params['text'] = cleanText($this->params['text']);
        } elseif ($this->params[PageKeys::KEY_WIKITEXT]) {
            $TEXT = $this->params[PageKeys::KEY_WIKITEXT] = $this->params['text'] = cleanText($this->params[PageKeys::KEY_WIKITEXT]);
        }
        if ($this->params[PageKeys::KEY_SUF]) {
            $SUF = $this->params[PageKeys::KEY_SUF] = cleanText($this->params[PageKeys::KEY_SUF]);
        }
        if ($this->params[PageKeys::KEY_SUM]) {
            $SUM = $this->params['sum'] = $this->params[PageKeys::KEY_SUM];
        }
        $this->dokuPageModel->init($this->params[PageKeys::KEY_ID], NULL, NULL, $this->params[PageKeys::KEY_REV]);
    }

    protected function getModel() {
        return $this->dokuPageModel;
    }

    public function addMetaTocResponse(&$response) {
        if (!isset($response['meta'])) {
            $response['meta'] = array();
        }
        $mEvt = new Doku_Event('WIOC_ADD_META', $response['meta']);
        if ($mEvt->advise_before()) {
            $toc = $this->getModel()->getMetaToc();
            $metaId = \str_replace(":", "_", $this->params[PageKeys::KEY_ID]) . '_toc';
            $response["meta"][] = ($this->getCommonPage($metaId, WikiIocLangManager::getLang('toc'), $toc) + ['type' => 'TOC']);
        }
        $mEvt->advise_after();
        unset($mEvt);
    }

    protected function getRevisionList() {
        $extra = array();
        $mEvt = new Doku_Event('WIOC_ADD_META_REVISION_LIST', $extra);
        if ($mEvt->advise_before()) {
            $ret = $this->getModel()->getRevisionList($this->params[PageKeys::KEY_OFFSET]);
        }
        $mEvt->advise_after();
        unset($mEvt);
        return $ret;
    }

    public function checklock() {
        return $this->resourceLocker->checklock();
    }

    public function updateLock() {
        return $this->resourceLocker->updateLock();
    }

    protected function clearFullDraft() {
        WikiIocInfoManager::setInfo('draft', $this->getModel()->getDraftFileName());
        act_draftdel($this->params[PageKeys::KEY_DO]);
    }

    protected function clearPartialDraft() {
        $this->getModel()->removePartialDraft();
    }


    private function generateUsernameNamePair(&$list) {
        global $auth;
        $newList = [];

        foreach ($list as $username) {
            $name = $auth->getUserData($username)['name'];
            $newList[] = ['username' => $username, 'name' => $name===null?"":$name];
        }
        return $newList;
    }

    protected function addNotificationsMetaToResponse(&$response) {
        if (!isset($response['meta'])) {
            $response['meta'] = array();
        }
        $ns = isset($response['ns']) ? $response['ns'] : $response['structure']['ns'];
        $rev = isset($response['rev']) ? $response['rev'] : $response['structure']['rev'];

        $list = PagePermissionManager::getListUsersPagePermission($ns, AUTH_EDIT);
        $list = $this->generateUsernameNamePair($list);

        $response['meta'][] = [
            "id" => $ns . "_metaNotifications",
            "title" => WikiIocLangManager::getLang('notification_form_title'),
            "content" => [
                'action' => 'lib/exe/ioc_ajax.php',
                'method' => 'post',
                'fields' => [
                    [
                        'type' => 'hidden',
                        'name' => 'call',
                        'value' => 'notify',
                    ],
                    [
                        'type' => 'hidden',
                        'name' => 'do',
                        'value' => 'add_message',
                    ],
                    [
                        'type' => 'hidden',
                        'name' => 'type',
                        'value' => 'warning',
                    ],
                    [
                        'type' => 'amd',
                        'data' => [
                            //ALERTA[Xavi] Dades de prova, això haurà d'arribar d'algun lloc!
                            'ns' => $ns,
                            'data' => $list,
                            'buttonLabel' => WikiIocLangManager::getLang('search'),
                            'fieldName' => 'to',
                            'searchDataUrl' => 'lib/exe/ioc_ajax.php?call=user_list',
                            'token' => getSecurityToken()
                        ],
                        'class' => 'IocFilteredList',
                        'label' => WikiIocLangManager::getLang('notification_form_to'), // Optional
                    ],
                    [
                        'type' => 'checkbox',
                        'name' => 'id',
                        'value' => $ns,
                        'label' => sprintf(WikiIocLangManager::getLang('notification_form_check_add_id'), $response['id']), // Optional
                        'properties' => ['checked'] // Optional
                    ],
                    [
                        'type' => 'hidden',
                        'name' => 'rev',
                        'value' => $rev,
                    ],
                    [
                        'type' => 'checkbox',
                        'name' => 'send_email',
                        'value' => true,
                        'label' => WikiIocLangManager::getLang('notification_form_check_add_email'), // Optional
                        'properties' => ['checked'] // Optional
                    ],
                    [
                        'type' => 'textarea',
                        'name' => 'message',
                        'value' => '',
                        'label' => WikiIocLangManager::getLang('notification_form_message'), // Optional
                        'properties' => ['required'] // Optional
                    ]

                ],
                'send_button' => WikiIocLangManager::getLang('notification_form_button_send')
            ],

            "type" => "request_form" // aixó no se si es necessari
        ];

    }

    protected function addRevisionSuffixIdToArray(&$elements) {
        for ($i=0, $len = count($elements); $i<$len; $i++) {

            if ($elements[$i]['id'] && substr($elements[$i]['id'], -5) != self::REVISION_SUFFIX) {
                $elements[$i]['id'] .= self::REVISION_SUFFIX;
            }
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
    public function requireResource($lock = FALSE) {
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
    public function leaveResource($unlock = FALSE) {
        return $this->resourceLocker->leaveResource($unlock);
    }

}
