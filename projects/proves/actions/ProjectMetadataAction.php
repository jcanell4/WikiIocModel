<?php
/**
 * Clase que agrupa los elementos comunes de las Actions de este proyecto
 *
 * @culpable Rafael Claver
 */
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
require_once DOKU_PLUGIN . "wikiiocmodel/actions/AbstractWikiAction.php";
require_once DOKU_PLUGIN . "wikiiocmodel/projects/documentation/datamodel/ProjectModel.php";
require_once DOKU_PLUGIN . "ajaxcommand/defkeys/ProjectKeys.php";

abstract class ProjectMetadataAction extends AbstractWikiAction {

    protected $persistenceEngine;
    protected $projectModel;

    public function __construct($persistenceEngine) {
        $this->persistenceEngine = $persistenceEngine;
        $this->projectModel = new ProjectModel($persistenceEngine);
    }

    protected function idToRequestId($requestId) {
        $id = str_replace(":", "_", $requestId);
        return $id;
    }

}
