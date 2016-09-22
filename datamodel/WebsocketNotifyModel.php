<?php
if (!defined("DOKU_INC")) {
    die();
}
if (!defined('DOKU_PLUGIN')) {
    define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
}
require_once DOKU_PLUGIN . "wikiiocmodel/datamodel/AbstractWikiDataModel.php";
require_once DOKU_PLUGIN . "wikiiocmodel/WikiIocModelExceptions.php";
//require_once DOKU_PLUGIN . "wikiiocmodel/datamodel/DokuNotifyModel.php";
require_once DOKU_PLUGIN . "wikiiocmodel/datamodel/TimerNotifyModel.php";
require_once DOKU_INC . "inc/media.php";
require_once(DOKU_INC . 'inc/pageutils.php');
require_once(DOKU_INC . 'inc/common.php');


// ALERTA[Xavi] Aquesta ruta pot no funcionar correctament, s'ha de revisar perquè DOKU_INC es diferent si es fa un startNotifyServer
require_once(DOKU_INC . 'lib/exe/exe_ioc/websockets/classes/WebSocketNotifyServer.php');



/**
 * Description of WebsocketNotifyModel
 *
 * @author Xavier García <xaviergaro.dev@gmail.com>
 */
class WebsocketNotifyModel extends TimerNotifyModel
{

    protected $type = 'websocket';


    // Aquest es l'únic mètode necessari quan es tracta de websockets
    public function init()
    {
        $init['type'] = $this->type;
        $init['ip'] = WikiGlobalConfig::getConf('notifier_ws_ip', 'wikiiocmodel');
        $init['port'] = WikiGlobalConfig::getConf('notifier_ws_port', 'wikiiocmodel');

        // TODO[Xavi] Això crec que no cal, s'autentica l'usuari al servidor amb el seu nom d'usuari i contrasenya
        $init['token'] = 'TODO: generar token secret';// TODO[Xavi] El token s'ha de fer servir per autenticar al usuari a través del websocket, fer servir el mateix que sectok o un altre diferent?

        // El propi server controla si ja s'està executant, no cal controlar-lo aquí
        $server = new WebSocketNotifyServer($init['ip'], $init['port']);
//
//        try {
            $server->run();
//        } catch (Exception $e) {
//            $errorMessage = $e->getMessage();
//
//            $server->stdout($errorMessage);
//            $server->logError($errorMessage);
//        }

        return $init;
    }

//    public function notifyMessageToFrom($text, $receiverId, $senderId = NULL)
//    {
//        throw new UnavailableMethodExecutionException("DokuNotifyModel#notifyToFrom");
//    }

//    public function notifyTo($data, $receiverId, $type, $id=NULL)
//    {
//        throw new UnavailableMethodExecutionException("DokuNotifyModel#notifyToFrom");
//    }

//    public function popNotifications($userId)
//    {
//        throw new UnavailableMethodExecutionException("WebsocketNotifyModel#popNotifications");
//    }

    public function close($userId)
    {
        throw new UnavailableMethodExecutionException("WebsocketNotifyModel#close");
    }


}
