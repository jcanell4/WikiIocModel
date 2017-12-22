<?php
/**
 * Description of SavePageAction
 * @author Xavier García <xaviergaro.dev@gmail.com>
 */
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');

require_once(DOKU_INC . 'inc/common.php');
require_once(DOKU_INC . 'inc/actions.php');
require_once(DOKU_INC . 'inc/template.php');
require_once DOKU_PLUGIN . "wikiiocmodel/projects/defaultProject/DokuAction.php";
require_once DOKU_PLUGIN . "wikiiocmodel/projects/defaultProject/DokuModelExceptions.php";
require_once DOKU_PLUGIN . "ajaxcommand/defkeys/PageKeys.php";

class SavePageAction extends RawPageAction {

    protected $deleted = FALSE;
    private $code = 0;
    protected $subAction;

    public function __construct(BasicPersistenceEngine $engine) {
        parent::__construct($engine);
        $this->defaultDo = PageKeys::DW_ACT_SAVE;
    }

    protected function startProcess(){
        $this->subAction = $this->params[PageKeys::KEY_DO];
        parent::startProcess();

        // ALERTA[Xavi] Alguns dels params passats no es troben al $this->params
        if (isset($_REQUEST['keep_draft'])) {
            $this->params['keep_draft'] = $_REQUEST['keep_draft']==="true";
        }
    }

    protected function runProcess(){
        global $ID, $ACT;

        $ID = $this->params[PageKeys::KEY_ID];
        if ($this->params[PageKeys::KEY_DO]===PageKeys::DW_ACT_SAVE && !WikiIocInfoManager::getInfo("exists")) {
            throw new PageNotFoundException($ID);
        }

        $ACT = act_permcheck($ACT);
        if ($ACT==="denied"){
            throw new InsufficientPermissionToCreatePageException($ID);
        }

        //Tal vez hay que obtener los permisos de otro sitio
        if ($this->isEmptyText($this->params) && WikiIocInfoManager::getInfo("perm") < AUTH_DELETE) {
            throw new InsufficientPermissionToDeletePageException($ID);
        }

        if($this->checklock()== LockDataQuery::LOCKED){
            throw new FileIsLockedException($this->params[PageKeys::KEY_ID]);
        }

        $this->lockStruct = $this->updateLock();
        if ($this->lockState() === self::LOCKED){
            $this->_save();
            if ($this->subAction==='save_rev' || $this->deleted){
                $this->resourceLocker->leaveResource(TRUE);
            }
        }
    }

    protected function responseProcess() {
        global $TEXT;
        global $ID;

        $suffix = $this->params[PageKeys::KEY_REV] ? PageAction::REVISION_SUFFIX : '';

        $response['code'] = $this->code;

        if ($this->deleted) {
            $response['deleted'] = TRUE;
            $type = 'success';
            $message = sprintf(WikiIocLangManager::getLang('deleted'), $this->params[PageKeys::KEY_ID]);
            $duration = NULL;

        }
        else {
            $message = WikiIocLangManager::getLang('saved');

            if ($this->params[PageKeys::KEY_CANCEL_ALL] || $this->params[PageKeys::KEY_CANCEL]) {

                $response['code'] = "cancel_document";
                $response['cancel_params'] = [
                    'id' => str_replace(":", "_", $this->params[PageKeys::KEY_ID]),
                    'dataToSend'  => ['discardChanges' => true],
                    'event' => 'cancel'];
                $response['cancel_params']['event'] = 'cancel';

                if ($this->params['close']) {
                    $response['cancel_params']['dataToSend']['close'] =$this->params['close'];
                    $response['cancel_params']['dataToSend']['no_response'] = true;
                }


                if (isset($this->params['keep_draft'])) {
                    $response['cancel_params']['dataToSend']['keep_draft'] = $this->params['keep_draft'];
                }

            } else if ($this->params[PageKeys::KEY_REV]) {

                if ($this->params[PageKeys::KEY_RELOAD]) {
                    $response['reload']['id'] = $ID;
                    $response['reload']['call'] = 'edit';
                } else {
                    $response['reload']['id'] = $ID;
                    $response['reload']['call'] = 'page';
                }
            } else {
                $response['formId'] = 'form_' . WikiPageSystemManager::getContainerIdFromPageId($ID) . $suffix;
                $response['inputs'] = ['date' => WikiIocInfoManager::getInfo("meta")["date"]["modified"],
                                       PageKeys::CHANGE_CHECK => md5($TEXT)];
            }

            $type = 'success';
            $duration = 15;
        }

        $response["lockInfo"] = $this->lockStruct["info"];
        $id = $response['id'] = WikiPageSystemManager::getContainerIdFromPageId($this->params[PageKeys::KEY_ID]) . $suffix;
        $response['info'] = $this->generateInfo($type, $message, $id . $suffix, $duration);

        return $response;
    }

    private function _save(){
        //spam check
        if (checkwordblock()) {
            throw new WordBlockedException();
        }
        //conflict check
        if ($this->subAction !== 'save_rev' // ALERTA[Xavi] els revert ignoren la data del document
            && $this->params[PageKeys::KEY_DATE] != 0
            && WikiIocInfoManager::getInfo("meta")["date"]["modified"] > $this->params[PageKeys::KEY_DATE] ){
            //return 'conflict';
            throw new DateConflictSavingException();
        }

        //save it
        $this->dokuPageModel->setData(array(
                                        PageKeys::KEY_WIKITEXT => con($this->params[PageKeys::KEY_PRE],
                                                                      $this->params[PageKeys::KEY_WIKITEXT],
                                                                      $this->params[PageKeys::KEY_SUF], 1),
                                        PageKeys::KEY_SUM      => $this->params[PageKeys::KEY_SUM],
                                        PageKeys::KEY_MINOR    => $this->params[PageKeys::KEY_MINOR])
                                     );

        //delete draft
        $this->dokuPageModel->removeFullDraft();

        // Eliminem el fitxer d'esborranys parcials. ALERTA[Xavi] aquesta comprovació no s'hauria de fer! s'ha de mirar com restructurar el SavePartialPageAction perquè no es faci aquesta crida
        if (!isset($this->params[PageKeys::KEY_SECTION_ID])){ // TODO[Xavi] Fix temporal
            $this->getModel()->removePartialDraft();
        }

        // Si s'ha eliminat el contingut de la pàgina, ho indiquem a l'atribut $deleted i desbloquegem la pàgina
        $this->deleted = $this->isEmptyText($this->params);
    }

    private function isEmptyText($param) {
        $text = trim($param[PageKeys::KEY_PRE].
                     $param[PageKeys::KEY_WIKITEXT].
                     $param[PageKeys::KEY_SUF]
                    );
        return ($text === NULL );
    }
}
