<?php
/**
 * ImportProjectAction: importa les dades d'un altre projecte
 * @author rafael <rclaver@xtec.cat>
 */
if (!defined('DOKU_INC')) die();

class ImportProjectAction extends ViewProjectAction {
    
    private function __getTreballEnEquip($tipus, $importData) {
        $ret = false;
        if($tipus == "EAF"){
            $ret = $importData["treballEquipEAF"];
        }
        return $ret;
    }
    
    private function __getNotaMinimaInstrumentsAvaluacio($tipus, $importData) {
        $conversor=[
            "AC" => 'notaMinimaAC',
            "PAF" => 'notaMinimaPAF',
            "EAF" => 'notaMinimaEAF',
            "JT" => 'notaMinimaJT'
        ];
        $default = 0;
        if(isset($importData[$conversor[$tipus]])){
            $ret = $importData[$conversor[$tipus]];
        }else{
            $ret = $default;
        }
        return $ret;
    }
    
    public function responseProcess() {
        //
        // 1. Comprobar que el tipo de proyecto a importar es adecuado
        //
        $model = $this->getModel();
        $projectID = $this->params[ProjectKeys::KEY_ID];
        $metaDataQuery = $model->getPersistenceEngine()->createProjectMetaDataQuery($projectID, "management", $this->params[ProjectKeys::KEY_PROJECT_TYPE]);
        $importProjectType = $metaDataQuery->getProjectType($this->params['project_import']);

        $metaDataQuery = $model->getPersistenceEngine()->createProjectMetaDataQuery($projectID, "management", $this->params[ProjectKeys::KEY_PROJECT_TYPE]);
        $actionCommand = $model->getModelAttributes(AjaxKeys::KEY_ACTION);
        $data_management = $metaDataQuery->getDataProject();
        $action = $model->getMetaDataActionWorkflowFile($data_management['workflow']['currentState'], $actionCommand);
        $validProjectTypes = $action['button']['parms']['DJO'][ProjectKeys::KEY_PROJECT_TYPE];

        if ($importProjectType == $validProjectTypes || in_array($importProjectType, $validProjectTypes))   {

            // 2. Verificar permisos sobre el proyecto a importar
            $importRoles = $this->getModelManager()->getProjectRoleData($this->params['project_import'], $importProjectType);
            $import_modelManager = AbstractModelManager::Instance($importProjectType);
            $import_authorization = $import_modelManager->getAuthorizationManager('editProject');
            $has_perm_group = $this->array_in_array($this->params['groups'], $import_authorization->getAllowedGroups());
                        $allowedUsers = array();
            $allowedRoles = $import_authorization->getAllowedRoles();
            foreach ($importRoles['roleData'] as $key => $value) {
                if(in_array($key, $allowedRoles)){
                    $users = explode(",", $value);
                    foreach ($users as $v) {
                        $allowedUsers []= trim($v);
                    }
                }
            }
            $has_perm_rol = in_array(WikiIocInfoManager::getInfo('client'), $allowedUsers);

            if ($has_perm_group || $has_perm_rol) {
                $dataProject = $model->getCurrentDataProject();
                $import_metaDataQuery = $model->getPersistenceEngine()->createProjectMetaDataQuery($this->params['project_import'], "main", $importProjectType);
                $import_data = $import_metaDataQuery->getDataProject($this->params['project_import'], $importProjectType, "main");

                // 3. Verificar una importació anterior
                if (empty($import_data['nsProgramacio']) || $import_data['nsProgramacio']!== $this->params["id"]) {

                    // 4. Verificar que les dades a importar son del tipus adequat
                    if (!$import_data["tipusCicle"] || $import_data["tipusCicle"] == "LOGSE"){
                        //JOSEP: TODO- Caldrà plantejar una importació diferent en funció del tipus de pojecte (ptfploe, sintesi o fct). Aquesta correspon a ptfploe.
                        // Taula d'importació
                        //camps directes
                        $dataProject['cicle']       = $import_data['cicle'];
                        $dataProject['duradaCicle'] = $import_data['duradaCicle'];
                    
                        if (!$import_data["tipusCicle"]) {
                            $dataProject['creditId']      = $import_data['creditId'];
                            $dataProject['credit']        = $import_data['credit'];
                            $dataProject['notaMinimaAC']  = $import_data['notaMinimaAC'];
                            $dataProject['notaMinimaEAF'] = $import_data['notaMinimaEAF'];
                            $dataProject['notaMinimaJT']  = $import_data['notaMinimaJT'];
                            $dataProject['notaMinimaPAF'] = $import_data['notaMinimaPAF'];
                            $dataProject['duradaPAF']     = $import_data['duradaPAF'];                    
                    
                            //Camps amb importació parcial
                            //taulaInstrumentsAvaluacio
//                            if (!is_array($import_data["dadesQualificacio"])) $import_data["dadesQualificacio"]= json_decode ($import_data["dadesQualificacio"], TRUE);
                            $import_data["dadesQualificacio"] = IocCommon::toArrayThroughArrayOrJson($import_data["dadesQualificacio"]);

                            if (isset($dataProject["taulaInstrumentsAvaluacio"])){
//                                if (!is_array($dataProject["taulaInstrumentsAvaluacio"])) $dataProject["taulaInstrumentsAvaluacio"]= json_decode ($dataProject["taulaInstrumentsAvaluacio"], TRUE);
                                $dataProject["taulaInstrumentsAvaluacio"] = IocCommon::toArrayThroughArrayOrJson($dataProject["taulaInstrumentsAvaluacio"]);
                            }else{
                                $dataProject["taulaInstrumentsAvaluacio"]=array();
                            }
                            foreach ($import_data["dadesQualificacio"] as $instAvToImport) {
                                $dataProject["taulaInstrumentsAvaluacio"][]=[
                                    "tipus" => $instAvToImport["tipus qualificació"]
                                    ,"id" => $instAvToImport["abreviació qualificació"]
                                    ,"descripcio" => ""
                                    ,"treballEnEquip" => $this->__getTreballEnEquip($instAvToImport["tipus qualificació"], $import_data)
                                    ,"esObligatori" => ($instAvToImport["tipus qualificació"]=="AC"?false:true)
                                    ,"notaMinima" => $this->__getNotaMinimaInstrumentsAvaluacio($instAvToImport["tipus qualificació"], $import_data)
                                    ,"ponderacio" => $instAvToImport["ponderació"]
                                ];
                            }
                    
                            //taulaDadesBlocs
//                            if (!is_array($dataProject['taulaDadesBlocs'])) $dataProject['taulaDadesBlocs'] = json_decode($dataProject['taulaDadesBlocs'], TRUE);
                            $dataProject['taulaDadesBlocs'] = IocCommon::toArrayThroughArrayOrJson($dataProject['taulaDadesBlocs']);

                            $B = ["crèdit", "1r. bloc", "2n. bloc", "3r. bloc"];
                            $T = ['bloc' => array_search($import_data['tipusBlocCredit'], $B),
                                  'horesBloc' => $import_data['durada'],
                                  'avaluacioInicial' => ($import_data['avaluacioInicial']==="SI") ];
                            $dataProject['taulaDadesBlocs'][] = $T;

                            $durada = 0;
                            $avaluacioInicial = 0;
                            foreach ($dataProject['taulaDadesBlocs'] as $item) {
                                $durada += $item["horesBloc"];
                                $avaluacioInicial += $item["avaluacioInicial"]?0:1;
                            }
                            $dataProject["durada"] = $durada;
                            if ($avaluacioInicial==0){
                                $dataProject["avaluacioInicial"] = "NO";
                            }elseif($avaluacioInicial==1){
                                $dataProject["avaluacioInicial"] = "C";
                            }else{
                                $dataProject["avaluacioInicial"] = "B";
                            }
                    
                            //taulaDadesUD
//                            if (!is_array($import_data["taulaDadesUD"])) $import_data["taulaDadesUD"]= json_decode ($import_data["taulaDadesUD"], TRUE);
                            $import_data["taulaDadesUD"] = IocCommon::toArrayThroughArrayOrJson($import_data["taulaDadesUD"]);

                            $dataProject["taulaDadesUD"] = IocCommon::toArrayThroughArrayOrJson($dataProject["taulaDadesUD"]);

//                            if(isset($dataProject["taulaDadesUD"])){
//                                if (!is_array($dataProject["taulaDadesUD"])) $dataProject["taulaDadesUD"]= json_decode ($dataProject["taulaDadesUD"], TRUE);
//                            }else{
//                                $dataProject["taulaDadesUD"]=array();
//                            }

                            if (empty($dataProject["taulaDadesUD"])){
                                foreach ($import_data['taulaDadesUD'] as $key => $toImport) {
                                    $dataProject['taulaDadesUD'][] = [
                                        "unitat didàctica" => $toImport["unitat didàctica"],
                                        "nom" => $toImport["nom"],
                                        "hores" => $toImport["hores"],
                                        "bloc" => $toImport["bloc"]
                                    ];
                                }
                            }
                    
                            //taulaNuclisActivitat
//                            if (!is_array($import_data["calendari"])) $import_data["calendari"]= json_decode ($import_data["calendari"], TRUE);
                            $import_data["calendari"] = IocCommon::toArrayThroughArrayOrJson( $import_data["calendari"]);

                            if (isset($dataProject["taulaNuclisActivitat"])){
//                                if (!is_array($dataProject["taulaNuclisActivitat"])) $dataProject["taulaNuclisActivitat"]= json_decode ($dataProject["taulaNuclisActivitat"], TRUE);
                                $dataProject["taulaNuclisActivitat"] = IocCommon::toArrayThroughArrayOrJson($dataProject["taulaNuclisActivitat"]);
                            }else{
                                $dataProject["taulaNuclisActivitat"]=array();
                            }
                            foreach ($import_data['calendari'] as $value) {
                                $U['unitat didàctica'] = $value['unitat didàctica'];
                                $U['nucli activitat'] = $value['nucli activitat'];
                                $U['nom'] = $value['nom'];
                                $U['hores'] = $value['hores'];
                                $dataProject['taulaNuclisActivitat'][] = $U;
                            }
                        }else{
                            $dataProject['creditId']                  = $import_data['modulId'];
                            $dataProject['credit']                    = $import_data['modul'];
//                            if (!is_array($dataProject['taulaDadesBlocs'])) $dataProject['taulaDadesBlocs'] = json_decode($dataProject['taulaDadesBlocs'], TRUE);
                            $dataProject['taulaDadesBlocs'] = IocCommon::toArrayThroughArrayOrJson($dataProject['taulaDadesBlocs']);

                            $T = ['bloc' => 0,
                                  'horesBloc' => $import_data['durada'],
                                  'avaluacioInicial' => ($import_data['avaluacioInicial']==="SI") ];
                            $dataProject['taulaDadesBlocs'][] = $T;
                            
                            $durada = 0;
                            $avaluacioInicial = 0;
                            foreach ($dataProject['taulaDadesBlocs'] as $item) {
                                $durada += $item["horesBloc"];
                                $avaluacioInicial += $item["avaluacioInicial"]?0:1;
                            }
                            $dataProject["durada"] = $durada;
                            if ($avaluacioInicial==0){
                                $dataProject["avaluacioInicial"] = "NO";
                            }elseif($avaluacioInicial==1){
                                $dataProject["avaluacioInicial"] = "C";
                            }else{
                                $dataProject["avaluacioInicial"] = "B";
                            }                            
                        }                            

                        $summary = "Importació de dades correcta des del projecte '{$this->params['project_import']}'";
                        if ($model->setDataProject(json_encode($dataProject), $summary)) {
                            $resp['info'] = self::generateInfo("info", $summary, $projectID);
                        }else {
                            $resp['info'] = self::generateInfo("error", "Error en la $summary", $projectID);
                            $resp['alert'] = "Error en la $summary";
                        }

                        if(empty($import_data['nsProgramacio'])){                        
                            $import_data['nsProgramacio'] = $projectID;
                            if (! $import_metaDataQuery->setMeta(json_encode($import_data), "main", "Dades importades pel projecte '$projectID'")) {
                                $resp['info'] = self::addInfoToInfo(self::generateInfo("error", "Error en l'actualització de les dades a '{$this->params['project_import']}' després de la importació.", $projectID), $resp['info']);
                            }
                        }
                        
                        $response = parent::responseProcess();
                        if ($resp["info"]) $response["info"]= $resp["info"];
                        if ($resp["alert"]) $response["alert"]= $resp["alert"];
                    }else {
                        throw new Exception("El tipus de projecte no és de tipus LOE i no és vàlid per a la importació.");
                    }
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
