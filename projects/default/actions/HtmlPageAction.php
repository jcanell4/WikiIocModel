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
require_once DOKU_PLUGIN."wikiiocmodel/projects/default/actions/PageAction.php";
require_once DOKU_PLUGIN."wikiiocmodel/projects/default/DokuModelExceptions.php";

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
    protected $persistenceEngine;
    
    public function __construct(/*BasicPersistenceEngine*/ $engine) {
        $this->persistenceEngine = $engine;
        $this->defaultDo = DW_ACT_SHOW;
    }
    
    /**
     * És un mètode per sobrescriure. Per defecte no fa res, però la 
     * sobrescriptura permet processar l'acció i emmagatzemar totes aquelles 
     * dades  intermèdies que siguin necessàries per generar la resposta final:
     * DokuAction#responseProcess.
     */
    protected function runProcess(){
        global $ACT;
        global $ID;
        
        if (!WikiIocInfoManager::getInfo("exists")) {
            throw new PageNotFoundException($ID, WikiIocLangManager::getLang('pageNotFound'));
        }
        if (!WikiIocInfoManager::getInfo("perm")) {
            throw new InsufficientPermissionToViewPageException($ID); //TODO [Josep] Internacionalització missatge per defecte!
        }
    }
    
    /**
     * És un mètode per sobrescriure. Per defecte no fa res, però la 
     * sobrescriptura permet generar la resposta a enviar al client. Aquest 
     * mètode ha de retornar la resposa o bé emmagatzemar-la a l'atribut 
     * DokuAction#response.
     */
    protected function responseProcess(){  
        $response = array();
        
        $response['structure'] = $this->getStructuredDocument($psection, $pid, NULL);

        // TODO: afegir el 'info' que correspongui

        // Si no s'ha especificat cap altre missatge mostrem el de carrega
        if (!$response['info']) {
            $response['info'] = $this->generateInfo("info", $lang['document_loaded']);
        }

        // TODO: afegir el 'meta' que correspongui
        $response['meta'] = $this->getMetaResponse($pid);

        // TODO: afegir les revisions
        $response['revs'] = $this->getRevisions($pid);
        
        return $response;
    }
}
