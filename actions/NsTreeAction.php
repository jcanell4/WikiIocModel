<?php
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . "lib/plugins/");
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_PLUGIN . "wikiiocmodel/");
include_once (WIKI_IOC_MODEL . "actions/AbstractWikiAction.php");

class NsTreeAction extends AbstractWikiAction {

    private $wikiDataModel;

    public function init($modelManager) {
        parent::init($modelManager);
        $this->persistenceEngine = $modelManager->getPersistenceEngine();
        $this->wikiDataModel = new BasicWikiDataModel($this->persistenceEngine);
    }

    protected function startProcess() {
        $this->wikiDataModel->init([ProjectKeys::KEY_ID              => $this->params[ProjectKeys::KEY_ID],
                                    ProjectKeys::KEY_PROJECT_TYPE    => $this->params[ProjectKeys::KEY_PROJECT_TYPE],
                                    ProjectKeys::KEY_METADATA_SUBSET => $this->params[ProjectKeys::KEY_METADATA_SUBSET],
                                    ProjectKeys::KEY_PROJECTTYPE_DIR => $this->params[ProjectKeys::KEY_PROJECTTYPE_DIR]
                                  ]);
    }

    public function responseProcess() {
        if ($this->params['currentnode'] !== "" && $this->params['currentnode'] !== "_" && $this->params['fromRoot'] !== "" && $this->params['expandProject']) {
            //[WARNING] Rafa: El método siguiente exige que los nombres de tipo de proyecto no se puedan repetir a lo largo de los diferentes plugins (../lib/plugins/)
            $subSetList = $this->wikiDataModel->getNsTreeSubSetsList($this->params['fromRoot']);
        }
        $tree = $this->wikiDataModel->getNsTree(
                                   $this->params['currentnode'],
                                   $this->params['sortBy'],
                                   $this->params['onlyDirs'],
                                   $this->params['expandProject'],
				   $this->params['hiddenProjects'],
                                   $this->params['fromRoot'],
                                   $subSetList
        );

        if (!$tree)
            throw new Exception("Tree Not Found");
        else
            $ret = json_encode($tree);
            return $ret;
    }
}