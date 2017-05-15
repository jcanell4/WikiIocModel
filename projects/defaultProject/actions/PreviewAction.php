<?php

if (!defined("DOKU_INC")) {
    die();
}
if (!defined('DOKU_PLUGIN')) {
    define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
}

require_once (DOKU_INC . 'inc/pluginutils.php');
require_once (DOKU_INC . 'inc/actions.php');
require_once DOKU_PLUGIN."wikiiocmodel/WikiIocInfoManager.php";
require_once DOKU_PLUGIN."wikiiocmodel/WikiIocLangManager.php";
require_once DOKU_PLUGIN."wikiiocmodel/projects/defaultProject/DokuAction.php";
require_once(DOKU_PLUGIN.'ajaxcommand/requestparams/PageKeys.php');
require_once(DOKU_INC.'inc/html.php');
require_once(DOKU_INC.'inc/parserutils.php');


if (!defined('DW_ACT_PREVIEW')) {
    define('DW_ACT_PREVIEW', "preview");
}

/**
 * Description of AdminTaskAction
 *
 * @author josep
 */
class PreviewAction extends DokuAction{
    private $info;
    private $html;
    
    public function __construct() {
        $this->defaultDo = DW_ACT_PREVIEW;
        $this->setRenderer(TRUE);
    }
    
    /**
     * És un mètode per sobrescriure. Per defecte no fa res, però la 
     * sobrescriptura permet fer assignacions a les variables globals de la 
     * wiki a partir dels valors de DokuAction#params.
     */
    protected function startProcess(){        
        global $ID;
        global $ACT;
        global $TEXT;

        $ACT = $this->params[PageKeys::KEY_DO] = $this->defaultDo;
        $ACT = act_clean($ACT);

        if (!$this->params[PageKeys::KEY_ID]) {
            $this->params[PageKeys::KEY_ID] = "";
        }
        $ID = $this->params[PageKeys::KEY_ID];
        if ($this->params['text']) {
            $TEXT = $this->params[PageKeys::KEY_TEXT] = $this->params['text'] = cleanText($this->params['text']);
        } elseif ($this->params[PageKeys::KEY_TEXT]) {
            $TEXT = $this->params[PageKeys::KEY_TEXT] = $this->params['text'] = cleanText($this->params[PageKeys::KEY_TEXT]);
        }
    }
    
    /**
     * És un mètode per sobrescriure. Per defecte no fa res, però la 
     * sobrescriptura permet processar l'acció i emmagatzemar totes aquelles 
     * dades  intermèdies que siguin necessàries per generar la resposta final:
     * DokuAction#responseProcess.
     */
    protected function runProcess(){
        $info;
        $this->html = html_secedit(p_render('xhtml',p_get_instructions($this->params[PageKeys::KEY_TEXT]),$info),false);
        $this->info = $info;            
    }
    
    /**
     * És un mètode per sobrescriure. Per defecte no fa res, però la 
     * sobrescriptura permet generar la resposta a enviar al client. Aquest 
     * mètode ha de retornar la resposa o bé emmagatzemar-la a l'atribut 
     * DokuAction#response.
     */
    protected function responseProcess(){  
        $info;
        $response = array();        

        $response['html'] = $this->prePrint();
        $response['html'] .= $this->html;
        $response['html'] .= $this->postPrint();
        if($this->info){
            $response['info'] = $this->generateInfo("info", $this->info, $this->params[PageKeys::KEY_ID]);
        }
        
        return $response;
    }
    
    private function prePrint(){
        ob_start();
        include WikiGlobalConfig::tplIncDir().'pre_print.php';
        $contents = ob_get_contents();
        ob_end_clean();
        return $contents;
    }

    private function postPrint(){
        ob_start();
        include WikiGlobalConfig::tplIncDir().'post_print.php';
        $contents = ob_get_contents();
        ob_end_clean();
        return $contents;
    }
}

