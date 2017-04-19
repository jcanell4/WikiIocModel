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
require_once DOKU_PLUGIN . "wikiiocmodel/projects/defaultProject/DokuAction.php";
require_once DOKU_PLUGIN . "wikiiocmodel/projects/defaultProject/DokuModelExceptions.php";
require_once DOKU_PLUGIN . "ajaxcommand/requestparams/PageKeys.php";


if (!defined('DW_ACT_SAVE')) {
    define('DW_ACT_SAVE', "save");
}

/**
 * Description of AdminTaskAction
 *
 * @author Xavier García <xaviergaro.dev@gmail.com>
 */
class SavePageAction extends RawPageAction {

    protected $deleted = FALSE;
    private $code = 0;
     private $lockStruct;
    
    public function __construct(/*BasicPersistenceEngine*/ $engine) {
        parent::__construct($engine);
        $this->defaultDo = DW_ACT_SAVE;
    }

    /**
     * És un mètode per sobrescriure. Per defecte no fa res, però la
     * sobrescriptura permet processar l'acció i emmagatzemar totes aquelles
     * dades  intermèdies que siguin necessàries per generar la resposta final:
     * DokuAction#responseProcess.
     */
    protected function runProcess(){
        global $ACT;
        $ID=  $this->params[PageKeys::KEY_ID];
        
        if($this->params[PageKeys::KEY_DO]==DW_ACT_SAVE && !WikiIocInfoManager::getInfo("exists")) {
            throw new PageNotFoundException($ID, 'pageNotFound');
        }

        $ACT = act_permcheck($ACT);
        
        if($ACT==="denied"){
            throw new InsufficientPermissionToCreatePageException($ID);
        }
        
        if($this->checklock()==ST_LOCKED){        
            throw new FileIsLockedException($this->params[PageKeys::KEY_ID]);
        }

        $this->lockStruct = $this->updateLock();
        $this->_save();
    }

    /**
     * És un mètode per sobrescriure. Per defecte no fa res, però la
     * sobrescriptura permet generar la resposta a enviar al client. Aquest
     * mètode ha de retornar la resposa o bé emmagatzemar-la a l'atribut
     * DokuAction#response.
     */
    protected function responseProcess()
    {
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

            if ($this->params[PageKeys::KEY_CANCEL]) {

                $response['code'] = "cancel_document"; // ALERTA[Xavi] Hi ha algun lloc on es puguin consultar els codis creats?
                $response['cancel_params'] = ['id' => $ID, 'call' => 'cancel', 'discard_changes' => true, /*, 'do' => 'cancel'*/];

            } else if ($this->params[PageKeys::KEY_REV]) {
                $response['close']['id'] = WikiPageSystemManager::getContainerIdFromPageId($ID) . $suffix;

                if ($this->params[PageKeys::KEY_RELOAD]) {
                    $response['reload']['id'] = $ID;
                    $response['reload']['call'] = 'edit';
                } else {
                    $response['reload']['id'] = $ID;
                    $response['reload']['call'] = 'page';
                }
            } else {
                $response['formId'] = 'form_' . WikiPageSystemManager::getContainerIdFromPageId($ID) . $suffix;
                $response['inputs'] = [
                    'date' => @filemtime(wikiFN($ID)),
                    'changecheck' => md5($TEXT)
                ];
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
        if(checkwordblock()) {
//            msg($lang['wordblock'], -1);
//            return 'edit';
            throw new WordBlockedException();
        }
        //conflict check
        if($this->params[PageKeys::KEY_DATE] != 0 
                && WikiIocInfoManager::getInfo('meta')['date']['modified'] > $this->params[PageKeys::KEY_DATE] ){
            //return 'conflict';
            throw new DateConflictSavingException();
        }

        //save it
        //saveWikiText($ID,con($PRE,$TEXT,$SUF,1),$SUM,$INPUT->bool('minor')); //use pretty mode for con
        $this->dokuPageModel->setData(array(
            "text" => con($this->params[PageKeys::KEY_PRE],
                                $this->params[PageKeys::KEY_TEXT], 
                                $this->params[PageKeys::KEY_SUF], 1), 
            "summary" => $this->params[PageKeys::KEY_SUM], 
            "minor" =>  $this->params[PageKeys::KEY_MINOR]));

        //delete draft
//        act_draftdel($act);
        $this->dokuPageModel->removeFullDraft();

        // Esborrem el draft parcial perquè aquest NO l'elimina la wiki automàticament
        //$this->draftQuery->removePartialDraft($this->params['id']);

        // Eliminem el fitxer d'esborranys parcials. ALERTA[Xavi] aquesta comprovació no s'hauria de fer! s'ha de mirar com restructurar el SavePartialPageAction perquè no es faci aquesta crida

        if (!isset($this->params[PageKeys::KEY_SECTION_ID])){ // TODO[Xavi] Fix temporal
            $this->getModel()->removePartialDraft();
        }

        // Si s'ha eliminat el contingut de la pàgina, ho indiquem a l'atribut $deleted
        $this->deleted = (trim( $this->params[PageKeys::KEY_PRE].
                                $this->params[PageKeys::KEY_TEXT].
                                $this->params[PageKeys::KEY_SUF] )
                          == NULL );            
    }

}
