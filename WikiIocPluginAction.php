<?php
/**
 * Exemple de action
 * aquest exemple no és operatiu, només serveix per verificar el disseny estructural i el fluxe
 *
 * @culpable Rafael Claver
 */
if (!defined("DOKU_INC")) die();
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_INC . 'lib/plugins/wikiiocmodel/');
if (!defined('WIKI_IOC_PROJECTS')) define('WIKI_IOC_PROJECTS', WIKI_IOC_MODEL . 'projects/');
require_once (WIKI_IOC_MODEL . 'persistence/BasicPersistenceEngine.php');

class WikiIocPluginAction extends DokuWiki_Action_Plugin {

    protected $persistenceEngine;
    protected $projectMetaDataQuery;
    protected $projectType;

    public function __construct() {
        $this->persistenceEngine = new \BasicPersistenceEngine();
        $this->projectMetaDataQuery = $this->persistenceEngine->createProjectMetaDataQuery();
    }

    function register(Doku_Event_Handler $controller) {
        $listProjects = $this->projectMetaDataQuery->getListProjectTypes();
        foreach ($listProjects as $dir) {
            $action = WIKI_IOC_PROJECTS.$dir."/action.php";
            if (is_file($action)) {
                require_once ($action);
                $classe = "action_plugin_wikiiocmodel_projects_$dir";
                $accio = new $classe($dir);
                $accio->register($controller);

            }
        }
    }

}
