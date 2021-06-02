<?php
/**
 * ImportProjectAction: importa les dades d'un altre projecte
 * @author rafael <rclaver@xtec.cat>
 */
if (!defined('DOKU_INC')) die();

class ImportProjectAction extends ViewProjectAction {
    
    public function responseProcess() {
        //
        // 1. Comprobar que el tipo de proyecto a importar es adecuado
        $model = $this->getModel();
        $projectID = $this->params[ProjectKeys::KEY_ID];
        $metaDataQuery = $model->getPersistenceEngine()->createProjectMetaDataQuery($projectID, "management", $this->params[ProjectKeys::KEY_PROJECT_TYPE]);
        $importProjectType = $metaDataQuery->getProjectType($this->params['project_import']);
        
        $metaDataQuery = $model->getPersistenceEngine()->createProjectMetaDataQuery($projectID, "management", $this->params[ProjectKeys::KEY_PROJECT_TYPE]);
        $actionCommand = $model->getModelAttributes(AjaxKeys::KEY_ACTION);
        $data_management = $metaDataQuery->getDataProject();
        $action = $model->getMetaDataActionWorkflowFile($data_management['workflow']['currentState'], $actionCommand);
        $validProjectTypes = $action['button']['parms']['DJO'][ProjectKeys::KEY_PROJECT_TYPE];

        if ($importProjectType == $validProjectTypes || in_array($importProjectType, $validProjectTypes)) {

            // 2. Verificar permisos sobre el proyecto a importar
            $importRoles = $this->getModelManager()->getProjectRoleData($this->params['project_import'], $importProjectType);
            $import_modelManager = AbstractModelManager::Instance($importProjectType);
            $import_authorization = $import_modelManager->getAuthorizationManager('editProject');
            $has_perm_group = IocCommon::array_in_array($this->params['groups'], $import_authorization->getAllowedGroups());
            $has_perm_rol = in_array(WikiIocInfoManager::getInfo('client'), $importRoles['roleData']);

            if ($has_perm_group || $has_perm_rol) {
                $dataProject = $model->getCurrentDataProject(false, false);
                $import_metaDataQuery = $model->getPersistenceEngine()->createProjectMetaDataQuery($this->params['project_import'], "main", $importProjectType);
                $import_data = $import_metaDataQuery->getDataProject($this->params['project_import'], $importProjectType, "main");

                // 3. Verificar una importació anterior
                if (empty($import_data['nsProgramacio'])) {
                    // 4. importar dades
                    //camps directes
                    $dataProject['tipusCicle'] = $import_data['tipusCicle'];
                    $dataProject['cicle']      = $import_data['cicle'];
                    $dataProject['durada']     = $import_data['durada'];

                    $summary = "Importació de dades correcta des del projecte '{$this->params['project_import']}'";
                    if ($model->setDataProject(json_encode($dataProject), $summary)) {
                        $resp['info'] = self::generateInfo("info", $summary, $projectID);
                    }else {
                        $resp['info'] = self::generateInfo("error", "Error en la $summary", $projectID);
                        $resp['alert'] = "Error en la $summary";
                    }

                    $import_data['nsProgramacio'] = $projectID;
                    if (! $import_metaDataQuery->setMeta(json_encode($import_data), "main", "Dades importades pel projecte '$projectID'")) {
                        $resp['info'] = self::generateInfo("error", "Error en l'actualització de les dades a '{$this->params['project_import']}' després de la importació.", $projectID);
                    }

                    $response = parent::responseProcess();
                    if ($resp["info"]) $response["info"] = $resp["info"];
                    if ($resp["alert"]) $response["alert"] = $resp["alert"];
                }else{
                    throw new Exception("El projecte '{$this->params['project_import']}' ja ha estat importat anteriorment.");
                }
            }else {
                throw new Exception("No tens permissos per a importar dades del projecte '{$this->params['project_import']}'.");
            }
        }else {
            throw new Exception("El tipus de projecte $importProjectType no és un tipus vàlid per a la importació.");
        }

        return $response;
    }

    // Busca si algún elemento de $array1 está incluido en $array2
    public function array_in_array($array1, $array2) {
        $has = FALSE;
        if (!is_array($array1)) $array1 = array($array1);
        if (!is_array($array2)) $array2 = array($array2);
        foreach ($array1 as $elem) {
            if (in_array($elem, $array2)) {
                $has = TRUE;
                break;
            }
        }
        return $has;
    }

}
