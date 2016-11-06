<?php

if (!defined("DOKU_INC")) {
    die();
}
if (!defined('DOKU_PLUGIN')) {
    define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
}

require_once (DOKU_INC . 'inc/pluginutils.php');
require_once (DOKU_INC . 'inc/actions.php');
require_once DOKU_PLUGIN."ownInit/WikiGlobalConfig.php";
require_once DOKU_PLUGIN."wikiiocmodel/WikiIocInfoManager.php";
require_once DOKU_PLUGIN."wikiiocmodel/WikiIocLangManager.php";
require_once DOKU_PLUGIN."wikiiocmodel/projects/defaultProject/actions/HtmlPageAction.php";
require_once WikiGlobalConfig::tplIncDir()."conf/cfgIdConstants.php";



/**
 * Description of AdminTaskListAction
 *
 * @author josep
 */
class ShortcutsTaskListAction extends HtmlPageAction {

    /**
     * És un mètode per sobrescriure. Per defecte no fa res, però la 
     * sobrescriptura permet processar l'acció i emmagatzemar totes aquelles 
     * dades  intermèdies que siguin necessàries per generar la resposta final:
     * DokuAction#responseProcess.
     */
    protected function runProcess(){
        global $ACT;
        $ACT = act_permcheck( $ACT );
    }


    /** @override */
    public function get(/*Array*/
        $paramsArr = [])
    {
        $this->dokuPageModel->init($paramsArr['id']);
        return parent::get($paramsArr);

    }

    /**
     * És un mètode per sobrescriure. Per defecte no fa res, però la 
     * sobrescriptura permet generar la resposta a enviar al client. Aquest 
     * mètode ha de retornar la resposa o bé emmagatzemar-la a l'atribut 
     * DokuAction#response.
     */
    protected function responseProcess(){
//        $data = $this->getModel()->getRawData(); // ALERTA[Xavi] Es una estructura, s'ha de mirar si hi ha cap action que recuperi el document sencer

        if ($this->getModel()->pageExists()) {
            $data = $this->getModel()->getData();
            $ret = $this->getCommonPage("TAB Dreceres", WikiIocLangManager::getLang('tab_shortcuts'), $data['structure']['html']);
        } else {
            return ['content' => null];
        }

        return $ret;
    }


    /**
     * És un mètode per sobrescriure. Per defecte no fa res, però la
     * sobrescriptura permet fer assignacions a les variables globals de la
     * wiki a partir dels valors de DokuAction#params.
     */
    protected function startProcess()
    {
        // TODO: Implement startProcess() method.
    }
}
