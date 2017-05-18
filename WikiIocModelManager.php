<?php
/**
 * WikiIocModelManager: proporciona autorizaciones y ModelWrapper
 *                      propias de un proyecto concreto,
 *                      o bien, del proyecto por defecto
 * @author Rafael Claver
 */
if (!defined('DOKU_INC')) die();
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_INC . "lib/plugins/wikiiocmodel/");

require_once WIKI_IOC_MODEL . "datamodel/TimerNotifyModel.php";
require_once WIKI_IOC_MODEL . "datamodel/WebsocketNotifyModel.php";

class WikiIocModelManager {

    public static function Instance($type){
        if ($type) {
            $inst = WikiIocModelManager::createModelManager($type);
        } else {
            $inst = WikiIocModelManager::createModelManager('defaultProject');
        }
        return $inst;
    }

    private static function createModelManager($type){
        require_once(WIKI_IOC_MODEL . 'projects/' .$type . '/DokuModelManager.php');
        return new DokuModelManager();
    }

    public static function getNotifyModel($type, $persistenceEngine=NULL) {
        switch ($type) {
            case 'ajax':
                return new TimerNotifyModel($persistenceEngine);

            case 'websocket':
                return new WebsocketNotifyModel($persistenceEngine);

            default:
                throw new UnknownTypeParamException($type);
        }
    }
}
