<?php
/**
 * Description of PrintPageAction
 *
 * @author josep
 */
if (!defined('DOKU_INC')) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
if (!defined('DOKU_TPL_INCDIR')) define('DOKU_TPL_INCDIR', tpl_incdir());
require_once DOKU_PLUGIN . "ajaxcommand/defkeys/PageKeys.php";

class PrintPageAction extends PageAction{

    public function __construct(/*BasicPersistenceEngine*/$engine){
        parent::__construct($engine);
        //Indica que la resposta es renderitza i caldrà llançar l'esdeveniment quan calgui
        $this->setRenderer(TRUE);
    }

    protected function responseProcess(){
        $ret = array();
        ob_start();
        include DOKU_TPL_INCDIR.'print.php';
        $ret['html'] = ob_get_clean();
        return $ret;
    }

    protected function runProcess() {
        if (!WikiIocInfoManager::getInfo("exists")) {
            throw new PageNotFoundException($this->params[PageKeys::KEY_ID], 'pageNotFound');
        }
        if (!WikiIocInfoManager::getInfo("perm")) {
            throw new InsufficientPermissionToViewPageException($this->params[PageKeys::KEY_ID]);
        }
    }

}
