<?php
if (!defined('DOKU_INC')) die();

class ViewProjectAction extends BasicViewUpdatableProjectAction{

    protected function runAction() {

        if (!$this->getModel()->isProjectGenerated()) {
            $this->getModel()->setViewConfigKey(ProjectKeys::KEY_VIEW_FIRSTVIEW);
        }
        $response = parent::runAction();
        
        /*

        //----------------------------------------
        //Code to evaluated. Copied from ViewProjectAction.php de guiesges
        $projectModel = $this->getModel();
        $isProjectGenerated = $projectModel->isProjectGenerated();
        
        
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
                $arraytaula = IocCommon::toArrayThroughArrayOrJson($metaDataConfigProject['arraytaula']);
                $anyActual = date("Y");
                $dataActual = new DateTime();
                $dataActual->setTime(0, 0, 0);
                //arraytaula conté els elements del admconfig 
                //aquí està extraient els valors de data de les variables inici_trimestre_1, 
                //i posa sobre inici_1 el valor de la data amb l'any actual (crec)
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
                
                //Amb els valors de dates llegits inici_1, fi_1, inici_2, fi_2 etc
                //si les dates d'inci són posteriors a les de final, resto un any a la data d'inici
                if ($inici_1 > $fi_1) {
                    $inici_1 = date_sub($inici_1, new DateInterval('P1Y')); // P1D represents a period of 1 year
                }
                if ($inici_2 > $fi_2) {
                    $inici_2 = date_sub($inici_2, new DateInterval('P1Y'));
                }
                if ($inici_3 > $fi_3) {
                    $inici_3 = date_sub($inici_3, new DateInterval('P1Y'));
                }
                
                //calcula si estem actualment entre les dates d'inici_1 i fi_1 o be a inici_2 i fi_2 
                //o be a inici_3 i fi_3. 
                //Segons en quin dels rtes periodes estem,asigma a inici i fi els valors de inici_x i fi_x
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
                //Si realment estem dins d'un període dels marcats per modifciar
                //si no s'ha modificat el projecte o bé la data de modificació és anterior a l'inici
                //de l'actual interval, $interval serà true i s'assignarà 1 a KEY_ACTIVA_UPDATE_BTN
                //és a dir, si estem en període de d'actualització i encara no s'ha actualitzat, apareixerà
                //el botò per fer-ho.
                if ($finestraOberta) {
                    $updetedDate = $projectModel->getProjectSystemSubSetAttr("updatedDate");
                    $interval = (!$updetedDate  || $updetedDate < $inici->getTimestamp());
                    $response[AjaxKeys::KEY_ACTIVA_UPDATE_BTN] = ($interval) ? "1" : "0";
                }
            }
        }

        $response[AjaxKeys::KEY_ACTIVA_FTP_PROJECT_BTN] = $projectModel->haveFilesToExportList();
        
        
        //----------------------------------------
         * 
         */
        
        return $response;
    }

    public function responseProcess() {

        $response = parent::responseProcess();
        $response[AjaxKeys::KEY_FTPSEND_HTML] = $this->getModel()->get_ftpsend_metadata();

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
