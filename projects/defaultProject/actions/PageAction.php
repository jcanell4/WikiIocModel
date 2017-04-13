<?php

if (!defined("DOKU_INC")) {
    die();
}
if (!defined('DOKU_PLUGIN')) {
    define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
}

require_once DOKU_PLUGIN . "ownInit/WikiGlobalConfig.php";
require_once DOKU_PLUGIN . "wikiiocmodel/LockManager.php";
require_once DOKU_PLUGIN . "wikiiocmodel/persistence/WikiPageSystemManager.php";
require_once DOKU_PLUGIN . "wikiiocmodel/WikiIocLangManager.php";
require_once DOKU_PLUGIN . "wikiiocmodel/WikiIocInfoManager.php";
require_once DOKU_PLUGIN . "wikiiocmodel/projects/defaultProject/DokuAction.php";
require_once DOKU_PLUGIN . "wikiiocmodel/projects/defaultProject/datamodel/DokuPageModel.php";
require_once DOKU_PLUGIN . "ajaxcommand/requestparams/PageKeys.php";
require_once DOKU_PLUGIN . "wikiiocmodel/ResourceLocker.php";

/**
 * Description of PageAction
 *
 * @author josep
 */
abstract class PageAction extends DokuAction
{
    protected $dokuPageModel;
    protected $resourceLocker;
    protected $persistenceEngine;

    const REVISION_SUFFIX= '-rev-';

    public function __construct($persistenceEngine)
    {
        $this->persistenceEngine = $persistenceEngine;
        $this->dokuPageModel = new DokuPageModel($persistenceEngine);
        $this->resourceLocker = new ResourceLocker($this->persistenceEngine);
    }

    /** @override */
    public function get(/*Array*/
        $paramsArr = array())
    {
        $this->resourceLocker->init($paramsArr);
        return parent::get($paramsArr);

    }

    /**
     * És un mètode per sobrescriure. Per defecte no fa res, però la
     * sobrescriptura permet fer assignacions a les variables globals de la
     * wiki a partir dels valors de DokuAction#params.
     */
    protected function startProcess()
    {
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
            $TEXT = $this->params[PageKeys::KEY_TEXT] = $this->params['text'] = cleanText($this->params['text']);
        } elseif ($this->params[PageKeys::KEY_TEXT]) {
            $TEXT = $this->params[PageKeys::KEY_TEXT] = $this->params['text'] = cleanText($this->params[PageKeys::KEY_TEXT]);
        }
        if ($this->params[PageKeys::KEY_SUF]) {
            $SUF = $this->params[PageKeys::KEY_SUF] = cleanText($this->params[PageKeys::KEY_SUF]);
        }
        if ($this->params[PageKeys::KEY_SUM]) {
            $SUM = $this->params['sum'] = $this->params[PageKeys::KEY_SUM];
        }
//        $this->dokuPageModel->init($this->params[PageKeys::KEY_ID]);
        $this->dokuPageModel->init($this->params[PageKeys::KEY_ID], NULL, NULL, $this->params[PageKeys::KEY_REV]);
    }

    protected function getModel()
    {
        return $this->dokuPageModel;
    }

    public function addMetaTocResponse(&$response)
    {
//        $ret = array('id' => \str_replace(":", "_", $this->params[PageKeys::KEY_ID]));
//        if (!$meta) {
//            $meta = array();
//        }
        if(!isset($response['meta'])){
            $response['meta']=array();
        }
        $mEvt = new Doku_Event('WIOC_ADD_META', $response['meta']);
        if ($mEvt->advise_before()) {
            $toc = $this->getModel()->getMetaToc();
            $metaId = \str_replace(":", "_", $this->params[PageKeys::KEY_ID]) . '_toc';
            $response["meta"][] = ($this->getCommonPage($metaId, WikiIocLangManager::getLang('toc'), $toc) + ['type' => 'TOC']);
        }
        $mEvt->advise_after();
        unset($mEvt);
//        $ret['meta'] = $meta;
//
//        return $ret;
//        return $meta;
    }

    protected function getRevisionList()
    {
        $extra = array();
        $mEvt = new Doku_Event('WIOC_ADD_META_REVISION_LIST', $extra);
        if ($mEvt->advise_before()) {
            $ret = $this->getModel()->getRevisionList();
        }
        $mEvt->advise_after();
        unset($mEvt);
        return $ret;
    }

