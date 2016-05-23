<?php
if (!defined("DOKU_INC")) {
    die();
}

if (!defined('DOKU_PLUGIN')) {
    define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
}

if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_INC . 'lib/plugins/wikiiocmodel/');

require_once WIKI_IOC_MODEL . 'WikiIocModelExceptions.php';
require_once DOKU_PLUGIN . "wikiiocmodel/ResourceLockerInterface.php";
require_once DOKU_PLUGIN . "wikiiocmodel/ResourceUnlockerInterface.php";

class ResourceLocker implements ResourceLockerInterface, ResourceUnlockerInterface
{
    //RETORNAT PER checklock
    public static $ST_UNLOCKED = ResourceLockerInterface::LOCKED; // El recurs no es troba bloquejat per ningú
    public static $ST_LOCKED = ResourceLockerInterface::REQUIRED; // // El recurs es troba bloquejat per un altre usuari
    public static $ST_LOCKED_BEFORE = ResourceLockerInterface::LOCKED_BEFORE; // El recurs està bloquejat pel mateix usuari des d'un altre ordinador
    
    //RETORNAT PER requireResource o leaveResource
    public static $RS_LOCKED = ResourceLockerInterface::LOCKED; // El recurs no es troba bloquejat per ningú
    public static $RS_REQUIRED = ResourceLockerInterface::REQUIRED; // // El recurs es troba bloquejat per un altre usuari
    public static $RS_LOCKED_BEFORE = ResourceLockerInterface::LOCKED_BEFORE; // El recurs està bloquejat pel mateix usuari des d'un altre ordinador
    
    protected $lockDataQuery;
    protected $params;

    public function __construct(/*BasicPersistenceEngine*/
        $persistenceEngine, $params=NULL)
    {
        $this->lockDataQuery = $persistenceEngine->createLockDataQuery();

        $this->params = $params;
    }

    public function init($params){
        $this->params = $params;
    }

    /**
     * Es tracta del mètode que hauran d'executar en iniciar el bloqueig. Per  defecte no bloqueja el recurs, perquè
     * actualment el bloqueig es realitza internament a les funcions natives de la wiki. Malgrat tot, per a futurs
     * projectes es contempla la possibilitat de fer el bloqueig directament aquí, si es passa el paràmetre amb valor
     * TRUE. EL mètode comprova si algú està bloquejant ja el recurs i en funció d'això, retorna una constant amb el
     * resultat obtingut de la petició.
     *
     * @param bool $lock
     * @return int
     */
    public function requireResource($lock = FALSE)
    {
        $docId = $this->params[PageKeys::KEY_ID];

        $lockState = $this->lockDataQuery->checklock($docId);
        $state = array();

        switch ($lockState) {
            case LockDataQuery::LOCKED:
                $state["state"] = self::REQUIRED;
                if($this->params[PageKeys::KEY_TO_REQUIRE]){
                    $state["info"] = $this->lockDataQuery->addRequirement($docId);
                }else{
                    $state["info"] = $this->lockDataQuery->getLockInfo($docId);
                }
                break;

            case LockDataQuery::UNLOCKED:
                $state["state"] = self::LOCKED;
                $state["info"] = $this->lockDataQuery->xLock($docId, $lock);
                break;

            case LockDataQuery::LOCKED_BEFORE:
                $state["state"] = self::LOCKED_BEFORE;
                $state["info"] = $this->lockDataQuery->xLock($docId);
                break;

            default:
                throw new UnexpectedLockCodeException($lockState); // TODO[Xavi] Canviar per excepció més apropiada i localitzada
        }


        return $state;
    }

    /**
     * Es tracta del mètode que hauran d'executar en iniciar el desbloqueig o també quan l'usuari cancel·la la demanda
     * de bloqueig. Per  defecte no es desbloqueja el recurs, perquè actualment el desbloqueig es realitza internament
     * a les funcions natives de la wiki. Malgrat tot, per a futurs projectes es contempla la possibilitat de fer el
     * desbloqueig directament aquí, si es passa el paràmetre amb valor TRUE. EL mètode retorna una constant amb el
     * resultat obtingut de la petició.
     *
     * @param bool $unlock
     * @return int
     */
    public function leaveResource($unlock = FALSE)
    {

        $docId = $this->params[PageKeys::KEY_ID];

        // Estat del lock?
        $lockState  = $this->lockDataQuery->checklock($docId);

        switch ($lockState) {
            case LockDataQuery::LOCKED_BEFORE:
                // Bloquejat per aquest usuari
                $this->lockDataQuery->xUnlock($docId, $unlock);
                $returnState = self::UNLOCKED;
                break;

            case LockDataQuery::LOCKED:
                // Bloquejat per altre usuari
                $this->lockDataQuery->removeRequirement($docId);
                $returnState = self::LEAVED;
                break;

            case LockDataQuery::UNLOCKED:
            default:
                // Estava desbloquejat: No cal fer res
                $returnState = self::OTHER;
                break;
        }


        return $returnState; // TODO[Xavi] Retorna el codi correcte
    }

    public function checklock() {
        return $this->lockDataQuery->checklock($this->params[PageKeys::KEY_ID]);
    }

//    public function lock() {
//        return $this->lockDataQuery->lock($this->params[PageKeys::KEY_ID], TRUE);
//    }
//
//    public function unlock() {
//        return $this->lockDataQuery->xUnlock($this->params[PageKeys::KEY_ID], TRUE);
//    }

    public function updateLock() {
        return $this->lockDataQuery->lock($this->params[PageKeys::KEY_ID]);
    }

}