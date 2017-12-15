<?php
/**
 * Description of AdminTaskListAction
 * @author josep
 */
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');

require_once DOKU_INC . 'inc/pluginutils.php';
require_once DOKU_INC . 'inc/actions.php';
require_once DOKU_PLUGIN."wikiiocmodel/projects/defaultProject/actions/HtmlPageAction.php";

class ShortcutsTaskListAction extends HtmlPageAction {

    private $shortcutExist;

    public function init($modelManager) {
        parent::init($modelManager);
    }

    protected function runProcess(){
        $this->shortcutExist = $this->getModel()->pageExists();
    }

    protected function responseProcess(){
        if ($this->shortcutExist) {
            $data = $this->getModel()->getData();
            $ret = $this->getCommonPage("TAB Dreceres",
                                        WikiIocLangManager::getLang('tab_shortcuts'),
                                        $data['structure']['html']
                                       );
        }else {
            $ret = ['content' => null];
        }

        return $ret;
    }
}
