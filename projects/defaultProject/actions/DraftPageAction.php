<?php
/**
 * Description of DraftPageAction
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
require_once DOKU_PLUGIN . "wikiiocmodel/persistence/WikiPageSystemManager.php";

if (!defined('DW_ACT_SAVEDRAFT')) define('DW_ACT_SAVEDRAFT', "preview");
if (!defined('DW_ACT_DELDRAFT')) define('DW_ACT_DELDRAFT', "draftdel");

class DraftPageAction extends PageAction {
    private static $infoDuration = 15;

    public function __construct(BasicPersistenceEngine $engine) {
        parent::__construct($engine);
        //$this->draftQuery = $engine->createDraftDataQuery();
        $this->defaultDo = DW_ACT_SAVEDRAFT;
    }

    protected function startProcess() {
        if($this->params[PageKeys::KEY_DO]===DW_ACT_SAVEDRAFT ||
                $this->params[PageKeys::KEY_DO]==="save"){
            $this->defaultDo = DW_ACT_SAVEDRAFT;
        }else if($this->params[PageKeys::KEY_DO]===DW_ACT_DELDRAFT ||
                $this->params[PageKeys::KEY_DO]==="remove"){
            $this->defaultDo = DW_ACT_DELDRAFT;
        }
        parent::startProcess();
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

        $ACT = act_permcheck($this->defaultDo);

        if ($ACT == DW_ACT_DENIED) {
            throw new InsufficientPermissionToEditPageException($this->params[PageKeys::KEY_ID]);
        }
        
        if($this->checklock()== LockDataQuery::LOCKED){
            throw new FileIsLockedException($this->params[PageKeys::KEY_ID]);
        }

        if($this->params[PageKeys::KEY_DO]===DW_ACT_SAVEDRAFT){
            $lockInfo = $this->updateLock()["info"];
            $draft =json_decode($this->params['draft'], true);
            $draft['date'] = $this->params['date'];
            //$this->response = DraftManager::saveDraft($draft);// TODO[Xavi] Això hurà de contenir la info
            $this->getModel()->saveDraft($draft);
            $this->response['id'] = str_replace(":", "_", $this->params[PageKeys::KEY_ID]);
            if($draft['type']==="full"){
                $this->response = ['info' => self::generateInfo('info', 'Desat esborrany complet', $this->response['id'], self::$infoDuration)];//TODO [Josep] internacionalitzar!
            }else{
                $this->response = ['info' => self::generateInfo('info', 'Desat esborrany parcial', $this->response['id'], self::$infoDuration)];//TODO [Josep] internacionalitzar!
            }
            $this->response["lockInfo"] = $lockInfo;
        }else if($this->params[PageKeys::KEY_DO]===DW_ACT_DELDRAFT){
            $this->getModel()->removeDraft($this->params);
            $this->response['id'] = str_replace(":", "_", $this->params[PageKeys::KEY_ID]);
        }else{
            throw new UnexpectedValueException("Unexpected value '".$this->params["do"]."', for parameter 'do'");
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

        return $this->response;
    }
}
