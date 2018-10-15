<?php
/**
 * Obtiene la lista de plantillas de documentos
 * @culpable Rafael Claver
 */
if (!defined('DOKU_INC')) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_PLUGIN . "wikiiocmodel/");

class ListTemplatesAction extends AbstractWikiAction {

    private $persistenceEngine;
    private $projectModel;

    public function init($modelManager) {
        parent::init($modelManager);
        $this->persistenceEngine = $modelManager->getPersistenceEngine();
        $projectType = $modelManager->getProjectType();
        $ownProjectModel = ($projectType==="defaultProject") ? "BasicWikiDataModel" : $projectType."ProjectModel";
        $this->projectModel = new $ownProjectModel($this->persistenceEngine, $modelManager->getProjectTypeDir());
    }

    /**
     * Retorna un JSON que conté la llista de plantilles de documents
     * @return json
     */
    public function responseProcess() {
        global $conf;
        if (isset($this->params['template_list_type'])) {
            if ($this->params['template_list_type'] === "array") {
                $this->projectModel->init([ProjectKeys::KEY_ID              => $this->params[ProjectKeys::KEY_ID],
                                           ProjectKeys::KEY_PROJECT_TYPE    => $this->params[ProjectKeys::KEY_PROJECT_TYPE],
                                           ProjectKeys::KEY_METADATA_SUBSET => $this->params[ProjectKeys::KEY_METADATA_SUBSET],
                                           ProjectKeys::KEY_PROJECTTYPE_DIR => $this->params[ProjectKeys::KEY_PROJECTTYPE_DIR]
                                        ]);
                $list = $this->projectModel->getListMetaDataComponentTypes($this->params[ProjectKeys::KEY_PROJECT_TYPE],
                                                                           ProjectKeys::KEY_METADATA_COMPONENT_TYPES,
                                                                           $this->params[ProjectKeys::KEY_METADATA_SUBSET],
                                                                           ProjectKeys::KEY_MD_CT_DOCUMENTS,
                                                                           $this->getModelManager()->getProjectTypeDir());
            }
        }
        if (!isset($list)) {
            include (WIKI_IOC_MODEL . "conf/default.php");
            $list = json_encode($conf['projects']['defaultProject']['templates']);
        }
        return $list;
    }

}
