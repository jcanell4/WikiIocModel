<?php
if (!defined('DOKU_INC')) die();
if (!defined('DOKU_PLUGIN')) {
    define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
}

require_once(DOKU_PLUGIN . "wikiiocmodel/WikiIocModelExceptions.php");
require_once(DOKU_PLUGIN . "wikiiocmodel/WikiIocInfoManager.php");
require_once(DOKU_PLUGIN . 'wikiiocmodel/persistence/DataQuery.php');

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


    public function getFileName($id, $especParams = NULL)
    {
        // TODO: Implement getFileName() method.
        throw new UnavailableMethodExecutionException("LockDataQuery#getFileName");
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

    }

    /**
     * Bloqueja el recurs de la forma habitual.
     *
     * @param String $id
     */
    public function lock($id)
    {

    }

    /**
     * Desbloqueja el recurs de forma habitual
     *
     * @param String $id
     */
    public function unlock($id)
    {

    }

    /**
     * Indica si el fitxer està lliure o bloquejat. Pot retornar els següents valors: UNLOCKED, LOCKED, LOCKED_BEFORE
     *
     * @param String $id
     * @return int
     */
    public function checklock($id)
    {

    }

    /**
     * Afegeix la petició de que un usuari desitja bloquejar el recurs al fitxer de bloquejos estès. A més, activa el
     * sistema de notificacions a fi que l'usuari que manté bloquejat el recurs s'assabenti que hi ha un usuari que
     * reclama també el bloqueig.
     *
     * @param String $id
     */
    public function addRequirement($id)
    {

    }

    public function removeRequirement($id) {

    }

}
