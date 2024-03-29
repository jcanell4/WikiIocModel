<?php
if (!defined('DOKU_INC')) die();

class ProjectUpdateDataAction extends ViewProjectAction {

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
//            $arraytaula = json_decode($metaDataConfigProject['arraytaula'], TRUE);
            $processArray = array();

            foreach ($arraytaula as $elem) {
                if($elem["type"] !== "noprocess"){
                    if($elem['type']==="templateFile"){
                        $dataTemplate = $projectModel->getRawDocument($elem['value']);
                        $dataTemplate = ":###".preg_replace(["/:###/","/###:/","/~~WIOCCL_DATA.+~~/"], "", $dataTemplate)."###:";
                        $elemTemplate = $elem;
                    }else{
                        $processor = ucwords($elem['type'])."ProjectUpdateProcessor";
                        if ( !isset($processArray[$processor]) ) {
                            $processArray[$processor] = new $processor;
                        }
                        $processArray[$processor]->init($elem['value'], $elem['parameters']);
                        $processArray[$processor]->runProcess($response);
                    }
                }
            }

            if ($elem) {
                $metaData = [
                    ProjectKeys::KEY_ID_RESOURCE => $this->params[ProjectKeys::KEY_ID],
                    ProjectKeys::KEY_PROJECT_TYPE => $projectType,
                    ProjectKeys::KEY_PERSISTENCE => $this->persistenceEngine,
                    ProjectKeys::KEY_METADATA_SUBSET => $metaDataSubSet,
                    ProjectKeys::KEY_METADATA_VALUE => json_encode($response)
                ];
                $projectModel->setData($metaData);    //actualiza el contenido en 'mdprojects/'

                //canvis als fitxers si n'hi han
                if ($elemTemplate) {
                    $projectModel->setRawProjectDocument($elemTemplate['parameters']['file'], $dataTemplate, WikiIocLangManager::getLang("update_message"));
                }

                $projectModel->setProjectSystemSubSetAttr("updatedDate", time());

                $response = parent::runAction();
                if ($this->getModel()->isProjectGenerated()){
                    $id = $this->getModel()->getContentDocumentId($response);
                    p_set_metadata($id, array('metadataProjectChanged'=>true));
                }
            }
        }else {
            throw new ConfigurationProjectNotAvailableException($projectTypeConfigFile);
        }
        unset($response[AjaxKeys::KEY_ACTIVA_UPDATE_BTN]);
        return $response;
    }

    public function responseProcess() {
        $response = parent::responseProcess();
        $response[AjaxKeys::KEY_FTPSEND_HTML] = $this->getModel()->get_ftpsend_metadata();
        return $response;
    }
}
