<?php
if (!defined('DOKU_INC')) die();
if (!defined('DOKU_PLUGIN')) {
    define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
}

require_once(DOKU_PLUGIN . "wikiiocmodel/WikiIocModelExceptions.php");
require_once(DOKU_PLUGIN . "wikiiocmodel/WikiIocInfoManager.php");
require_once(DOKU_PLUGIN . 'wikiiocmodel/persistence/DataQuery.php');
require_once(DOKU_PLUGIN . 'wikiiocmodel/persistence/NotifyDataQuery.php');

/**
 * Description of LockDataQuery
 *
 * @author Xavier García <xaviergaro.dev@gmail.com>
 */
class LockDataQuery extends DataQuery
{
    const UNLOCKED = 100; // El recurs no es troba bloquejat per ningú
    const LOCKED = 200;  // El recurs es troba bloquejat per un altre usuari
    const LOCKED_BEFORE = 400; // El recurs està bloquejat per l'usuari actual


    protected $notifyDataQuery;

    public function __construct() {
        $this->notifyDataQuery = new NotifyDataQuery();
    }

    public function getFileName($id, $especParams = NULL)
    {
        $filename = wikiLockFN($id);
        if ($especParams) {
            $filename .= '.' . $especParams;
        }

        return $filename;
    }

    public function getNsTree($currentNode, $sortBy, $onlyDirs = FALSE)
    {
        throw new UnavailableMethodExecutionException("LockDataQuery#getNsTree");
    }

    /**
     * Enregistra en el fitxer de bloquejos del recurs identificat per id, l'usuari actual com bloquejador del recurs.
     * En cas que el paràmetre lock sigui TRUE, també bloqueja el recurs de la forma habitual.
     *
     * @param String $id
     * @param bool $lock
     */
    public function xLock($id, $lock = FALSE)
    {

        // TODO: Actualitzar el registre estès de bloquejos

        if ($lock) {
            $this->lock($id);
        }
    }

    /**
     * Elimina de l'entrada del registre estès de bloquejos del recurs identificat per id, de l'usauri bloquejador.
     * A més activa el sistema de notificacions (NotifyAction) escrivint a la pissarra de cada usuari que tenia
     * demanada la petició de bloqueig (cua de peticions) per indicar que s'ha alliberat el bloqueig i que si ho
     * desitja pot tornar a fer la petició.
     *
     * @param String $id
     * @param bool $unlock
     */
    public function xUnlock($id, $unlock = FALSE)
    {
        $id = WikiPageSystemManager::cleanIDForFiles($id);

        // TODO: Actualitzar el registre estès de bloquejos

        if ($unlock) {
            $this->unlock($id);
        }
    }

    /**
     * Bloqueja el recurs de la forma habitual.
     *
     * @param String $id
     */
    public function lock($id)
    {
        $id = WikiPageSystemManager::cleanIDForFiles($id);

        $locker = $this->checklock($id);

        if ($locker !== self::LOCKED) {
            lock($id);
        } else {
            throw new WikiIocModelException("El fitxer es troba bloquejat");
        }
    }

    /**
     * Desbloqueja el recurs de forma habitual
     *
     * @param String $id
     */
    public function unlock($id)
    {
        unlock(WikiPageSystemManager::cleanIDForFiles($id));
    }

    /**
     * Indica si el fitxer està lliure o bloquejat. Pot retornar els següents valors: UNLOCKED, LOCKED, LOCKED_BEFORE
     *
     * @param String $id
     * @return int
     */
    public function checklock($id)
    {
        $id = WikiPageSystemManager::cleanIDForFiles($id);
        $lock = checklock($id);

        if ($lock === false) {
            $state = self::UNLOCKED;
        } else if ($lock === WikiIocInfoManager::getInfo('userinfo')['name']) {
            $state = self::LOCKED_BEFORE;
        } else {
            $state = self::LOCKED;
        }


        return $state;
    }

