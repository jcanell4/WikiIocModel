<?php
if (!defined('DOKU_INC')) die();

/**
 * Class LockManager
 *
 * Gestiona el blocqueig i desbloqueig dels documents
 *
 * @author Xavier García <xaviergaro.dev@gmail.com>
 */
class LockManager{
    


    public function lock($pid) {

        $locker = checklock($pid);

        if ($locker === false) {
            lock($pid);
        }

        return $locker;
    }

    public function unlock($pid)
    {
        unlock($pid);
    }
}