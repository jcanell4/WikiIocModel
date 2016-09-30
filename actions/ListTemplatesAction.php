<?php
/**
 * Obtiene la lista de plantillas de documentos
 * 
 * @culpable Rafael Claver
 */
if (!defined('DOKU_INC')) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
if (!defined('DOKU_PPROJECTS_DIR')) define('DOKU_PPROJECTS_DIR', DOKU_PLUGIN . 'wikiiocmodel/projects/');

require_once DOKU_PLUGIN . "wikiiocmodel/actions/AbstractWikiAction.php";

class ListTemplatesAction extends AbstractWikiAction {
    
    public function __construct() {
        $this->get();
    }

    public function get($paramsArr = array()) {
        return $this->getListTemplates();
    }
    
    /**
     * Retorna un array que conté la llista de plantilles de documents
     * @param string $projectsPath Ruta del directori que conté les plantilles de documents
     * @return array 
     */
    private function getListTemplates( $projectsPath=NULL ) {
        $projectsPath = ($projectsPath) ? $projectsPath : DOKU_PPROJECTS_DIR;
        $ret = "[{'id': 'plantilla1', 'name': 'plantilla 1'},
                 {'id': 'plantilla2', 'name': 'plantilla 2'},
                 {'id': 'plantilla3', 'name': 'plantilla 3'}]";
        return $ret;
    }
    
}
