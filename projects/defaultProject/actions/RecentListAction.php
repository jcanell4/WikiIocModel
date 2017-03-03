<?php

if (!defined("DOKU_INC")) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');

require_once DOKU_INC . "inc/changelog.php";
require_once DOKU_INC . "inc/html.php";
require_once DOKU_PLUGIN . "wikiiocmodel/projects/defaultProject/DokuAction.php";
require_once DOKU_PLUGIN . "ajaxcommand/requestparams/RequestParameterKeys.php";

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
if (!defined('DW_ACT_RECENT')) {
    define('DW_ACT_RECENT', "recent");
}

/**
 * Description of RecentListAction
 *
 * @author josep
 */
class RecentListAction extends DokuAction{
    private $content;
    
    public function __construct() {
        $this->defaultDo = DW_ACT_RECENT;
    }

    protected function responseProcess() {
        $this->response =[ 
            'id' => "recent_list",
            'title' => WikiIocLangManager::getLang("recent_list"),
            "content" => $this->content,
            'type' => "html"
        ];        
        return $this->response;
    }

    protected function runProcess() {
        ob_start();
        html_recent();
        $this->content= ob_get_clean();
    }

    protected function startProcess() {
        global $ACT;

        $ACT = $this->params[RequestParameterKeys::DO_KEY] = DW_ACT_RECENT;
    }

}
