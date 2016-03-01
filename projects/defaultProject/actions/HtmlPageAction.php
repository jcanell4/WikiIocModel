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
require_once DOKU_PLUGIN."wikiiocmodel/WikiIocLangManager.php";

if (!defined('DW_ACT_SHOW')) {
    define('DW_ACT_SHOW', "show");
}

if (!defined('DW_DEFAULT_PAGE')) {
    define('DW_DEFAULT_PAGE', "start");
}

/**
 * Description of HtmlPageAction
 *
 * @author josep
 */
class HtmlPageAction extends PageAction{
    
    public function __construct(/*BasicPersistenceEngine*/ $engine) {
        parent::__construct($engine);
        $this->defaultDo = DW_ACT_SHOW;
    }
    
    protected function startProcess() {
        parent::startProcess();
    }

        /**
     * És un mètode per sobrescriure. Per defecte no fa res, però la 
     * sobrescriptura permet processar l'acció i emmagatzemar totes aquelles 
     * dades  intermèdies que siguin necessàries per generar la resposta final:
     * DokuAction#responseProcess.
     */
    protected function runProcess(){
        if (!WikiIocInfoManager::getInfo("exists")) {
            throw new PageNotFoundException($this->params['id'], WikiIocLangManager::getLang('pageNotFound'));
        }
        if (!WikiIocInfoManager::getInfo("perm")) {
            throw new InsufficientPermissionToViewPageException($this->params['id']); //TODO [Josep] Internacionalització missatge per defecte!
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
        
//        $response['structure'] = $this->getModel()->getData();
        $response = $this->getModel()->getData();

        // TODO: afegir el 'info' que correspongui

        // Si no s'ha especificat cap altre missatge mostrem el de carrega
        if (!$response['info']) {
            $response['info'] = $this->generateInfo("info", WikiIocLangManager::getLang('document_loaded'));
        }else {
            $this->addInfoToInfo($response['info'], $this->generateInfo("info", WikiIocLangManager::getLang('document_loaded')));
        }

        // TODO: afegir el 'meta' que correspongui
        $response['meta'] = $this->getMetaTocResponse();

        // TODO: afegir les revisions
        $response['revs'] = $this->getRevisionList();
        
        return $response;
    }
}
