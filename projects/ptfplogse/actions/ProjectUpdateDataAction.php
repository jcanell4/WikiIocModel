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
            $restoreData = !$projectModel->getProjectSubSetAttr("updatedDate");
            if($restoreData){
                //La primera vegada aquests camps no s'actualitzen!
                $calendari = $response["calendari"];
                $datesAC = $response["datesAC"];
                $datesEAF = $response["datesEAF"];
                $datesJT = $response["datesJT"];
            }
            if(ManagerProjectUpdateProcessor::updateAll($arraytaula, $response)){
                if($restoreData){
                    //La primera vegada aquests camps no s'actualitzen!
                    $response["calendari"] = $calendari;
                    $response["datesAC"] = $datesAC;
                    $response["datesEAF"] = $datesEAF;
                    $response["datesJT"] = $datesJT;
                }
                $metaData = [
                    ProjectKeys::KEY_ID_RESOURCE => $this->params[ProjectKeys::KEY_ID],
                    ProjectKeys::KEY_PROJECT_TYPE => $projectType,
                    ProjectKeys::KEY_PERSISTENCE => $this->persistenceEngine,
                    ProjectKeys::KEY_METADATA_SUBSET => $metaDataSubSet,
                    ProjectKeys::KEY_METADATA_VALUE => json_encode($response)
                ];
                $projectModel->setData($metaData);    //actualiza el contenido en 'mdprojects/'
                $projectModel->setProjectSubSetAttr("updatedDate", time());
                $response = parent::runAction();
            }
            if($this->getModel()->isProjectGenerated()){
                $id = $this->getModel()->getContentDocumentId($response);
                p_set_metadata($id, array('metadataProjectChanged'=>true));
            }
        }
        return $response;
    }


//    protected function runAction() {
//        $projectType = $this->params[ProjectKeys::KEY_PROJECT_TYPE];
//        $metaDataSubSet = $this->params[ProjectKeys::KEY_METADATA_SUBSET];
//
//        $projectModel = $this->getModel();
//        $response = $projectModel->getCurrentDataProject();
//
//        $confProjectType = $this->modelManager->getConfigProjectType();
//        //obtenir la ruta de la configuració per a aquest tipus de projecte
//        $projectTypeConfigFile = $projectModel->getProjectTypeConfigFile();
//
//        $cfgProjectModel = $confProjectType."ProjectModel";
//        $configProjectModel = new $cfgProjectModel($this->persistenceEngine);
//
//        $configProjectModel->init([ProjectKeys::KEY_ID              => $projectTypeConfigFile,
//                                   ProjectKeys::KEY_PROJECT_TYPE    => $confProjectType,
//                                   ProjectKeys::KEY_METADATA_SUBSET => $metaDataSubSet
//                                ]);
//
//        //Obtenir les dades de la configuració d'aquest tipus de projecte
//        $metaDataSubset = ($this->params[ProjectKeys::KEY_METADATA_SUBSET]) ? $this->params[ProjectKeys::KEY_METADATA_SUBSET] : ProjectKeys::VAL_DEFAULTSUBSET;
//        $metaDataConfigProject = $configProjectModel->getCurrentDataProject($metaDataSubset);
//
//        if ($metaDataConfigProject['arraytaula']) {
//            $arraytaula = json_decode($metaDataConfigProject['arraytaula'], TRUE);
//            $restoreData = !$projectModel->getProjectSubSetAttr("updatedDate");
//            if($restoreData){
//                $calendari = $response["calendari"];
//                $datesAC = $response["datesAC"];
//                $datesEAF = $response["datesEAF"];
//                $datesJT = $response["datesJT"];
//            }
//            $processArray = array();
//
//            foreach ($arraytaula as $elem) {
//                if($elem["type"] !== "noprocess"){
//                    $processor = ucwords($elem['type'])."ProjectUpdateProcessor";
//                    if ( !isset($processArray[$processor]) ) {
//                        $processArray[$processor] = new $processor;
//                    }
//                    $processArray[$processor]->init($elem['value'], $elem['parameters']);
//                    $processArray[$processor]->runProcess($response);
//                }
//            }
//            if($restoreData){
//                $response["calendari"] = $calendari;
//                $response["datesAC"] = $datesAC;
//                $response["datesEAF"] = $datesEAF;
//                $response["datesJT"] = $datesJT;
//            }
//
//            if ($elem) {
//                $metaData = [
//                    ProjectKeys::KEY_ID_RESOURCE => $this->params[ProjectKeys::KEY_ID],
//                    ProjectKeys::KEY_PROJECT_TYPE => $projectType,
//                    ProjectKeys::KEY_PERSISTENCE => $this->persistenceEngine,
//                    ProjectKeys::KEY_METADATA_SUBSET => $metaDataSubSet,
//                    ProjectKeys::KEY_METADATA_VALUE => json_encode($response)
//                ];
//                $projectModel->setData($metaData);    //actualiza el contenido en 'mdprojects/'
//
//                $projectModel->setProjectSubSetAttr("updatedDate", time());
//
//                $response = parent::runAction();
//                if($this->getModel()->isProjectGenerated()){
//                    $id = $this->getModel()->getContentDocumentId($response);
//                    p_set_metadata($id, array('metadataProjectChanged'=>true));
//                }
//            }
//        }
//
//        return $response;
//    }

    public function responseProcess() {

        $response = parent::responseProcess();
        $response[ProjectKeys::KEY_FTPSEND_HTML] = $this->getModel()->get_ftpsend_metadata();

        return $response;
    }

}
