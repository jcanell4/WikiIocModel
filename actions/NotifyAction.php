<?php

if (!defined("DOKU_INC")) {
    die();
}
if (!defined('DOKU_PLUGIN')) {
    define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
}

require_once DOKU_PLUGIN . "ownInit/WikiGlobalConfig.php";
require_once DOKU_PLUGIN . "wikiiocmodel/LockManager.php";
require_once DOKU_PLUGIN . "wikiiocmodel/persistence/WikiPageSystemManager.php";
require_once DOKU_PLUGIN . "wikiiocmodel/WikiIocLangManager.php";
require_once DOKU_PLUGIN . "wikiiocmodel/WikiIocInfoManager.php";
require_once DOKU_PLUGIN . "wikiiocmodel/WikiIocModelManager.php";
require_once DOKU_PLUGIN . "wikiiocmodel/actions/AbstractWikiAction.php";
require_once DOKU_PLUGIN . "ajaxcommand/requestparams/PageKeys.php";

/**
 * Description of PageAction
 *
 * @author josep
 */
class NotifyAction extends AbstractWikiAction
{
    const DO_INIT = "init";
    const DO_ADD = "add";
    const DO_ADDMESS = "add_message";
    const DO_GET = "get";
    const DO_CLOSE = "close";

    /*
     * NO CAL. Ho deixo per il·lustrar com mentenir constants amb un únic orígen de dades, sense necessitat de conèixer la seva classe.
     
    static $TYPE_ALERT;
    static $TYPE_MESSAGE;
    static $TYPE_DIALOG;
    static $TYPE_REQUIREMENT;
    static $TYPE_RELEASE;
    static $TYPE_CANCEL_NOTIFICATION;
    static $TYPE_EXPIRING;
     */

    protected $dokuNotifyModel;
    protected $params;

    public function __construct($persistenceEngine)
    {
        $type = WikiGlobalConfig::getConf('notifier_type', 'wikiiocmodel');
        $this->dokuNotifyModel = WikiIocModelManager::getNotifyModel($type, $persistenceEngine);
       
        /*
        $notifyClass = $persistenceEngine->getNotifyDataQueryClass();
        
        self::$TYPE_ALERT = $notifyClass::TYPE_ALERT;
        self::$TYPE_MESSAGE = $notifyClass::TYPE_MESSAGE;
        self::$TYPE_DIALOG = $notifyClass::TYPE_DIALOG;
        self::$TYPE_REQUIREMENT = $notifyClass::TYPE_REQUIREMENT;
        self::$TYPE_RELEASE = $notifyClass::TYPE_RELEASE;
        self::$TYPE_CANCEL_NOTIFICATION = $notifyClass::TYPE_CANCEL_NOTIFICATION;
        self::$TYPE_EXPIRING = $notifyClass::TYPE_EXPIRING;
         */
    }

    /**
     * És un mètode per sobrescriure. Per defecte no fa res, però la
     * sobrescriptura permet fer assignacions a les variables globals de la
     * wiki a partir dels valors de DokuAction#params.
     * // ALERTA[Xavi] Obligatori interficies DokuAction
     */
    protected function startProcess()
    {

    }

    // ALERTA[Xavi] Obligatori interficies DokuAction
    protected function runProcess()
    {

    }

    // ALERTA[Xavi] Obligatori interficies DokuAction
    // Aquí es genera la resposta
    protected function responseProcess()
    {
        $option = $this->params[PageKeys::KEY_DO];

        $response['notifications'] = [];

        switch ($option) {

            //TODO[Xavi] per fer proves només afegim una info amb el resultat, això ha de fer servir el propi notifier
            case self::DO_INIT: // Retorna la resposta que inicia el sistema de notificacions segons calgui: o el processNotifications o el processWebSocketClient
                $notificationInit = $this->init();
                $response['notifications'][] = $notificationInit;

                if ($notificationInit['params']['type'] == "ajax") {
                    $response['notifications'][] = $this->popNotifications();

                }
                break;

            // ALERTA[Xavi] Aquesta opció no s'utilitza i no està implementada
//            case self::DO_ADD: // El usuari idUser envia una notificació, notifyToFrom().
//                $response['notifications'][] = $this->notifyTo();
//
//                break;

            case self::DO_ADDMESS:
                //TODO[Xavi] Casella confirmar correu
                //TODO[Xavi]Casella notificar canvi (el missatge inclou document id)
                $response = $this->notifyMessageToFrom();
                break;

            case self::DO_GET: // Obtenir totes les notificacions pel idUser, cridat periodicament pel timer, popNotifications()
                $response['notifications'][]= $this->popNotifications();
                break;

            case self::DO_CLOSE: // Elimina totes les notificacions pendents pel usuari loginat, cridat al fer logout, close()
                $response['notifications'][] = $this->close();
                break;

            default:
                // TODO[Xavi] Canviar la excepció per una propia, per determinar el codi
                throw new UnavailableMethodExecutionException("NotifyAction#responseProcess");
        }


        return $response;
    }

    public function init()
    {
        $response['params'] = $this->dokuNotifyModel->init();
        $response['action'] = 'init_notifier';
        return $response;
    }

    // ALERTA[Xavi] això no es correcte, però tampoc s'està utilitzant
    public function notifyMessageToFrom()
    {

        $data = $this->params['message']; // TODO[Xavi] Convertir en constants, decidir on
        $receiverId = $this->params['to'];
        $senderId = $this->getCurrentUser();

        if (is_string($data)) {
            $message = [
                'type' => isset($this->params['type']) ? $this->params['type'] : 'info',
                'id' => $this->params['id'] . '_' . $senderId, // ALERTA[Xavi] La id d'aquests missatges concatenan la id del document i l'usuari.
                'title' => 'Missatge', // TODO[Xavi]: Localitzar
                'text' => $data
            ];
        } else {
            $message = $data;
        }




        $response['params'] = $this->dokuNotifyModel->notifyMessageToFrom($message, $receiverId, $senderId);
        $response['action'] = 'notification_send';

        return $response;
    }

    // ALERTA[Xavi] això no es correcte, però tampoc s'està utilitzant
    public function notifyTo()
    {
        // ALERTA[Xavi] No s'utilitza
    }

    public function popNotifications()
    {
        $userId = $this->getCurrentUser();
        $response['params'] = [];
        $response['params']['notifications'] = $this->dokuNotifyModel->popNotifications($userId);
        $response['action'] = 'notification_received';

        return $response;
    }

    public function close()
    {
        $userId = $this->getCurrentUser();
        $response['params'] = $this->dokuNotifyModel->close($userId);
        $response['action'] = 'close_notifier';

        return $response;
    }

    public function getCurrentUser()
    {
        return $_SERVER['REMOTE_USER'];
//        return WikiIocInfoManager::getInfo('userinfo')['name']; // Aquest no concorda amb el id desat al lock de la wiki
    }

    public function get(/*Array*/
        $paramsArr = array())
    {
        $this->params = $paramsArr;
        return $this->responseProcess();

    }
}