//    public function lock()
//    {
//
//        $pid = $this->params[PageKeys::KEY_ID];
//        $cleanId = WikiPageSystemManager::getContainerIdFromPageId($pid);
//
//        //$lockManager = new LockManager($this);
//        $lockManager = new LockManager();
//        $locker = $lockManager->lock($pid);
//
//        if ($locker === false) {
//
//            $info = $this->generateInfo('info', "S'ha refrescat el bloqueig"); // TODO[Xavi] Localitzar el missatge
//            $response = ['id' => $cleanId , 'timeout' => WikiGlobalConfig::getConf('locktime'), 'info' => $info];
//
//        } else {
//
//            $response = ['id' => $cleanId , 'timeout' => -1, 'info' => $this->generateInfo('error', WikiIocLangManager::getLang('lockedby') . ' ' . $locker)];
//        }
//
//        return $response;
//    }

//    public function unlock()
//    {
////        $lockManager = new LockManager();
////        $lockManager->unlock($this->params[PageKeys::KEY_ID]);
//        $this->resourceLocker->unlock();
//        
//        $info = $this->generateInfo('success', "S'ha alliberat el bloqueig");
//        $response['info'] = $info; // TODO[Xavi] Localitzar el missatge
//
//        return $response;
//    }

    public function checklock()
    {
        return $this->resourceLocker->checklock();
    }

    public function updateLock() {
        return $this->resourceLocker->updateLock();
    }

    protected function clearFullDraft()
    {
        WikiIocInfoManager::setInfo('draft', $this->getModel()->getDraftFileName());
        act_draftdel($this->params[PageKeys::KEY_DO]);

    }


    protected function clearPartialDraft()
    {
        $this->getModel()->removePartialDraft();
    }


    protected function addNotificationsMetaToResponse(&$response) {
        if(!isset($response['meta'])){
            $response['meta']=array();
        }
        $ns = isset($response['ns']) ? $response['ns'] : $response['structure']['ns'];
        $rev = isset($response['rev']) ? $response['rev'] : $response['structure']['rev'];
        
        $response['meta'][] = [
            "id" => $ns . "_metaNotifications",
            "title" => WikiIocLangManager::getLang('notification_form_title'),
            "content" => [
                'action' => 'lib/plugins/ajaxcommand/ajax.php',
                'method' => 'post',
                'fields' => [
//                    [
//                        'type' => 'hidden',
//                        'name' => 'sectok',
//                        'value' => getSecurityToken(),
//                    ],
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
//                    [
//                        'type' => 'text',
//                        'name' => 'to',
//                        'value' => '',
//                        'label' => WikiIocLangManager::getLang('notification_form_to'), // Optional
//                        'properties' => ['required'] // Optional
//                    ],
                    [

                        'type' => 'amd',
                        'data' => [
                            //ALERTA[Xavi] Dades de prova, això haurà d'arribar d'algun lloc!
                            'data' =>[
                                ['name' => 'Xavier Garcia', 'username' => 'admin'],
                                ['name' => 'Josep Cañellas', 'username' => 'admin2'],
                                ['name' => 'Joan Ramon', 'username' => 'aaa'],
                                ['name' => 'Alicia Vila', 'username' => 'bbb'],
                                ['name' => 'Josep LLadonosa', 'username' => 'ccc'],
                            ],
                            'buttonLabel' => WikiIocLangManager::getLang('search'),
                            'fieldName' => 'to',
                            'searchDataUrl' => ''
                        ],
                        'class' => 'IocFilteredList',
//                        'name' => 'to',
//                        'value' => '',
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
        
//        return $response['meta'];
    }

    protected function addRevisionSuffixIdToArray(&$elements) {
        for ($i=0, $len = count($elements); $i<$len; $i++) {

            if ($elements[$i]['id'] && substr($elements[$i]['id'], -5) != self::REVISION_SUFFIX) {
                $elements[$i]['id'] .= self::REVISION_SUFFIX;
            }
        }
    }



}
