<?php


if (!defined("DOKU_INC")) {
    die();
}
if (!defined('DOKU_PLUGIN')) {
    define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
}

require_once (DOKU_INC . 'inc/common.php');
require_once (DOKU_INC . 'inc/actions.php');
require_once (DOKU_INC . 'inc/template.php');
require_once DOKU_PLUGIN."ownInit/WikiGlobalConfig.php";
require_once DOKU_PLUGIN."wikiiocmodel/WikiIocInfoManager.php";
require_once DOKU_PLUGIN."wikiiocmodel/WikiIocLangManager.php";
require_once DOKU_PLUGIN."wikiiocmodel/projects/defaultProject/actions/PageAction.php";
require_once DOKU_PLUGIN."wikiiocmodel/projects/defaultProject/DokuModelExceptions.php";

if (!defined('DW_ACT_EDIT')) {
    define('DW_ACT_EDIT', "edit");
}

if (!defined('DW_ACT_DENIED')) {
    define('DW_ACT_DENIED', "denied");
}

if (!defined('DW_DEFAULT_PAGE')) {
    define('DW_DEFAULT_PAGE', "start");
}

/**
 * Description of RawPageAction
 *
 * @author josep
 */
class RawPartialPageAction extends PageAction{
    public function __construct(/*BasicPersistenceEngine*/ $engine) {
        parent::__construct($engine);
        $this->defaultDo = DW_ACT_EDIT;
    }
    
    protected function startProcess() {
        parent::startProcess();
        $this->dokuPageModel->init( $this->params['id'],  
                                    $this->params['editing_chunks'],
                                    $this->params['section_id'],
                                    $this->params['rev'],
                                    $this->params['recover_draft']);                
    }

    /**
     * És un mètode per sobrescriure. Per defecte no fa res, però la 
     * sobrescriptura permet processar l'acció i emmagatzemar totes aquelles 
     * dades  intermèdies que siguin necessàries per generar la resposta final:
     * DokuAction#responseProcess.
     */
    protected function runProcess(){
        if (!WikiIocInfoManager::getInfo("exists")) {
            throw new PageNotFoundException($ID, WikiIocLangManager::getLang('pageNotFound'));
        }
    }
    
    /**
     * És un mètode per sobrescriure. Per defecte no fa res, però la 
     * sobrescriptura permet generar la resposta a enviar al client. Aquest 
     * mètode ha de retornar la resposa o bé emmagatzemar-la a l'atribut 
     * DokuAction#response.
     */
    protected function responseProcess(){   
//        $response = array();
        
//        $response['structure'] = $this->getModel()->getData(TRUE);
        $response = $this->getModel()->getData(TRUE);
        
        if($this->params["recover_draft"]){            
            $draftContent = $response["draft"];
            if($response["draftType"]==DokuPageModel::PARTIAL_DRAFT){
                $this->setContentForChunkByHeader($response['structure'], $selected, $response["draft"]);
            }
            $response['info'] = $this->generateInfo('warning', $lang['draft_editing']);            
        }else if ($response['draftTye']==DokuPageModel::PARTIAL_DRAFT) {
            $response['show_draft_dialog'] = true;
            $response['content'] = $this->getChunkFromStructureById($response['structure'], $selected);
            $response['draft'] = $this->getStructuredDraftForHeader($pid, $selected);

            if ($response['draft']['content'] === $response['content']['editing']) {
                $this->removeStructuredDraft($pid, $selected);
                unset($response['draft']);
                $response['show_draft_dialog'] = false;
            }


            $response['original_call'] = $this->generateOriginalCall($selected, $editing_chunks, $prev, $pid, $psum);
            $response['info'] = $this->generateInfo('warning', $lang['partial_draft_found']);
        }else if($response['draftTye']==DokuPageModel::FULL_DRAFT){
            $response['original_call'] = $this->generateOriginalCall($selected, $editing_chunks, $prev, $pid, $psum);
            $response['id'] = $pid;
            $response['full_draft'] = true;
            $response['info'] = $this->generateInfo('warning', $lang['draft_found']);
        } else {
            $locked = $this->lock($pid);
            if ($locked['timeout'] < 0) {
                $response['info'] = $locked['info'];
            } else {
                $response['info'] = $this->generateInfo('success', $lang['chunk_editing'] . $pid . ':' . $selected);
            }
        }

        return $response;
    }
    
    private function setContentForChunkByHeader(&$structure, $selected, $content){
        for ($i = 0; $i < count($structure['chunks']); $i++) {
            if ($structure['chunks'][$i]['header_id'] == $selected) {
                $structure['chunks'][$i]['text']['editing'] = $content['content'];
                break;
            }
        }
        return $structure;
    }


}
