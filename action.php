<?php
/**
 * Aquest action de plugin és disparat pel procés init
 * La lògica comuna dels actions dels projects d'aquest plugin es troba a WikiIocPluginAction
 * @culpable Rafael Claver
 */
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . "lib/plugins/");
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_PLUGIN . "wikiiocmodel/");
require_once (WIKI_IOC_MODEL . "WikiIocPluginAction.php");

class action_plugin_wikiiocmodel extends WikiIocPluginAction {
    private $viewMode = false;

    function register(Doku_Event_Handler $controller) {
        parent::register($controller);
        $controller->register_hook('WIOC_AJAX_COMMAND', "BEFORE", $this, "setViewMode", array());
        $controller->register_hook('IO_WIKIPAGE_READ', "AFTER", $this, "io_readWikiPage", array());
        $controller->register_hook('PARSER_CACHE_USE', "BEFORE", $this, "cache_use", array());
    }
    
    function setViewMode(&$event, $param){
        switch ($event->data["call"]){
            case "page":
            case "cancel":  
                $this->viewMode = true;
                break;
        }
    }
    
    function cache_use(&$event, $param){
        global $plugin_controller;
        
        $projectOwner =  $plugin_controller->getProjectOwner();
        if($this->viewMode &&  $projectOwner){
            $fileProjetc = $plugin_controller->getProjectFile($projectOwner);
            $event->data->depends["files"] []= $fileProjetc;
        }        
    }
    
    function io_readWikiPage(&$event, $param){
        global $plugin_controller;
        
        if($this->viewMode &&  $plugin_controller->getProjectOwner()){
            $counter = 0;
            $text = preg_replace("/~~USE:WIOCCL~~\n/", "", $event->result, 1, $counter);
            if($counter>0){
                $dataSource = $plugin_controller->getCurrentProjectDataSource();
                $event->result = WiocclParser::getValue($text, [], $dataSource);
            }
        }
        return false;
    }

}
