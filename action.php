<?php
/**
 * Aquest action de plugin és disparat pel procés init
 * La lògica comuna dels actions dels projects d'aquest plugin es troba a WikiIocPluginAction
 * @culpable Rafael Claver
 */
if (!defined("DOKU_INC")) die();

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

        if($this->viewMode &&  preg_match("/~~USE:WIOCCL~~\n/", $event->result)){
            $counter = 0;
            $text = preg_replace("/~~USE:WIOCCL~~\n/", "", $event->result, 1);
            if(preg_match("/~~WIOCCL_DATA:(.*)~~\n/", $text, $match)){
               $text = preg_replace("/~~WIOCCL_DATA:(.*)~~\n/", "", $text, 1, $counter);
               $dataSource = $plugin_controller->getCurrentProjectDataSource($match[1]);
               $event->result = WiocclParser::getValue($text, [], $dataSource);
            }else if($plugin_controller->getProjectOwner()){
                $dataSource = $plugin_controller->getCurrentProjectDataSource();
                $event->result = WiocclParser::getValue($text, [], $dataSource);
            }
        }
        return false;
    }

}
