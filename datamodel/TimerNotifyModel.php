<?php
if (!defined("DOKU_INC")) {
    die();
}
if (!defined('DOKU_PLUGIN')) {
    define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
}
require_once DOKU_PLUGIN . "wikiiocmodel/datamodel/AbstractWikiDataModel.php";
require_once DOKU_PLUGIN . "wikiiocmodel/datamodel/DokuNotifyModel.php";
require_once DOKU_PLUGIN . "wikiiocmodel/WikiIocModelExceptions.php";
require_once DOKU_INC . "inc/media.php";
require_once(DOKU_INC . 'inc/pageutils.php');
require_once(DOKU_INC . 'inc/common.php');


/**
 * Description of TimerLockModel
 *
 * @author Xavier García <xaviergaro.dev@gmail.com>
 */
class TimerNotifyModel extends DokuNotifyModel
{
    protected $type = 'ajax';

    protected /*NotifyDataQuery*/
        $dataQuery;


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

        $init['type'] = $this->type;
        $init['check_timer'] = WikiGlobalConfig::getConf('notifier_ajax_timer', 'wikiiocmodel');
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
