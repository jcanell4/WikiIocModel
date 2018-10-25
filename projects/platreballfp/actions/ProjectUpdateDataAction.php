<?php
if (!defined('DOKU_INC')) die();
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_INC."lib/plugins/wikiiocmodel/");
include_once WIKI_IOC_MODEL."projects/platreballfp/actions/ViewProjectMetaDataAction.php";

class ProjectUpdateDataAction extends ViewProjectMetaDataAction {

    protected function runAction() {
        $projectType = $this->params[ProjectKeys::KEY_PROJECT_TYPE];

        $projectModel = $this->getModel();
        $response = $projectModel->getDataProject();

        $confProjectType = $this->modelManager->getConfigProjectType();
        //obtenir la ruta de la configuració per a aquest tipus de projecte
        $projectTypeConfigFile = $projectModel->getProjectTypeConfigFile($projectType);

        $cfgProjectModel = $confProjectType."ProjectModel";
        $configProjectModel = new $cfgProjectModel($this->persistenceEngine);
        $configProjectModel->init($projectTypeConfigFile, $confProjectType);

        //Obtenir les dades de la configuració d'aquest tipus de projecte
        $projectFileName = $configProjectModel->getProjectFileName();
        $metaDataSubset = ($this->params[ProjectKeys::KEY_METADATA_SUBSET]) ? $this->params[ProjectKeys::KEY_METADATA_SUBSET] : ProjectKeys::VAL_DEFAULTSUBSET;
        $metaDataConfigProject = $configProjectModel->getMetaDataProject($projectFileName, $metaDataSubset);

        if ($metaDataConfigProject['arraytaula']) {
            $arraytaula = json_decode($metaDataConfigProject['arraytaula'], TRUE);
            $processArray = array();

            foreach ($arraytaula as $elem) {
                if($elem["type"] !== "noprocess"){
                    $processor = ucwords($elem['type'])."ProjectUpdateProcessor";
                    if ( !isset($processArray[$processor]) ) {
                        $processArray[$processor] = new $processor;
                    }
                    $processArray[$processor]->init($elem['value'], $elem['parameters']);
                    $processArray[$processor]->runProcess($response);
                }
            }

            if ($elem) {
                $metaData = [
                    ProjectKeys::KEY_ID_RESOURCE => $this->params[ProjectKeys::KEY_ID],
                    ProjectKeys::KEY_METADATA_SUBSET => ProjectKeys::VAL_DEFAULTSUBSET,
                    ProjectKeys::KEY_PERSISTENCE => $this->persistenceEngine,
                    ProjectKeys::KEY_PROJECT_TYPE => $projectType,
                    ProjectKeys::KEY_METADATA_VALUE => json_encode($response)
                ];
                $projectModel->setData($metaData);    //actualiza el contenido en 'mdprojects/'

                $response = parent::runAction();
                if($this->getModel()->isProjectGenerated()){
                    $id = $this->getModel()->getContentDocumentId($response);
                    p_set_metadata($id, array('metadataProjectChanged'=>true));
                }
            }
        }

        return $response;
    }

}
