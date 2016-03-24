<?php

if (!defined("DOKU_INC")) {
    die();
}
if (!defined('DOKU_PLUGIN')) {
    define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
}

require_once DOKU_PLUGIN."ownInit/WikiGlobalConfig.php";
require_once DOKU_PLUGIN."wikiiocmodel/LockManager.php";
require_once DOKU_PLUGIN."wikiiocmodel/persistence/WikiPageSystemManager.php";
require_once DOKU_PLUGIN."wikiiocmodel/WikiIocLangManager.php";
require_once DOKU_PLUGIN."wikiiocmodel/WikiIocInfoManager.php";
require_once DOKU_PLUGIN."wikiiocmodel/projects/defaultProject/DokuAction.php";
require_once DOKU_PLUGIN."wikiiocmodel/projects/defaultProject/datamodel/DokuPageModel.php";
require_once DOKU_PLUGIN."ajaxcommand/requestparams/PageKeys.php";

/**
 * Description of PageAction
 *
 * @author josep
 */
abstract class PageAction extends DokuAction {
    protected $dokuPageModel;
    
    public function __construct($persistenceEngine) {
        $this->dokuPageModel = new DokuPageModel($persistenceEngine);
    }
    
    /**
     * És un mètode per sobrescriure. Per defecte no fa res, però la 
     * sobrescriptura permet fer assignacions a les variables globals de la 
     * wiki a partir dels valors de DokuAction#params.
     */
    protected function startProcess(){        
        global $ID;
        global $ACT;
        global $REV;
        global $RANGE;
        global $DATE;
        global $PRE;
        global $TEXT;
        global $SUF;
        global $SUM;

        $ACT = $this->params[PageKeys::KEY_DO]=  $this->defaultDo;
        $ACT = act_clean( $ACT );

        if ( ! $this->params[PageKeys::KEY_ID] ) {
                $this->params[PageKeys::KEY_ID] = WikiGlobalConfig::getConf(DW_DEFAULT_PAGE);
        }
        $ID = $this->params[PageKeys::KEY_ID];
        if ( $this->params[PageKeys::KEY_REV] ) {
                $REV = $this->params[PageKeys::KEY_REV];
        }
        if ($this->params[PageKeys::KEY_RANGE]) {
                $RANGE = $this->params[PageKeys::KEY_RANGE];
        }
        if ( $this->params[PageKeys::KEY_DATE] ) {
                $DATE = $this->params[PageKeys::KEY_DATE];
        }
        if ( $this->params[PageKeys::KEY_PRE] ) {
                $PRE = $this->params[PageKeys::KEY_PRE] 
                                    = cleanText( substr( $this->params[PageKeys::KEY_PRE], 0, - 1 ) );
        }
        if ( $this->params['text'] ) {
                $TEXT = $this->params[PageKeys::KEY_TEXT] = $this->params['text'] = cleanText( $this->params['text']  );
        }elseif($this->params[PageKeys::KEY_TEXT]){
            $TEXT = $this->params[PageKeys::KEY_TEXT] = $this->params['text'] = cleanText( $this->params[PageKeys::KEY_TEXT]  );
        }
        if ( $this->params[PageKeys::KEY_SUF] ) {
                $SUF = $this->params[PageKeys::KEY_SUF] = cleanText( $this->params[PageKeys::KEY_SUF]  );
        }
        if ( $this->params[PageKeys::KEY_SUM] ) {
                $SUM = $this->params['sum'] = $this->params[PageKeys::KEY_SUM];
        }  
        $this->dokuPageModel->init($this->params[PageKeys::KEY_ID]);                
    }
    
    protected function getModel(){
        return $this->dokuPageModel;
    }

    public function getMetaTocResponse($meta=NULL){
        $ret = array('id' => \str_replace(":", "_", $this->params[PageKeys::KEY_ID]));
        if(!$meta){
            $meta = array();
        }
        $mEvt = new Doku_Event('WIOC_ADD_META', $meta);
        if ($mEvt->advise_before()) {
            $toc = $this->getModel()->getMetaToc();
            $metaId = \str_replace(":", "_", $this->params[PageKeys::KEY_ID]) . '_toc';
            $meta[] = ($this->getCommonPage($metaId,  WikiIocLangManager::getLang('toc'), $toc) + ['type' => 'TOC']);
        }
        $mEvt->advise_after();
        unset($mEvt);
        $ret['meta'] = $meta;

        return $ret;
    }

    protected function getRevisionList(){
        $extra =array();
        $mEvt = new Doku_Event('WIOC_ADD_META_REVISION_LIST', $extra);
        if ($mEvt->advise_before()) {
            $ret = $this->getModel()->getRevisionList();
        }
        $mEvt->advise_after();
        unset($mEvt);
        return $ret;        
    }

     public function lock()
    {
        $pid = WikiPageSystemManager::cleanIDForFiles($this->params[PageKeys::KEY_ID]);
        //$lockManager = new LockManager($this);
        $lockManager = new LockManager();
        $locker = $lockManager->lock($pid);

        if ($locker === false) {

            $info = $this->generateInfo('info', "S'ha refrescat el bloqueig"); // TODO[Xavi] Localitzar el missatge
            $response = ['id' => $pid, 'timeout' => WikiGlobalConfig::getConf('locktime'), 'info' => $info];

        } else {

            $response = ['id' => $pid, 'timeout' => -1, 'info' => $this->generateInfo('error', WikiIocLangManager::getLang('lockedby') . ' ' . $locker)];
        }

        return $response;
    }

    public function unlock()
    {
        //$lockManager = new LockManager($this);
        $lockManager = new LockManager();
        //$lockManager->unlock($this->cleanIDForFiles($pid));
        $lockManager->unlock(WikiPageSystemManager::cleanIDForFiles($this->params[PageKeys::KEY_ID]));
        
        $info = $this->generateInfo('success', "S'ha alliberat el bloqueig");
        $response['info'] = $info; // TODO[Xavi] Localitzar el missatge

        return $response;
    }

    public function checklock($pid)
    {
        //[ALERTA JOSEP] Cal passar checklock a LockDataQuery i fer la crida des d'allà
        return checklock(WikiPageSystemManager::cleanIDForFiles($this->params[PageKeys::KEY_ID]));
    }


//put your code here
}