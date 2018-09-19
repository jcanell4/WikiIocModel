<?php
if (!defined('DOKU_INC')) die();
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_INC."lib/plugins/wikiiocmodel/");
include_once WIKI_IOC_MODEL."actions/BasicViewProjectMetaDataAction.php";

class ViewProjectMetaDataAction extends BasicViewProjectMetaDataAction{

    protected function runAction() {
        $response = parent::runAction();

        $projectType = $this->params[ProjectKeys::KEY_PROJECT_TYPE];
        $confProjectType = $this->modelManager->getConfigProjectType();

        //obtenir la ruta de la configuració per a aquest tipus de projecte
        $projectTypeConfigFile = $this->projectModel->getProjectTypeConfigFile($projectType);

        $cfgProjectModel = $confProjectType."ProjectModel";
        $configProjectModel = new $cfgProjectModel($this->persistenceEngine);
        $configProjectModel->init($projectTypeConfigFile, $confProjectType);

        $configResponse = $configProjectModel->getData();
        if ($configResponse['projectMetaData']['arraytaula']['value']) {
            $arraytaula = json_decode($configResponse['projectMetaData']['arraytaula']['value']);
            $anyActual = date("Y");

            foreach ($arraytaula as $e) {
                $elem = json_decode(json_encode($e), TRUE);
                if ($response['projectMetaData']['semestre']['value'] === "1") {
                    if ($elem['key']==="inici_semestre_1") {
                        $inici_semestre = $this->_obtenirData($elem['value'], $anyActual);
                    }
                    else if ($elem['key']==="fi_semestre_1") {
                        $fi_semestre = $this->_obtenirData($elem['value'], $anyActual);
                    }
                }
                else if ($response['projectMetaData']['semestre']['value'] === "2") {
                    if ($elem['key']==="inici_semestre_2") {
                        $inici_semestre = $this->_obtenirData($elem['value'], $anyActual);
                    }
                    else if ($elem['key']==="fi_semestre_2") {
                        $fi_semestre = $this->_obtenirData($elem['value'], $anyActual);
                    }
                }
            }

            if ($inici_semestre) {
                if ($inici_semestre > $fi_semestre) {
                    $fi_semestre = date_add ($fi_semestre, new DateInterval('P1Y'));
                }
                $interval = !(date >= $inici_semestre && date <= $fi_semestre);
                if ($interval) {
                    $response['canvi_semestre'] = TRUE;
                }
            }
        }

        return $response;
    }

    /**
     * Retorna una data UNIX a partir de:
     * @param string $diames en format "01/06"
     * @param string $anyActual
     * @return date
     */
    private function _obtenirData($diames, $anyActual) {
        $mesdia = explode("/", $diames);
        $data = date_create($anyActual."/".$mesdia[1]."/".$mesdia[0]);
        return date_create($anyActual."/".$mesdia[1]."/".$mesdia[0]);
    }

}