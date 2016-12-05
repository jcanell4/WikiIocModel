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
require_once DOKU_PLUGIN . "wikiiocmodel/persistence/WikiPageSystemManager.php";
require_once DOKU_PLUGIN . "ajaxcommand/requestparams/PageKeys.php";
require_once DOKU_PLUGIN . "wikiiocmodel/ResourceUnlockerInterface.php";
require_once DOKU_PLUGIN . "wikiiocmodel/ResourceLockerInterface.php";

if (!defined('DW_ACT_SAVEDRAFT')) define('DW_ACT_SAVEDRAFT', "preview");
if (!defined('DW_ACT_DELDRAFT')) define('DW_ACT_DELDRAFT', "draftdel");

/**
 * Description of RawPageAction
 *
 * @author josep
 */
class DraftPageAction extends PageAction
{
    protected $engine;
    private $response;

    public function __construct(/*BasicPersistenceEngine*/ $engine)
    {
        parent::__construct($engine);
        //$this->draftQuery = $engine->createDraftDataQuery();
        $this->defaultDo = DW_ACT_SAVEDRAFT;
        $this->engine = $engine;
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
            throw new PageNotFoundException($this->params[PageKeys::KEY_ID], 'pageNotFound');
        }

        $ACT = act_permcheck($this->defaultDo);

        if ($ACT == DW_ACT_DENIED) {
            throw new InsufficientPermissionToEditPageException($this->params[PageKeys::KEY_ID]);
        }
        
        if($this->checklock()==ST_LOCKED){        
            throw new FileIsLockedException($this->params[PageKeys::KEY_ID]);
        }
        
        if($this->params[PageKeys::KEY_DO]===DW_ACT_SAVEDRAFT){
            $lockInfo = $this->updateLock()["info"];
            $draft =json_decode($this->params['draft'], true);
            $this->response = DraftManager::saveDraft($draft);// TODO[Xavi] Això hurà de contenir la info
            $this->response['id'] = str_replace(":", "_", $this->params[PageKeys::KEY_ID]);
            $this->response["lockInfo"] = $lockInfo;
        }else if($this->params[PageKeys::KEY_DO]===DW_ACT_DELDRAFT){
            $this->response = DraftManager::removeDraft($this->params);// TODO[Xavi] Això hurà de contenir la info
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
