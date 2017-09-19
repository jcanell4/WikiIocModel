<?php
/**
 * Aquest action de plugin és disparat pel procés init
 * La lògica comuna dels actions dels projects d'aquest plugin es troba a WikiIocPluginAction
 * @culpable Rafael Claver
 */
if (!defined("DOKU_INC")) die();
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_INC . 'lib/plugins/wikiiocmodel/');
require_once (WIKI_IOC_MODEL . 'WikiIocPluginAction.php');

class action_plugin_wikiiocmodel extends WikiIocPluginAction {

    function register(Doku_Event_Handler $controller) {
        parent::register($controller);
    }

}
