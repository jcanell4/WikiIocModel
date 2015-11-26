<?php
if (!defined('DOKU_INC')) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');

require_once(DOKU_PLUGIN . 'wikiiocmodel/DokuModelAdapter.php');

/**
 * Class LockManager
 *
 * Gestiona el blocqueig i desbloqueig dels documents
 *
 * @author Xavier García <xaviergaro.dev@gmail.com>
 */
class LockManager
{
    public function __construct(WikiIocModel $modelWrapper = NULL)
    {
        if ($modelWrapper) {
            $this->modelWrapper = $modelWrapper;
        } else {
            $this->modelWrapper = new DokuModelAdapter();
        }
    }

    public function lock($pid)
    {
        global $conf,
               $lang;

        $locker = checklock($pid);

        if ($locker === false) {
            lock($pid);

            $info = $this->modelWrapper->generateInfo('info', "S'ha refrescat el bloqueig"); // TODO[Xavi] Localitzar el missatge

            return ['id' => $pid, 'timeout' => $conf['locktime'], 'info' => $info];

        } else {

            return ['id' => $pid, 'timeout' => -1, 'info' => $this->modelWrapper->generateInfo('error', $lang['lockedby'] . ' ' . $locker)];
        }


    }

    public function unlock($pid)
    {
        unlock($pid);
        $info = $this->modelWrapper->generateInfo('success', "S'ha alliberat el bloqueig");
        return ['info' => $info]; // TODO[Xavi] Localitzar el missatge
    }
}