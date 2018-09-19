<?php
/**
 * Obtiene la lista de plantillas de documentos
 * @culpable Rafael Claver
 */
if (!defined('DOKU_INC')) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');

class ListTemplatesAction extends AbstractWikiAction {

    private $persistenceEngine;
    private $projectModel;

    public function init($modelManager) {
        parent::init($modelManager);
        $this->persistenceEngine = $modelManager->getPersistenceEngine();
        $projectType = $modelManager->getProjectType();
        $ownProjectModel = ($projectType==="defaultProject") ? "BasicWikiDataModel" : $projectType."ProjectModel";
        $this->projectModel = new $ownProjectModel($this->persistenceEngine);
    }

    /**
     * Retorna un JSON que conté la llista de plantilles de documents
     * @return json
     */
    public function responseProcess() {
        global $conf;
        if (isset($this->params['template_list_type'])) {
            if ($this->params['template_list_type'] === "array") {
                $this->projectModel->init($this->params[ProjectKeys::KEY_ID], $this->params[ProjectKeys::KEY_PROJECT_TYPE]);
                $list = $this->projectModel->getListMetaDataComponentTypes($this->params[ProjectKeys::KEY_PROJECT_TYPE], ProjectKeys::KEY_METADATA_COMPONENT_TYPES, ProjectKeys::KEY_MD_CT_DOCUMENTS);
            }
        }
        if (!isset($list)) {
            include (DOKU_PLUGIN . 'wikiiocmodel/conf/default.php');
            $list = json_encode($conf['projects']['defaultProject']['templates']);
        }
        return $list;
    }

}
