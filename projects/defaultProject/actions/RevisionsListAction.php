<?php
/**
 * RevisionsListAction
 * @author josep
 */
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');

require_once DOKU_INC . 'inc/pluginutils.php';
require_once DOKU_INC . 'inc/actions.php';
require_once DOKU_PLUGIN."wikiiocmodel/projects/defaultProject/actions/HtmlPageAction.php";

class RevisionsListAction extends HtmlPageAction {

    public function init($modelManager) {
        parent::init($modelManager);
    }

    protected function responseProcess(){
        return $this->getRevisionList($this->params[PageKeys::KEY_OFFSET]);;
    }

}
