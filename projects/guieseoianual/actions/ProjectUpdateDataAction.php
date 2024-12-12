<?php
defined('DOKU_INC') || die();

class ProjectUpdateDataAction extends ViewProjectAction {
    //marjose: 
    //s'executa quan s'actualitza 
    //actualitzat amb els camps corresponents: calendari
    
    protected function runAction() {
        $projectType = $this->params[ProjectKeys::KEY_PROJECT_TYPE];
        $metaDataSubSet = ($this->params[ProjectKeys::KEY_METADATA_SUBSET]) ? $this->params[ProjectKeys::KEY_METADATA_SUBSET] : ProjectKeys::VAL_DEFAULTSUBSET;

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
        $metaDataConfigProject = $configProjectModel->getCurrentDataProject($metaDataSubSet);

        if ($metaDataConfigProject['arraytaula']) {
            $arraytaula = IocCommon::toArrayThroughArrayOrJson($metaDataConfigProject['arraytaula']);
            $restoreData = !$projectModel->getProjectSystemSubSetAttr("updatedDate");
            if($restoreData){
                //La primera vegada aquests camps no s'actualitzen!
                $calendari = $response["calendari"];
            }
            if(ManagerProjectUpdateProcessor::updateAll($arraytaula, $response)){
                if($restoreData){
                    //La primera vegada aquests camps no s'actualitzen!
                    $response["calendari"] = $calendari;
                }
                $metaData = [
                    ProjectKeys::KEY_ID_RESOURCE => $this->params[ProjectKeys::KEY_ID],
                    ProjectKeys::KEY_PROJECT_TYPE => $projectType,
                    ProjectKeys::KEY_PERSISTENCE => $this->persistenceEngine,
                    ProjectKeys::KEY_METADATA_SUBSET => $metaDataSubSet,
                    ProjectKeys::KEY_METADATA_VALUE => json_encode($response)
                ];
                $projectModel->setData($metaData);    //actualiza el contenido en 'mdprojects/'
                $projectModel->setProjectSystemSubSetAttr("updatedDate", time());
                $response = parent::runAction();
            }
            if($this->getModel()->isProjectGenerated()){
                $id = $this->getModel()->getContentDocumentId($response);
                p_set_metadata($id, array('metadataProjectChanged'=>true));
            }
        }else {
            throw new ConfigurationProjectNotAvailableException($projectTypeConfigFile);
        }

        return $response;
    }

}
