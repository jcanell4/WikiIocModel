<?php
if (!defined("DOKU_INC")) {
    die();
}
if (!defined('DOKU_PLUGIN')) {
    define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
}
require_once DOKU_PLUGIN . "wikiiocmodel/datamodel/AbstractWikiDataModel.php";
require_once DOKU_PLUGIN . "wikiiocmodel/WikiIocModelExceptions.php";
require_once DOKU_INC . "inc/media.php";
require_once(DOKU_INC . 'inc/pageutils.php');
require_once(DOKU_INC . 'inc/common.php');


/**
 * Description of DokuLockModel
 *
 * @author Xavier García <xaviergaro.dev@gmail.com>
 */
class DokuNotifyModel extends AbstractWikiDataModel
{

    protected /*NotifyDataQuery*/
        $dataQuery;

    public function __construct($persistenceEngine)
    {
        // TODO[Xavi] Segons la configuració del wikiioc model farem servir el NotifyDataQuery o el WebSocketConnection
        $this->dataQuery = $persistenceEngine->createNotifyDataQuery();
    }

    public function getData()
    {
        // TODO: Implement getData() method.
        throw new UnavailableMethodExecutionException("DokuNotifyModel#getData");
    }

    public function setData($toSet)
    {
        // TODO: Implement setData() method.
        throw new UnavailableMethodExecutionException("DokuNotifyModel#setData");
    }

    public function init()
    {
        // Incialitza el sistema per l'usuari actiu. En el sistema de WebSockets incialitzarà el socket corresponent al
        // canal de comunicació entre client i servidor. En el sistema de Timers no es retorrnarà  una resposta
        // consistent en indicar els paràmetres per configurar el Timer del client (periodicitat de les peticions).
        // TODO[Xavi] Carregar la configuració: tipus de notifier i params per inicialitzar el frontend
        //      Notifier normal: tipus i checktimer

        $init['type'] = WikiGlobalConfig::getConf('notifier_type', 'wikiiocmodel');

        switch ($init['type']) {
            case 'ajax':
                // No cal fer res al servidor, només indicar la periodicitat per comprovar si hi ha notificacions (en ms)
                $init['check_timer'] = WikiGlobalConfig::getConf('notifier_check_timer', 'wikiiocmodel');
                break;

            case 'websocket':
                // TODO[Xavi]
                throw new UnavailableMethodExecutionException("DokuNotifyModel#init_websocket");
                break;

            default:
                throw new UnavailableMethodExecutionException("DokuNotifyModel#init");

        }

        return $init;
    }

    public function notifyToFrom($text, $receiverId, $params = [], $senderId = NULL)
    {
        // Posa el missatge text a la cua d'enviaments de l'usuari receiverId i firma el missatge amb el nom indicat a
        // sender. En el sistema de WebSockets el missatge s'envia de forma immediata al client. En el cas de Timers,
        // s'emmagatzema a la pissarra de l'usuari receiverId.


        // L'afegim al blackboard del destinatari
        $this->dataQuery->add($receiverId, $text, $params, $senderId, 'message'); // TODO[Xavi] S'ha de canviar per una constant
    }

    public function popNotifications($userId)
    {
        // Aquest mètode només té sentit en el sistema de Timers per tal que es pugui retornar el contingut de la
        // pissarra de l'usuari actiu. En el cas de WebSockets, no es cridarà mai, ja que el mètode notifyToFrom fa
        // l'enviament de forma immediata. El mètode  popNotifications, a més de retornar el contingut, elimina també
        // la pissarra consultada.

        return $this->dataQuery->get($userId);
    }

    public function close($userId)
    {
        // Tanca la sessió i el sistema (per exemple els sockets)
        // TODO[Xavi] Tancar la sessió
        $this->dataQuery->delete($userId);
    }


}
