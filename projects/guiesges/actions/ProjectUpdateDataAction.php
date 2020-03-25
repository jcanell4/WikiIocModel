<?php
if (!defined('DOKU_INC')) die();

class ProjectUpdateDataAction extends ViewProjectMetaDataAction {

    protected function runAction() {
        $projectType = $this->params[ProjectKeys::KEY_PROJECT_TYPE];
        $metaDataSubSet = $this->params[ProjectKeys::KEY_METADATA_SUBSET];

        $projectModel = $this->getModel();
        $response = $projectModel->getCurrentDataProject();

        $confProjectType = $this->modelManager->getConfigProjectType();
        //obtenir la ruta de la configuració per a aquest tipus de projecte
        $projectTypeConfigFile = $projectModel->getProjectTypeConfigFile();

        $cfgProjectModel = $confProjectType."ProjectModel";
        $configProjectModel = new $cfgProjectModel($this->persistenceEngine);

        $configProjectModel->init([ProjectKeys::KEY_ID              => $projectTypeConfigFile,
                                   ProjectKeys::KEY_PROJECT_TYPE    => $confProjectType,
                                   ProjectKeys::KEY_METADATA_SUBSET => $metaDataSubSet
                                ]);

        //Obtenir les dades de la configuració d'aquest tipus de projecte
        $metaDataSubset = ($this->params[ProjectKeys::KEY_METADATA_SUBSET]) ? $this->params[ProjectKeys::KEY_METADATA_SUBSET] : ProjectKeys::VAL_DEFAULTSUBSET;
        $metaDataConfigProject = $configProjectModel->getCurrentDataProject($metaDataSubset);

        if ($metaDataConfigProject['arraytaula']) {
            $arraytaula = json_decode($metaDataConfigProject['arraytaula'], TRUE);
            if(ManagerProjectUpdateProcessor::updateAll($arraytaula, $response)){
                $metaData = [
                    ProjectKeys::KEY_ID_RESOURCE => $this->params[ProjectKeys::KEY_ID],
                    ProjectKeys::KEY_PROJECT_TYPE => $projectType,
                    ProjectKeys::KEY_PERSISTENCE => $this->persistenceEngine,
                    ProjectKeys::KEY_METADATA_SUBSET => $metaDataSubSet,
                    ProjectKeys::KEY_METADATA_VALUE => json_encode($response)
                ];
                $projectModel->setData($metaData);    //actualiza el contenido en 'mdprojects/'

                $response = parent::runAction();
                $projectModel->setProjectSubSetAttr("updatedDate", time());
            }
            if($this->getModel()->isProjectGenerated()){
                $id = $this->getModel()->getContentDocumentId($response);
                p_set_metadata($id, array('metadataProjectChanged'=>true));
            }
        }
        return $response;
    }

    public function responseProcess() {
        $response = parent::responseProcess();
        $response[ProjectKeys::KEY_FTPSEND_HTML] = $this->getModel()->get_ftpsend_metadata();
        return $response;
    }

}
