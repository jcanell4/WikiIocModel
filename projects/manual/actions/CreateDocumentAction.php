<?php
/**
 * CreateDocumentAction: añade nuevos documentos a los datos del proyecto de tipo 'manual'
 * @culpable rafael <rclaver@xtec.cat>
 */
if (!defined('DOKU_INC')) die();

class CreateDocumentAction extends BasicCreateDocumentAction {

    protected function runProcess() {
        parent::runProcess();

        global $plugin_controller;
        // instancia de manualProjectModel
        $projectModel = $plugin_controller->getCurrentProjectModel();
        $projectModel->init([ProjectKeys::KEY_ID              => $this->params[ProjectKeys::KEY_PROJECT_ID],
                             ProjectKeys::KEY_PROJECT_TYPE    => $this->params[ProjectKeys::KEY_PROJECT_TYPE],
                             ProjectKeys::KEY_METADATA_SUBSET => $this->params[ProjectKeys::KEY_METADATA_SUBSET]
                            ]);

        // nom del nou document (inclou la ruta relativa al ns del projecte)
        $nom = trim(str_replace($this->params[ProjectKeys::KEY_PROJECT_ID], "", $this->params[ProjectKeys::KEY_ID]), ":");
        $this->_addDocumentsToProject($projectModel, $nom);
    }

    /**
     * Añade el nuevo documento a la la tabla de documentos del formulario del proyecto
     * @param object $projectModel : instancia de manualProjectModel
     * @param string $nom : nombre, con wiki ruta relativa incluida, del nuevo documento a insertar
     */
    private function _addDocumentsToProject($projectModel, $nom) {
        $dataProject = $projectModel->getCurrentDataProject();
        $documents = json_decode($dataProject['documents'], true);
        $documents[] = ['ordre' => $documents[count($documents)-1]['ordre']+1,
                        'nom' => $nom];
        $dataProject['documents'] = json_encode($documents);
        $metaData = [
            ProjectKeys::KEY_ID_RESOURCE => $this->params[ProjectKeys::KEY_PROJECT_ID],
            ProjectKeys::KEY_PROJECT_TYPE => $this->params[ProjectKeys::KEY_PROJECT_TYPE],
            ProjectKeys::KEY_PERSISTENCE => $projectModel->getPersistenceEngine(),
            ProjectKeys::KEY_METADATA_SUBSET => $this->params[ProjectKeys::KEY_METADATA_SUBSET],
            ProjectKeys::KEY_METADATA_VALUE => json_encode($dataProject)
        ];
        $projectModel->setData($metaData);
    }
}