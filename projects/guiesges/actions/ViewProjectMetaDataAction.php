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
            $metaDataConfigProject = $configProjectModel->getCurrentDataProject($metaDataSubSet);

            if ($metaDataConfigProject['arraytaula']) {
                $arraytaula = json_decode($metaDataConfigProject['arraytaula'], TRUE);
                $anyActual = date("Y");
                $dataActual = new DateTime();

                foreach ($arraytaula as $elem) {
                    if ($elem['key']==="inici_trimestre_1") {
                        $inici_1 = $this->_obtenirData($elem['value'], $anyActual);
                    }else if ($elem['key']==="fi_trimestre_1") {
                        $fi_1 = $this->_obtenirData($elem['value'], $anyActual);
                    }
                    if ($elem['key']==="inici_trimestre_2") {
                        $inici_2 = $this->_obtenirData($elem['value'], $anyActual);
                    }else if ($elem['key']==="fi_trimestre_2") {
                        $fi_2 = $this->_obtenirData($elem['value'], $anyActual);
                    }
                    if ($elem['key']==="inici_trimestre_3") {
                        $inici_3 = $this->_obtenirData($elem['value'], $anyActual);
                    }else if ($elem['key']==="fi_trimestre_3") {
                        $fi_3 = $this->_obtenirData($elem['value'], $anyActual);
                    }
                }
                if ($inici_1 > $fi_1) {
                    $inici_1 = date_sub($inici_1, new DateInterval('P1Y'));
                }
                if ($inici_2 > $fi_2) {
                    $inici_2 = date_sub($inici_2, new DateInterval('P1Y'));
                }
                if ($inici_3 > $fi_3) {
                    $inici_3 = date_sub($inici_3, new DateInterval('P1Y'));
                }
                $finestraOberta = $dataActual >= $inici_1 && $dataActual <= $fi_1;
                if($finestraOberta){
                    $inici = $inici_1;
                    $fi= $fi_1;
                }else{
                    $finestraOberta = $dataActual >= $inici_2 && $dataActual <= $fi_2;
                    if($finestraOberta){
                        $inici = $inici_2;
                        $fi= $fi_2;
                    }else{
                        $finestraOberta = $dataActual >= $inici_3 && $dataActual <= $fi_3;
                        if($finestraOberta){
                            $inici = $inici_3;
                            $fi= $fi_3;
                        }
                    }
                }

                if ($finestraOberta) {
                    $updetedDate = $projectModel->getProjectSubSetAttr("updatedDate");
                    $interval = (!$updetedDate  || $updetedDate < $inici->getTimestamp());
                    $response[AjaxKeys::KEY_ACTIVA_UPDATE_BTN] = ($interval) ? "1" : "0";
                }
            }
        }

        $response[AjaxKeys::KEY_ACTIVA_FTP_PROJECT_BTN] = $projectModel->haveFilesToExportList();

        return $response;
    }

    public function responseProcess() {
        $response = parent::responseProcess();
        $projectModel = $this->getModel();
        $response[AjaxKeys::KEY_FTPSEND_HTML] = $projectModel->get_ftpsend_metadata();
        $response['ftpSendFileNames'] = $projectModel->getMetaDataFtpSenderFiles();
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
