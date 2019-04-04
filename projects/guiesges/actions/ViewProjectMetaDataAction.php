<?php
if (!defined('DOKU_INC')) die();

class ViewProjectMetaDataAction extends BasicViewProjectMetaDataAction{

    protected function runAction() {

        $projectModel = $this->getModel();
        $isProjectGenerated = $projectModel->isProjectGenerated();

        if (!$isProjectGenerated) {
            $projectModel->setViewConfigName("firstView");
        }
        $response = parent::runAction();

        if ($isProjectGenerated) {
            $metaDataSubSet = $this->params[ProjectKeys::KEY_METADATA_SUBSET];
            $confProjectType = $this->modelManager->getConfigProjectType();

            //obtenir la ruta de la configuració per a aquest tipus de projecte
            $projectTypeConfigFile = $projectModel->getProjectTypeConfigFile();

            $cfgProjectModel = $confProjectType."ProjectModel";
            $configProjectModel = new $cfgProjectModel($this->persistenceEngine);

            $configProjectModel->init([ProjectKeys::KEY_ID              => $projectTypeConfigFile,
                                       ProjectKeys::KEY_PROJECT_TYPE    => $confProjectType,
                                       ProjectKeys::KEY_METADATA_SUBSET => ProjectKeys::VAL_DEFAULTSUBSET
                                    ]);
            //Obtenir les dades de la configuració per a aquest tipus de projecte
            $metaDataConfigProject = $configProjectModel->getMetaDataProject($metaDataSubSet);

            if ($metaDataConfigProject['arraytaula']) {
                $arraytaula = json_decode($metaDataConfigProject['arraytaula'], TRUE);
                $anyActual = date("Y");
                $dataActual = new DateTime();

                foreach ($arraytaula as $elem) {
                    if ($response['projectMetaData']['trimestre']['value'] == "1") { //$response['projectMetaData']['trimestre']['value'] Semestre actual indicado en los datos del proyecto
                        if ($elem['key']==="inici_trimestre_1") {
                            $inici_trimestre = $this->_obtenirData($elem['value'], $anyActual);
                        }
                        else if ($elem['key']==="fi_trimestre_1") {
                            $fi_trimestre = $this->_obtenirData($elem['value'], $anyActual);
                        }
                    }
                    else if ($response['projectMetaData']['trimestre']['value'] == "2") {
                        if ($elem['key']==="inici_trimestre_2") {
                            $inici_trimestre = $this->_obtenirData($elem['value'], $anyActual);
                        }
                        else if ($elem['key']==="fi_trimestre_2") {
                            $fi_trimestre = $this->_obtenirData($elem['value'], $anyActual);
                        }
                    }
                    else if ($response['projectMetaData']['trimestre']['value'] == "3") {
                        if ($elem['key']==="inici_trimestre_3") {
                            $inici_trimestre = $this->_obtenirData($elem['value'], $anyActual);
                        }
                        else if ($elem['key']==="fi_trimestre_3") {
                            $fi_trimestre = $this->_obtenirData($elem['value'], $anyActual);
                        }
                    }
                }

                if ($inici_trimestre) {
                    if ($inici_trimestre > $fi_trimestre) {
                        $inici_trimestre = date_sub($inici_trimestre, new DateInterval('P1Y'));
                    }
                    $updetedDate = $projectModel->getProjectSubSetAttr("updatedDate");
                    $interval = (!$updetedDate  || $updetedDate < $inici_trimestre->getTimestamp()) && !($dataActual >= $inici_trimestre && $dataActual <= $fi_trimestre);
                    $response[ProjectKeys::KEY_ACTIVA_UPDATE_BTN] = ($interval) ? "1" : "0";
                }
            }
        }
        return $response;
    }

    /**
     * Retorna una data UNIX a partir de:
     * @param string $diames en format "01/06"
     * @param string $anyActual
     * @return object DateTime
     */
    private function _obtenirData($diames, $anyActual) {
        $mesdia = explode("/", $diames);
        return date_create($anyActual."/".$mesdia[1]."/".$mesdia[0]);
    }

}
