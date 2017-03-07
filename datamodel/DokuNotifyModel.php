<?php
if (!defined("DOKU_INC")) {
    die();
}
if (!defined('DOKU_PLUGIN')) {
    define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
}
require_once DOKU_PLUGIN . "wikiiocmodel/datamodel/AbstractWikiDataModel.php";
require_once DOKU_PLUGIN . "wikiiocmodel/WikiIocModelExceptions.php";
require_once DOKU_INC . "inc/media.php";
require_once(DOKU_INC . 'inc/pageutils.php');
require_once(DOKU_INC . 'inc/common.php');


/**
 * Description of DokuNotifyModel
 *
 * @author Xavier García <xaviergaro.dev@gmail.com>
 */
abstract class DokuNotifyModel extends AbstractWikiDataModel
{

    protected $type = 'abstract';
    protected /*NotifyDataQuery*/ $dataQuery;

    public function __construct($persistenceEngine)
    {
        parent::__construct($persistenceEngine);
        // TODO[Xavi] Segons la configuració del wikiioc model farem servir el NotifyDataQuery o el WebSocketConnection
        $this->dataQuery = $persistenceEngine->createNotifyDataQuery();
    }

    public function getData()
    {
        // TODO: Implement getData() method.
        throw new UnavailableMethodExecutionException("DokuNotifyModel#getData");
    }

    public function setData($toSet)
    {
        // TODO: Implement setData() method.
        throw new UnavailableMethodExecutionException("DokuNotifyModel#setData");
    }

    public abstract function init();

    public abstract function notifyMessageToFrom($text, $receiverId, $senderId = NULL, $mailbox, $read = false);
    
    public abstract function notifyTo($data, $receiverId, $type, $id=NULL, $mailbox);

    public abstract function popNotifications($userId, $since);

    public abstract function close($userId);

    public abstract function update($notificationId, $blackboardId, $updatedData);

    public abstract function delete($notificationId, $blackboardId);
}
