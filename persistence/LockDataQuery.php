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

    public function getFileName($id, $especParams = NULL)
    {
        // TODO: Implement getFileName() method.
        throw new UnavailableMethodExecutionException("LockDataQuery#getFileName");
    }

    public function getNsTree($currentNode, $sortBy, $onlyDirs = FALSE)
    {
        throw new UnavailableMethodExecutionException("LockDataQuery#getNsTree");
    }
}
