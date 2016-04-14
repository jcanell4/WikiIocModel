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
    const TIMESTAMP = 'timestamp';
    const SENDER_ID = 'sender_id';
    const MESSAGE = 'message';
    const TYPE = 'type'; // ALERT, INFO, DIALOG
    const SUBTYPE = 'subtype'; // debug, warning, info, success, error, etc. els que fem servir actualment pels infos
    const DOC_ID = 'doc_id'; // ID del document, o res si es global
    const DURATION = 'duration';
    const PARAMS = 'params';

    const TYPE_ALERT = 'alert';
    const TYPE_INFO = 'info';
    const TYPE_DIALOG = 'dialog';

    const SUBTYPE_INFO = 'info';
    const SUBTYPE_SUCCESS = 'success';
    const SUBTYPE_WARNING = 'warning';
    const SUBTYPE_ERROR = 'error';
    const SUBTYPE_DEBUG = 'debug';

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


    public function generateMessage($text, $params = [])
    {
        $message = [];
        $now = new DateTime();
        $message[self::TIMESTAMP] = $now->getTimestamp();
        $message[self::MESSAGE] = $text;
        $message[self::PARAMS] = $params;


        return $message;
    }

    public function pushMessage($message, $receiverId, $senderId = NULL)
    {
        // Si no s'ha especificat el sender s'afegeix l'usuari connectat
        if ($senderId === NULL) {
            $message[self::SENDER_ID] = $senderId;
        }


        $this->loadBlackboard($receiverId);

        $this->blackboard[$receiverId][] = $message;

        $this->saveBlackboard($receiverId);
    }


    public function popNotifications($userId)
    {
        // Alerta[Xavi] PHP copia els arrays per valor, i no per referència
        $messages = $this->getBlackboard($userId);
        $this->clearBlackboard($userId);
        return $messages;
    }

    public function loadBlackboard($userId)
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

    public function saveBlackboard($userId)
    {
        $filename = $this->getFileName($userId);
        $blackboard = $this->getBlackboard($userId);

        if (count($blackboard) > 0) {
            // Serialitzem el contingut del blackboard del usuari
            // Desem el fitxer
            io_saveFile($filename, serialize($blackboard));
        } else {
            // No hi ha res, l'esborrem
            $this->clearBlackboard($userId);

        }
    }

    // ALERTA[Xavi] el que es retorna es una copia del array, així que els canvis no afectan al cache
    public function getBlackboard($userId)
    {
        if (!$this->blackboard[$userId]) {
            // Carreguem el blackboard
            $this->loadBlackboard($userId);
        }

        return $this->blackboard[$userId];
    }

    public function clearBlackboard($userId)
    {
        // Eliminem el contingut del cache
        unset ($this->blackboard[$userId]);
        // Obtenim el nom del fitxer

        $filename = $this->getFileName($userId);
        // Eliminem el fitxer

        @unlink($filename);
    }
}
