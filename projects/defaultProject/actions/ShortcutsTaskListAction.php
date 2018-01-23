<?php
/**
 * ShortcutsTaskListAction
 * @author josep
 */
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');

require_once DOKU_INC . 'inc/pluginutils.php';
require_once DOKU_INC . 'inc/actions.php';
require_once DOKU_PLUGIN."wikiiocmodel/projects/defaultProject/actions/HtmlPageAction.php";

class ShortcutsTaskListAction extends HtmlPageAction {

    private $shortcutExist;
    private $ns_shortcut;

    public function __construct($user_id) {
        if (!$user_id) {
            // TODO[Xavi] canviar per una excepció més adient i localitzar el missatge.
            throw new Exception("No es troba cap usuari al userinfo");
        } else {
            $this->ns_shortcut = WikiGlobalConfig::getConf('userpage_ns','wikiiocmodel').$user_id.':'.WikiGlobalConfig::getConf('shortcut_page_name','wikiiocmodel');
        }
    }

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

    public function getNsShortcut() {
        return $this->ns_shortcut;
    }
}
