<?php
if (!defined("DOKU_INC")) {
    die();
}
if (!defined('DOKU_PLUGIN')) {
    define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
}
require_once DOKU_PLUGIN."wikiiocmodel/datamodel/AbstractWikiDataModel.php";
require_once DOKU_PLUGIN."wikiiocmodel/WikiIocModelExceptions.php";
require_once DOKU_INC."inc/media.php";
require_once(DOKU_INC. 'inc/pageutils.php');
require_once(DOKU_INC. 'inc/common.php');



/**
 * Description of DokuLockModel
 *
 * @author Xavier García <xaviergaro.dev@gmail.com>
 */
class DokuLockModel extends AbstractWikiDataModel {

    protected /*LockDataQuery*/ $dataQuery;

    public function __construct($persistenceEngine) {
        $this->dataQuery = $persistenceEngine->createNotifyDataQuery();
    }

    public function getData()
    {
        // TODO: Implement getData() method.
        throw new UnavailableMethodExecutionException("DokuLockModel#getData");
    }

    public function setData($toSet)
    {
        // TODO: Implement setData() method.
        throw new UnavailableMethodExecutionException("DokuLockModel#setData");
    }
}
