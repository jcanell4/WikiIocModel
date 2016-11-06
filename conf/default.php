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
$conf['system_warning_user'] = 'Avís del sistema';


$conf['projects']['defaultProject']['templates'][0]['id'] =0;
$conf['projects']['defaultProject']['templates'][0]['name'] ="glosari";
$conf['projects']['defaultProject']['templates'][0]['path'] ="plantilles:sensecommon:cicle:m99:glosari.txt";
$conf['projects']['defaultProject']['templates'][1]['id'] =1;
$conf['projects']['defaultProject']['templates'][1]['name'] ="htmlindex";
$conf['projects']['defaultProject']['templates'][1]['path'] ="plantilles:sensecommon:cicle:m99:htmlindex.txt";
$conf['projects']['defaultProject']['templates'][2]['id'] =2;
$conf['projects']['defaultProject']['templates'][2]['name'] ="material_paper";
$conf['projects']['defaultProject']['templates'][2]['path'] ="plantilles:sensecommon:cicle:m99:material_paper.txt";
$conf['projects']['defaultProject']['templates'][3]['id'] =3;
$conf['projects']['defaultProject']['templates'][3]['name'] ="pdfindex";
$conf['projects']['defaultProject']['templates'][3]['path'] ="plantilles:sensecommon:cicle:m99:pdfindex.txt";
$conf['projects']['defaultProject']['templates'][4]['id'] =4;
$conf['projects']['defaultProject']['templates'][4]['name'] ="presentacio";
$conf['projects']['defaultProject']['templates'][4]['path'] ="plantilles:sensecommon:cicle:m99:presentacio.txt";
$conf['projects']['defaultProject']['templates'][5]['id'] =5;
$conf['projects']['defaultProject']['templates'][5]['name'] ="resultats";
$conf['projects']['defaultProject']['templates'][5]['path'] ="plantilles:sensecommon:cicle:m99:resultats.txt";
$conf['projects']['defaultProject']['templates'][6]['id'] =6;
$conf['projects']['defaultProject']['templates'][6]['name'] ="guia_imatges";
$conf['projects']['defaultProject']['templates'][6]['path'] ="plantilles:sensecommon:cicle:m99:u1:guia_imatges.txt";
$conf['projects']['defaultProject']['templates'][7]['id'] =7;
$conf['projects']['defaultProject']['templates'][7]['name'] ="index";
$conf['projects']['defaultProject']['templates'][7]['path'] ="plantilles:sensecommon:cicle:m99:u1:index.txt";
$conf['projects']['defaultProject']['templates'][8]['id'] =8;
$conf['projects']['defaultProject']['templates'][8]['name'] ="introduccio";
$conf['projects']['defaultProject']['templates'][8]['path'] ="plantilles:sensecommon:cicle:m99:u1:introduccio.txt";
$conf['projects']['defaultProject']['templates'][9]['id'] =9;
$conf['projects']['defaultProject']['templates'][9]['name'] ="pdfindex d'unitat";
$conf['projects']['defaultProject']['templates'][9]['path'] ="plantilles:sensecommon:cicle:m99:u1:pdfindex.txt";
$conf['projects']['defaultProject']['templates'][10]['id']=10;
$conf['projects']['defaultProject']['templates'][10]['name']="referencies";
$conf['projects']['defaultProject']['templates'][10]['path']="plantilles:sensecommon:cicle:m99:u1:referencies.txt";
$conf['projects']['defaultProject']['templates'][11]['id']=11;
$conf['projects']['defaultProject']['templates'][11]['name']="resultats";
$conf['projects']['defaultProject']['templates'][11]['path']="plantilles:sensecommon:cicle:m99:u1:resultats.txt";
$conf['projects']['defaultProject']['templates'][12]['id']=12;
$conf['projects']['defaultProject']['templates'][12]['name']="resum";
$conf['projects']['defaultProject']['templates'][12]['path']="plantilles:sensecommon:cicle:m99:u1:resum.txt";
$conf['projects']['defaultProject']['templates'][13]['id']=13;
$conf['projects']['defaultProject']['templates'][13]['name']="activitats";
$conf['projects']['defaultProject']['templates'][13]['path']="plantilles:sensecommon:cicle:m99:u1:a1:activitats.txt";
$conf['projects']['defaultProject']['templates'][14]['id']=14;
$conf['projects']['defaultProject']['templates'][14]['name']="annexos";
$conf['projects']['defaultProject']['templates'][14]['path']="plantilles:sensecommon:cicle:m99:u1:a1:annexos.txt";
$conf['projects']['defaultProject']['templates'][15]['id']=15;
$conf['projects']['defaultProject']['templates'][15]['name']="continguts";
$conf['projects']['defaultProject']['templates'][15]['path']="plantilles:sensecommon:cicle:m99:u1:a1:continguts.txt";
$conf['projects']['defaultProject']['templates'][16]['id']=16;
$conf['projects']['defaultProject']['templates'][16]['name']="exercicis";
$conf['projects']['defaultProject']['templates'][16]['path']="plantilles:sensecommon:cicle:m99:u1:a1:exercicis.txt";
