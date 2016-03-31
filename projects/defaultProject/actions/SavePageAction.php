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
    
    public function __construct(/*BasicPersistenceEngine*/ $engine) {
        parent::__construct($engine);
        $this->defaultDo = DW_ACT_SAVE;
    }

    /**
     * És un mètode per sobrescriure. Per defecte no fa res, però la
     * sobrescriptura permet fer assignacions a les variables globals de la
     * wiki a partir dels valors de DokuAction#params.
     */
//    protected function startProcess(){
////        if ($this->params['wikitext']) {
////            $this->params['text'] = $this->params['wikitext']; // TODO[Xavi] canviar el formulari del frontent per enviar el paràmetre text en lloc de wikitext? <-- en el save total, el partial ja ho fa amb text només
////        }
//
//        parent::startProcess();
//        //$this->dokuPageModel->init($this->params['id']);                
//
//    }

    /**
     * És un mètode per sobrescriure. Per defecte no fa res, però la
     * sobrescriptura permet processar l'acció i emmagatzemar totes aquelles
     * dades  intermèdies que siguin necessàries per generar la resposta final:
     * DokuAction#responseProcess.
     */
    protected function runProcess(){
        global $ACT;
        global $ID;
        
        if($this->params[PageKeys::KEY_DO]==DW_ACT_SAVE && !WikiIocInfoManager::getInfo("exists")) {
            throw new PageNotFoundException($ID, WikiIocLangManager::getLang('pageNotFound'));
        }

        $ACT = act_permcheck($ACT);

        if ($ACT == DW_ACT_SAVE) {
            $ret = act_save($ACT);
        } else {
            $ret = $ACT;
        }

//        $ret='edit';

        switch ($ret) {
            case 'edit':
                throw new WordBlockedException($ID);

            case 'conflict':
                throw new DateConflictSavingException($ID);

            case 'denied':
                throw new InsufficientPermissionToCreatePageException($ID);
        }

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

        if ($this->deleted) {
            $response['deleted'] = TRUE;
            $type = 'success';
            $response['info'] = sprintf(WikiIocLangManager::getLang('deleted'), $this->params[PageKeys::KEY_ID]);
            $response['code'] = $this->code;
            $id = $response['id'] = str_replace(":", "_", $this->params[PageKeys::KEY_ID]);
            $duration = NULL;
            
        }
        else {
            $response = ['code' => $this->code, 'info' => WikiIocLangManager::getLang('saved')];

            //TODO[Josep] Cal canviar els literals per referencies dinàmiques del maincfg <-- [Xavi] el nom del formulari ara es dinamic, canvia per cada document
            $response['formId'] = 'form_' . WikiPageSystemManager::cleanIDForFiles($ID);
            $response['inputs'] = [
                'date' => @filemtime(wikiFN($ID)),
                'changecheck' => md5($TEXT)
            ];
            $type = 'success';
            $duration = 10;
            $id = str_replace(":", "_", $this->params[PageKeys::KEY_ID]);
        }
        
        $response['info'] = $this->generateInfo($type, $response['info'], $id, $duration);



        return $response;
    }

}
