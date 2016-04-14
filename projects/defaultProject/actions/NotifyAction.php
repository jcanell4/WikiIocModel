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
require_once DOKU_PLUGIN . "wikiiocmodel/projects/defaultProject/DokuAction.php";
//require_once DOKU_PLUGIN . "wikiiocmodel/projects/defaultProject/datamodel/DokuPageModel.php";
require_once DOKU_PLUGIN . "wikiiocmodel/projects/defaultProject/datamodel/DokuNotifyModel.php";
require_once DOKU_PLUGIN . "ajaxcommand/requestparams/PageKeys.php";

/**
 * Description of PageAction
 *
 * @author josep
 */
class NotifyAction extends DokuAction
{
    const ALERT = "alert";
    const INFO = "info";
    const DIALOG = "dialog";

    const DO_INIT = "init";
    const DO_ADD = "add";
    const DO_GET = "get";
    const DO_DELETE = "delete";

    protected $dokuNotifyModel;

    public function __construct($persistenceEngine)
    {
        $this->dokuNotifyModel = new DokuNotifyModel($persistenceEngine);
    }

    /**
     * És un mètode per sobrescriure. Per defecte no fa res, però la
     * sobrescriptura permet fer assignacions a les variables globals de la
     * wiki a partir dels valors de DokuAction#params.
     * // ALERTA[Xavi] Obligatori interficies DokuAction
     */
    protected function startProcess()
    {
        $testA = "Notificant";
    }

    // ALERTA[Xavi] Obligatori interficies DokuAction
    protected function runProcess()
    {


        $testB = "Per aquí també";
    }

    // ALERTA[Xavi] Obligatori interficies DokuAction
    // Aquí es genera la resposta
    protected function responseProcess()
    {
        $option = $this->params[PageKeys::KEY_DO];

        switch ($option) {

            //TODO[Xavi] per fer proves només afegim una info amb el resultat, això ha de fer servir el propi notifier
            case self::DO_INIT:
                // Retorna la resposta que inicia el sistema de notificacions segons calgui: o el processNotifications o el processWebSocketClient
                $response = $this->init();

                $response['info'] = $this->generateInfo('success', WikiIocLangManager::getLang('notifier_initialized'));

                break;

            case self::DO_ADD: // El usuari idUser envia una notificació, notifyToFrom(). ALERTA[Xavi] Pel que hem parlat Josep i jo, s'envia la notificació al sistema. El sistema
                $response = $this->notifyToFrom(); // ALERTA[Xavi] Cal obtenir algun tipus de resposta?

                break;

            case self::DO_GET: // Obtenir totes les notificacions pel idUser, cridat periodicament pel timer, popNotifications()
                $response['messages'] = $this->popNotifications();
                $response['info'] = $this->generateInfo('success', WikiIocLangManager::getLang('notifier_pop_messaged'));
                break;

            case self::DO_DELETE: // Elimina totes les notificacions pendents pel usuari idUser, cridat al fer logout, close()
                $this->close();
                $response['info'] = $this->generateInfo('success', WikiIocLangManager::getLang('notifier_closed'));
                break;

        }

        $testC = "Aqui passa";


        return $response;
    }

    public function init()
    {
        $response['params'] = $this->dokuNotifyModel->init();
        $response['action'] = 'start_notifier';
        return $response;
    }

    public function notifyToFrom()
    {
        $params = $this->params[PageKeys::KEY_PARAMS];
        $text = $this->params['message']; // TODO[Xavi] Convertir en constants, decidir on
        $receiverId = $this->params['to'];
        $senderId = WikiIocInfoManager::getInfo('userinfo')['name']; // El que envia es l'usuari actiu

        $response['params'] = $this->dokuNotifyModel->notifyToFrom($text, $receiverId, $senderId, $params);
        $response['action'] = 'notification_send';

        return $response;
    }

    public function popNotifications()
    {
        $userId = $this->getCurrentUser();
        $response['params'] = $this->dokuNotifyModel->popNotifications($userId);
        $response['action'] = 'notificaction_received';

        return $response;
    }

    public function close()
    {
        $userId = $this->getCurrentUser();
        $response['params'] = $this->dokuNotifyModel->close($userId);
        $response['action'] = 'close_notifier';

        return $response;
    }

    public function getCurrentUser() {
        return WikiIocInfoManager::getInfo('userinfo')['name'];
    }

}
