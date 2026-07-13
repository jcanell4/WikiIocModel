<?php
if (!defined('DOKU_INC')) die();

class ProjectUpdateDataAction extends ViewProjectAction {

    protected function runAction() {
        $projectType = $this->params[ProjectKeys::KEY_PROJECT_TYPE];
        $metaDataSubSet = ($this->params[ProjectKeys::KEY_METADATA_SUBSET]) ? $this->params[ProjectKeys::KEY_METADATA_SUBSET] : ProjectKeys::VAL_DEFAULTSUBSET;

        $projectModel = $this->getModel();
        $response = $projectModel->getCurrentDataProject();
        $response["calendari"] = "[]";

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
            $this->applyUpdateMode($arraytaula, $response);
            $restoreData = !$projectModel->getProjectSystemSubSetAttr("updatedDate");
            if($restoreData){
                //La primera vegada aquests camps no s'actualitzen!
                $datesAC = array_key_exists("datesAC", $response) ? $response["datesAC"] : NULL;
                $datesEAF = array_key_exists("datesEAF", $response) ? $response["datesEAF"] : NULL;
                $datesJT = array_key_exists("datesJT", $response) ? $response["datesJT"] : NULL;
            }
            if(ManagerProjectUpdateProcessor::updateAll($arraytaula, $response)){
                if($restoreData){
                    //La primera vegada aquests camps no s'actualitzen!
                    if (array_key_exists("datesAC", $response)) {
                        $response["datesAC"] = $datesAC;
                    }
                    if (array_key_exists("datesEAF", $response)) {
                        $response["datesEAF"] = $datesEAF;
                    }
                    if (array_key_exists("datesJT", $response)) {
                        $response["datesJT"] = $datesJT;
                    }
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

    private function applyUpdateMode(&$arraytaula, &$response) {
        $anual = $this->isTrueValue(array_key_exists("anual", $response) ? $response["anual"] : FALSE);
        $semestre = array_key_exists("semestre", $response) ? intval($response["semestre"]) : 1;

        $incrementValue = 5;
        $incrementUnit = "M";

        if ($anual) {
            $incrementValue = 1;
            $incrementUnit = "Y";
        } else if ($semestre === 2) {
            $incrementValue = 7;
            $response["semestre"] = "1";
        } else {
            $incrementValue = 5;
            $response["semestre"] = "2";
        }

        $this->ensureIncrementDatesRule($arraytaula);

        foreach ($arraytaula as &$elem) {
            if ($elem["key"] === "increment_dates" && $elem["type"] === "arrayIncrement") {
                $elem["value"] = (string) $incrementValue;
                $params = IocCommon::toArrayThroughArrayOrJson($elem["parameters"]);
                $params["unit"] = $incrementUnit;
                $elem["parameters"] = json_encode($params);
                break;
            }
        }
    }

    private function ensureIncrementDatesRule(&$arraytaula) {
        foreach ($arraytaula as $elem) {
            if ($elem["key"] === "increment_dates" && $elem["type"] === "arrayIncrement") {
                return;
            }
        }

        $arraytaula[] = [
            "key" => "increment_dates",
            "type" => "arrayIncrement",
            "value" => "5",
            "parameters" => json_encode([
                "fields" => ["datesAC"],
                "keysOfArray" => [["enunciat", "lliurament", "qualificació"]],
                "conditions" => [[]],
                "type" => "data",
                "unit" => "M"
            ])
        ];
    }

    private function isTrueValue($value) {
        if (is_bool($value)) {
            return $value;
        }
        if (is_numeric($value)) {
            return intval($value) !== 0;
        }
        $normalized = strtolower(trim((string) $value));
        return in_array($normalized, ["1", "true", "yes", "si"]);
    }

}
