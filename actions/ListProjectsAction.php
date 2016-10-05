<?php
/**
 * Obtiene la lista de proyectos, es decir, la lista de directorios de proyectos
 * 
 * @culpable Rafael Claver
 */
if (!defined('DOKU_INC')) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
if (!defined('DOKU_PPROJECTS_DIR')) define('DOKU_PPROJECTS_DIR', DOKU_PLUGIN . 'wikiiocmodel/projects/');

require_once DOKU_PLUGIN . "wikiiocmodel/actions/AbstractWikiAction.php";

//require_once (DOKU_PLUGIN . 'wikiiocmodel/persistence/DataQuery.php');
//require_once (DOKU_PLUGIN . 'wikiiocmodel/persistence/WikiPageSystemManager.php');
//require_once (DOKU_PLUGIN . 'ownInit/WikiGlobalConfig.php');

//class ListProjects extends DataQuery {
    //public function getListProjects( $currentnode, $sortBy, $onlyDirs=TRUE, $expandProject=FALSE, $hiddenProjects=FALSE ) {
        //$base = WikiGlobalConfig::getConf('datadir');
        //return $this->getNsTreeFromBase( $base, $currentnode, $sortBy, $onlyDirs, $expandProject, $hiddenProjects=FALSE );

    //public function getFileName($id, $especParams=NULL);
    //public function getNsTree($currentNode, $sortBy, $onlyDirs=FALSE, $expandProject=FALSE, $hiddenProjects=FALSE);

class ListProjectsAction extends AbstractWikiAction {
    
    public function __construct($persistenceEngine) {
        $this->get();
    }
    /**
     * Retorna un array que conté la llista de tipus de projectes
     * @param string $projectsPath Ruta del directori que conté els tipus de projectes
     * @return array 
     */
    public function get($paramsArr = array()) {
        return $this->getListProjects(DOKU_PPROJECTS_DIR);
    }
    
    private function getListProjects( $projectsPath=NULL ) {
        $projectsPath = ($projectsPath) ? $projectsPath : DOKU_PPROJECTS_DIR;
        $ret = "[{'id': 'defaultProject', 'name': 'defaultProject'},
                 {'id': 'documentation', 'name': 'documentation'},
                 {'id': 'testmat', 'name': 'testmat'}]";
        return $ret;
    }
    
}
