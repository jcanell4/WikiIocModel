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
$conf['notifier_type'] = 'websocket'; // 'ajax' o 'websocket'
//$conf['notifier_type'] = 'ajax'; // 'ajax' o 'websocket'

$conf['notifier_ajax_timer'] = 30; // Temps en s
//$conf['notifier_ajax_timer'] = 100000; // Temps en s (Augmentat per no interferir amb altres proves)

$conf['notifier_ws_port'] = '9090';
//$conf['notifier_ws_ip'] = '127.0.0.1';
$conf['notifier_ws_ip'] = '192.168.33.10';
$conf['notifier_ws_tick'] = 1; // Temps en s per cada tick de consulta al blackboard