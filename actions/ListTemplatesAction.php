<?php
/**
 * Obtiene la lista de plantillas de documentos
 * 
 * @culpable Rafael Claver
 */
if (!defined('DOKU_INC')) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');

require_once (DOKU_PLUGIN . "wikiiocmodel/actions/AbstractWikiAction.php");

class ListTemplatesAction extends AbstractWikiAction {
    
    /**
     * Retorna un array que conté la llista de plantilles de documents
     * @return json
     */
    public function get($paramsArr = array()) {
        include (DOKU_PLUGIN . 'wikiiocmodel/conf/default.php');
        return json_encode($conf['projects']['defaultProject']['templates']);
    }
    
}
