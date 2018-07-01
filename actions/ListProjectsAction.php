<?php
/**
 * Obtiene la lista de tipos de proyecto, es decir, la lista de directorios de proyectos
 * @culpable Rafael Claver
 */
if (!defined('DOKU_INC')) die();

class ListProjectsAction extends AbstractWikiAction {

    private $persistenceEngine;
    private $model;

    public function init($modelManager) {
        parent::init($modelManager);
        $this->persistenceEngine = $modelManager->getPersistenceEngine();
        $this->model = new DokuPageModel($this->persistenceEngine);  //Canviar per BasicWikiDataModel
    }

    /**
     * Retorna un JSON que conté la llista de tipus de projectes vàlids
     */
    public function responseProcess() {
        if ($this->params['list_type'] !== FALSE) {
            $this->model->init($this->params[ProjectKeys::KEY_ID]);
            $p = ($this->params['list_type']==="array") ? $this->params['projectType'] : NULL;
            $listProjectTypes = $this->model->getListProjectTypes($p);
            $aList=[];
            foreach ($listProjectTypes as $pTypes) {
                $aList[] = ['id' => "id_$pTypes", 'name' => $pTypes];
            }
            $ret = json_encode($aList);
        }
        return $ret;
    }

}
