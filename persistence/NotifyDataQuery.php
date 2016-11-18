<?php
if (!defined('DOKU_INC')) die();
if (!defined('DOKU_PLUGIN')) {
    define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
}

require_once(DOKU_PLUGIN . "wikiiocmodel/WikiIocModelExceptions.php");
require_once(DOKU_PLUGIN . "wikiiocmodel/WikiIocInfoManager.php");
require_once(DOKU_PLUGIN . 'wikiiocmodel/persistence/DataQuery.php');

/**
 * Description of NotifyDataQuery
 *
 * @author Xavier García <xaviergaro.dev@gmail.com>
 */
class NotifyDataQuery extends DataQuery
{
    // TODO[Xavi] moure a altre fitxer com els PageKeys?
    const NOTIFICATION_ID = 'notification_id';
    const SENDER_ID = 'sender_id';
    const DATA = 'data';
    const TYPE = 'type'; // ALERT, MESSAGE, DIALOG
    
    const TYPE_ALERT = 'alert';
    const TYPE_MESSAGE = 'message';
    const TYPE_DIALOG = 'dialog';
    const TYPE_RELEASED = 'released';
    const TYPE_CANCELED_BY_REMOTE_AGENT = 'canceled_by_remote_agent';    

    const DEFAULT_USER = 'SYSTEM';

    // TODO[Xavi] Segons la configuració del servidor (conf wikiiocmodel) es farà servir un sistema de timers o de websockets

    private $blackboard = []; // Creem un cache per guardar els blackboards carregats

    public function getFileName($userId, $especParams = NULL)
    {
//        $fileName = getCacheName($userId, '.blackboard');
        $fileName = $this->_notifyFN($userId);

        return $fileName;
    }

    public function getNsTree($currentNode, $sortBy, $onlyDirs=FALSE, $expandProject=FALSE, $hiddenProjects=FALSE)
    {
        throw new UnavailableMethodExecutionException("NotifyDataQuery#getNsTree");
    }


    public function generateNotification($notificationData, $type = self::TYPE_MESSAGE, $id=NULL, $senderId = NULL)
    {

        $notification = [];
        if($id===NULL){
            $now = new DateTime(); // id
            $id = $now->getTimestamp();
        }
        
        $notification[self::NOTIFICATION_ID] = $id; // ALERTA[Xavi] Moure les constants a un altre fitxer?
        $notification[self::TYPE] = $type;
        $notification[self::DATA] = $notificationData;


        // Si no s'ha especificat el sender s'atribueix al sistema
        if ($senderId === NULL) {
            $notification[self::SENDER_ID] = self::DEFAULT_USER;
        } else {
            $notification[self::SENDER_ID] = $senderId;
        }

        return $notification;
    }

    public function add($receiverId, $notificationData, $type = self::TYPE_MESSAGE, $id=NULL, $senderId = NULL)
    {


        // Generar la notificació
        $message = $this->generateNotification($notificationData, $type, $id, $senderId);


        $this->loadBlackboard($receiverId);

        $this->blackboard[$receiverId][] = $message;

        $this->saveBlackboard($receiverId);
    }

    public function get($userId, $deleteContent = TRUE)
    {

        $messages = $this->getBlackboard($userId);// Alerta[Xavi] PHP copia els arrays per valor, i no per referència

        if ($deleteContent) {
            $this->delete($userId);
        }

        return $messages;
    }


    private function loadBlackboard($userId)
    {
        // Generem el nom
        $filename = $this->getFileName($userId);

        // Carreguem el fitxer
        if (@file_exists($filename)) {
            // Unserialitzem el contingut
            $blackboard = unserialize(io_readFile($filename, FALSE));
        } else {
            // Si no existeix retornem un fitxer amb un array buit
            $blackboard = [];
        }

        //Establim el contingut carregat
        $this->blackboard[$userId] = $blackboard;
    }

    private function saveBlackboard($userId)
    {
        $filename = $this->getFileName($userId);
        $blackboard = $this->getBlackboard($userId);

        if (count($blackboard) > 0) {
            // Serialitzem el contingut del blackboard del usuari
            // Desem el fitxer
            io_saveFile($filename, serialize($blackboard));
        } else {
            // No hi ha res, l'esborrem
            $this->delete($userId);

        }
    }

    // ALERTA[Xavi] el que es retorna es una copia del array, així que els canvis no afectan al cache
    private function getBlackboard($userId)
    {
        if (!$this->blackboard[$userId]) {
            // Carreguem el blackboard
            $this->loadBlackboard($userId);
        }

        return $this->blackboard[$userId];
    }

    public function delete($userId)
    {
        // Eliminem el contingut del cache
        unset ($this->blackboard[$userId]);
        // Obtenim el nom del fitxer

        $filename = $this->getFileName($userId);
        // Eliminem el fitxer

        @unlink($filename);
    }

    private function _notifyFN($user) {
        $dir = WikiGlobalConfig::getConf("notificationdir");
        return $dir.'/'.md5(cleanID($user)).'.blackboard';
    }
}
