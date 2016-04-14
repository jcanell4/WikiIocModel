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
    const TEXT = 'text';
    const TYPE = 'type'; // ALERT, MESSAGE, DIALOG
    const PARAMS = 'params';

    const TYPE_ALERT = 'alert';
    const TYPE_MESSAGE = 'message';
    const TYPE_DIALOG = 'dialog';


    const DEFAULT_USER = 'SYSTEM';

    // TODO[Xavi] Segons la configuració del servidor (conf wikiiocmodel) es farà servir un sistema de timers o de websockets

    private $blackboard = []; // Creem un cache per guardar els blackboards carregats

    public function getFileName($userId, $especParams = NULL)
    {
        $fileName = getCacheName($userId, '.blackboard');

        return $fileName;
    }

    public function getNsTree($currentNode, $sortBy, $onlyDirs = FALSE)
    {
        throw new UnavailableMethodExecutionException("NotifyDataQuery#getNsTree");
    }


    public function generateNotification($text, $type = self::TYPE_MESSAGE, $params = [], $senderId = NULL)
    {
        $message = [];
        $now = new DateTime(); // id
        $message[self::NOTIFICATION_ID] = $now->getTimestamp();
        $message[self::TYPE] = $type;
        $message[self::TEXT] = $text;
        $message[self::PARAMS] = $params;


        // Si no s'ha especificat el sender s'atribueix al sistema
        if ($senderId === NULL) {
            $message[self::SENDER_ID] = self::DEFAULT_USER;
        } else {
            $message[self::SENDER_ID] = $senderId;
        }

        return $message;
    }

    public function add($receiverId, $textMessage, $params, $senderId = NULL, $type = self::TYPE_ALERT)
    {


        // Generar la notificació
        $message = $this->generateNotification($textMessage, $type, $params, $senderId);


        $this->loadBlackboard($receiverId);

        $this->blackboard[$receiverId][] = $message;

        $this->saveBlackboard($receiverId);
    }


    // ALERTA[Xavi] no tinc clar per a que necessitem aquesta funció, afegida perquè es trobava al document
    public function save($receiverId, $textMessage, $params, $senderId = NULL, $type = self::TYPE_ALERT)
    {
        return $this->add($receiverId, $textMessage, $params, $senderId, $type);
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
}
