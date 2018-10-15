<?php
/**
 * Obtiene la lista de tipos de proyecto, es decir, la lista de directorios de proyectos
 * @culpable Rafael Claver
 */
if (!defined('DOKU_INC')) die();

class ListProjectsAction extends AbstractWikiAction {

    private $persistenceEngine;
    private $model;
    private $projectTypeDir;

    public function init($modelManager) {
        parent::init($modelManager);
        $this->persistenceEngine = $modelManager->getPersistenceEngine();
        //$this->model = new DokuPageModel($this->persistenceEngine);  //Canviar per BasicWikiDataModel
        $this->projectTypeDir = $modelManager->getProjectTypeDir();
        $this->model = new BasicWikiDataModel($this->persistenceEngine);
    }

    /**
     * Retorna un JSON que conté la llista de tipus de projectes vàlids
     */
    public function responseProcess() {
        if ($this->params['list_type'] !== FALSE) {
            $metaDataSubSet = ($this->params[ProjectKeys::KEY_METADATA_SUBSET]) ? $this->params[ProjectKeys::KEY_METADATA_SUBSET] : ProjectKeys::VAL_DEFAULTSUBSET;

            $this->model->init([ProjectKeys::KEY_ID              => $this->params[ProjectKeys::KEY_ID],
                                ProjectKeys::KEY_PROJECT_TYPE    => $this->params[ProjectKeys::KEY_PROJECT_TYPE],
                                ProjectKeys::KEY_METADATA_SUBSET => $metaDataSubSet,
                                ProjectKeys::KEY_PROJECTTYPE_DIR => $this->projectTypeDir
                              ]);
            $pT = ($this->params['list_type']==="array") ? $this->params[ProjectKeys::KEY_PROJECT_TYPE] : NULL;
            $pTDir = ($pT) ? $this->projectTypeDir : NULL;
            $listProjectTypes = $this->model->getListProjectTypes($pT, $metaDataSubSet, $pTDir);
            $aList=[];
            foreach ($listProjectTypes as $pTypes) {
                $aList[] = ['id' => "id_$pTypes", 'name' => $pTypes];
            }
            $ret = json_encode($aList);
        }
        return $ret;
    }

}
