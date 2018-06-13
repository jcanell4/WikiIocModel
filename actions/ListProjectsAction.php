<?php
/**
 * Obtiene la lista de tipos de proyecto, es decir, la lista de directorios de proyectos
 * @culpable Rafael Claver
 */
if (!defined('DOKU_INC')) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . "lib/plugins/");
require_once DOKU_PLUGIN . "wikiiocmodel/actions/AbstractWikiAction.php";

class ListProjectsAction extends AbstractWikiAction {

    private $persistenceEngine;
    private $projectModel;

    public function init($modelManager) {
        parent::init($modelManager);
        $this->persistenceEngine = $modelManager->getPersistenceEngine();
        $this->projectModel = new ProjectModel($this->persistenceEngine);
    }

    /**
     * Retorna un JSON que conté la llista de tipus de projectes vàlids
     */
    public function responseProcess() {
        if (isset($this->params['list_type'])) {
            $this->projectModel->init($this->params[ProjectKeys::KEY_ID], $this->params[ProjectKeys::KEY_PROJECT_TYPE]);
            $p = ($this->params['list_type']==="array") ? $this->params['newProjectType'] : NULL;
            $listProjectTypes = $this->projectModel->getListProjectTypes($p);
            $aList=[];
            foreach ($listProjectTypes as $pTypes) {
                $aList[] = ['id' => "id_$pTypes", 'name' => $pTypes];
            }
            $ret = json_encode($aList);
        }
        return $ret;
    }

}
