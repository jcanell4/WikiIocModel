<?php
/**
 * WikiIocModelManager: proporciona autorizaciones y ModelWrapper
 *
 * @author Rafael Claver
 */
if (!defined('DOKU_INC')) die();
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_INC . "lib/plugins/wikiiocmodel/");

require_once WIKI_IOC_MODEL . "datamodel/TimerNotifyModel.php";
require_once WIKI_IOC_MODEL . "datamodel/WebsocketNotifyModel.php";

class WikiIocModelManager {

    public static function Instance($type){
        //logica er decidir quin espai de noms cal activar
        //per defecte activarem defaultNamespace

        if ($type) {
            $inst = WikiIocModelManager::createModelManager($type);
        } else {
            $inst = WikiIocModelManager::createModelManager('defaultProject');
        }


        return $inst;
    }

    private static function createModelManager($type){
        require_once(WIKI_IOC_MODEL . 'projects/' .$type . '/DokuModelManager.php');
//        return new \ioc_dokuwiki\WikiIocModelManager();
        return new DokuModelManager();
    }

    public function getNotifyModel($type, $persistenceEngine) {
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