    /**
     * Afegeix la petició de que un usuari desitja bloquejar el recurs al fitxer de bloquejos estès. A més, activa el
     * sistema de notificacions a fi que l'usuari que manté bloquejat el recurs s'assabenti que hi ha un usuari que
     * reclama també el bloqueig.
     *
     * ALERTA[Xavi] Compte! El id ha de contenir els : com a separadors
     *
     * @param String $id
     */
    public function addRequirement($id)
    {

        // Nom del fitxer:
        $lockFilename = $this->getFileName($id);
        $lockFilenameExtended = $this->getFileName($id, 'extended');

        // TODO[Xavi] Afegit la neteja de locks (com en el checklock de la wiki)
        $locked = $this->clearLockIfNeeded($id);


        if ($locked) {
            $now = new DateTime();
            $requirerUser = $this->getCurrentUser();
            $requirerTimestamp = $now->getTimestamp();

            if (@file_exists($lockFilenameExtended)) {
                // S'ha d'actualitzar
                $extended = unserialize(io_readFile($lockFilenameExtended, FALSE));

            } else {
                // S'ha de crear un nou, llegim la informació del lock base
                list($ip, $session) = explode("\n", io_readFile($lockFilename));

                $extended = [];
                $extended['locker'] = [
                    'user' => $ip,  // Correspón al nom d'usuari
                    'session' => $session // ALERTA[Xavi] sempre es null? És el que guarda la wiki
                ];

            }
            $extended['requirers'][$requirerUser] = $requirerTimestamp;

        } else {
            // ALERTA[Xavi] El document no està bloquejat, aquest cas no s'hauria de donar mai
            return;

        }

        // Afegir la informació al sistema de bloquejos extés
        io_saveFile($lockFilenameExtended, serialize($extended));

        // Afegir una notificació al usuari que el bloqueja
        $this->addRequirementNotification($extended['locker']['user'], $id);

        return; // Test, per afegir breakpoint
    }

    private function addRequirementNotification($lockerId, $docId) {
        $message = sprintf(WikiIocLangManager::getLang('documentRequired'), $this->getCurrentUser(), $docId);
        $this->notifyDataQuery->add($lockerId, $message);

        //add($receiverId, $textMessage, $params, $senderId = NULL, $type = self::TYPE_ALERT)
    }

    /**
     * Retorna cert si s'han eliminat els bloquejos o no existian i fals en cas contrari
     *
     * @param String $id
     * @return bool
     */
    private function clearLockIfNeeded($id)
    {
        $clear = true;


        $lock = $this->getFileName($id);
        $extended = $this->getFileName($id, 'extended');


        if (!@file_exists($lock)) {
            $clear = false;
        } else if ((time() - filemtime($lock)) > WikiGlobalConfig::getConf('locktime')) {
            @unlink($lock);
            $clear = false;
        } else {
            list($ip, $session) = explode("\n", io_readFile($lock));
            if ($ip == $_SERVER['REMOTE_USER'] || $ip == clientIP() || $session == session_id()) {
//                $clear = false; // Està bloquejat pel mateix usuari, ALERTA[Xavi] descomentar després de les proves!
            }
        }

        if (!$clear) {
            @unlink($extended);
        }


        return $clear;

    }

    private function getCurrentUser()
    {
        // TODO[Xavi] Per determinar que s'ha de fer si no existeix el REMOTE_USER
        // ALERTA[Xavi] Si s'obté el nom de l'usuari del info (WikiIocInfoManager::getInfo('userinfo')['name']) pot no coincidir amb el que es guarda al common.php#lock, ja que utilitza el codi següent:
//        if($_SERVER['REMOTE_USER']) {
        $currentUser = $_SERVER['REMOTE_USER'];
//        } else {
//            $currentUser = clientIP()."\n".session_id());
//        }

        return $currentUser;
    }

    public function removeRequirement($id)
    {
        $lockFilenameExtended = $this->getFileName($id, 'extended');

        // Comprovar si existeix l'extended
        if (@file_exists($lockFilenameExtended)) {
            $requirerUser = $this->getCurrentUser();
            $extended = unserialize(io_readFile($lockFilenameExtended, FALSE));

            // Si existeix eliminar el usuari
            unset($extended['requirers'][$requirerUser]);

            // Desar el fitxer
            io_saveFile($lockFilenameExtended, serialize($extended));
        }
    }
}
