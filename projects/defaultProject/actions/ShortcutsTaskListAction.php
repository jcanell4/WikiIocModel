<?php
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
if (!defined('DOKU_TPL_INCDIR')) define('DOKU_TPL_INCDIR', tpl_incdir());

require_once DOKU_INC . 'inc/pluginutils.php';
require_once DOKU_INC . 'inc/actions.php';
require_once DOKU_PLUGIN."wikiiocmodel/projects/defaultProject/actions/HtmlPageAction.php";
require_once DOKU_TPL_INCDIR."conf/cfgIdConstants.php";

/**
 * Description of AdminTaskListAction
 *
 * @author josep
 */
class ShortcutsTaskListAction extends HtmlPageAction {

    private $shortcutExist;

    /**
     * És un mètode per sobrescriure. Per defecte no fa res, però la
     * sobrescriptura permet processar l'acció i emmagatzemar totes aquelles
     * dades  intermèdies que siguin necessàries per generar la resposta final:
     * DokuAction#responseProcess.
     */
    protected function runProcess(){
        global $ACT;
        $this->shortcutExist = $this->getModel()->pageExists();
    }

    protected function responseProcess(){
        if ($this->shortcutExist) {
            $data = $this->getModel()->getData();
            $ret = $this->getCommonPage("TAB Dreceres", WikiIocLangManager::getLang('tab_shortcuts'), $data['structure']['html']);
        }else {
            return ['content' => null];
        }

        return $ret;
    }
}
