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
    
    public function __construct($persistenceEngine) {
        $this->persistenceEngine = $persistenceEngine;
        $this->dataquery = $persistenceEngine->createProjectMetaDataQuery();
    }
    
    /**
     * Retorna un JSON que conté la llista de tipus de projectes
     */
    public function responseProcess($paramsArr = array()) {
        $listProjectTypes = $this->dataquery->getListProjectTypes();
        foreach ($listProjectTypes as $pTypes) {
            $aList[] = ['id' => "id_$pTypes", 'name' => $pTypes];
        }
        $ret = json_encode($aList);
        return $ret;
    }
    
}
