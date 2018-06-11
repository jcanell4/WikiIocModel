<?php
/**
 * Obtiene la lista de tipos de proyecto, es decir, la lista de directorios de proyectos
 *
 * @culpable Rafael Claver
 */
if (!defined('DOKU_INC')) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
require_once DOKU_PLUGIN . "wikiiocmodel/actions/AbstractWikiAction.php";

class ListProjectsAction extends AbstractWikiAction {

    private $persistenceEngine;
    private $dataquery;

    public function init($modelManager) {
        parent::init($modelManager);
        $this->persistenceEngine = $modelManager->getPersistenceEngine();
        $this->dataquery = $this->persistenceEngine->createProjectMetaDataQuery();
    }

    /**
     * Retorna un JSON que conté la llista de tipus de projectes vàlids
     */
    public function responseProcess() {
        $listProjectTypes = $this->dataquery->getListProjectTypes($this->params['newProjectType']);
        $aList=[];
        foreach ($listProjectTypes as $pTypes) {
            $aList[] = ['id' => "id_$pTypes", 'name' => $pTypes];
        }
        $ret = json_encode($aList);
        return $ret;
    }

}
