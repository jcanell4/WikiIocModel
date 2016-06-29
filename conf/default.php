<?php
/**
 * Default settings for the ajaxcommand plugin
 *
 * @author Josep Cañellas <jcanell4@ioc.cat> 
 */

//$conf['debugLvl']                 = 1;                  // debug mode level -- more verbose ( 0: no display; 1: display error msg; 3: display&log error msg all msg; 3: display&log all )
$conf['userpage_allowed']           = 1;
$conf['userpage_ns']                =":wiki:user:";
$conf['userpage_discuss_ns']        =":talk:wiki:user:";

// TODO[Xavi] Comprovar si es pot possar com array
//$conf['notifier_type'] = 'websocket'; // 'ajax' o 'websocket'
$conf['notifier_type'] = 'ajax'; // 'ajax' o 'websocket'

$conf['notifier_ajax_timer'] = 10; // Temps en s

$conf['notifier_ws_port'] = '9090';
$conf['notifier_ws_ip'] = '127.0.0.1';
