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
    const ALERT = "alert";
    const INFO = "info";
    const DIALOG = "dialog";

    const DO_INIT = "init";
    const DO_ADD = "add";
    const DO_GET = "get";
    const DO_CLOSE = "close";

    protected $dokuNotifyModel;
    protected $params;

    public function __construct($persistenceEngine)
    {
        $type = WikiGlobalConfig::getConf('notifier_type', 'wikiiocmodel');
        $modelManager = WikiIocModelManager::Instance();
        $this->dokuNotifyModel = $modelManager->getNotifyModel($type, $persistenceEngine);
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

        switch ($option) {

            //TODO[Xavi] per fer proves només afegim una info amb el resultat, això ha de fer servir el propi notifier
            case self::DO_INIT: // Retorna la resposta que inicia el sistema de notificacions segons calgui: o el processNotifications o el processWebSocketClient
                $response = $this->init();
                break;

            case self::DO_ADD: // El usuari idUser envia una notificació, notifyToFrom(). ALERTA[Xavi] Pel que hem parlat Josep i jo, s'envia la notificació al sistema. El sistema
                $response = $this->notifyToFrom();
                break;

            case self::DO_GET: // Obtenir totes les notificacions pel idUser, cridat periodicament pel timer, popNotifications()
                $response = $this->popNotifications();
                break;

            case self::DO_CLOSE: // Elimina totes les notificacions pendents pel usuari loginat, cridat al fer logout, close()
                $response = $this->close();
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

    public function notifyToFrom()
    {
        $params = $this->params[PageKeys::KEY_PARAMS];
        $text = $this->params['message']; // TODO[Xavi] Convertir en constants, decidir on
        $receiverId = $this->params['to'];
        $senderId = $this->getCurrentUser();

        $response['params'] = $this->dokuNotifyModel->notifyToFrom($text, $receiverId, $params, $senderId);
        $response['action'] = 'notification_send';

        return $response;
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
