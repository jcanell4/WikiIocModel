<?php
/**
 * ImportProjectAction: importa les dades d'un altre projecte
 * @author rafael <rclaver@xtec.cat>
 */
if (!defined('DOKU_INC')) die();

class ImportProjectAction extends ViewProjectAction {
    
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
    
    private function __getAvaluacioInicialUF($tipus, $isFirst){
        $APT = ["NO", "INICI", "PER_UF"];
        $APR = ["No en té", "A l'inici del semestre", "A l'inici de la UF"];
        if ($tipus === $APT[1] && $isFirst)
            $ret = $APR[1];
        elseif ($tipus === $APT[2])
            $ret = $APR[2];
        else
            $ret = $APR[0];

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
            //
            // 2. Verificar permisos sobre el proyecto a importar
            //
            $import_modelManager = AbstractModelManager::Instance($importProjectType);
            $import_authorization = $import_modelManager->getAuthorizationManager('editProject');
            $has_perm_group = $this->array_in_array($this->params['groups'], $import_authorization->getAllowedGroups());
            $has_perm_rol = $this->array_in_array($this->params['roles'], $import_authorization->getAllowedRoles());

            if ($has_perm_group || $has_perm_rol) {
                $dataProject = $model->getCurrentDataProject();
                $import_metaDataQuery = $model->getPersistenceEngine()->createProjectMetaDataQuery($this->params['project_import'], "main", $importProjectType);
                $import_data = $import_metaDataQuery->getDataProject($this->params['project_import'], $importProjectType, "main");

                if (!$import_data["tipusCicle"] || $import_data["tipusCicle"] == "LOE"){
                    // 3. Verificar una importació anterior
                    if (empty($import_data['nsProgramacio'])) {
                        //JOSEP: TODO- Caldrà plantejar una importació diferrnt en funció del tipus de pojecte (ptfploe, sintesi o fct). Aquesta correspon a ptfploe.
                        // Taula d'importació
                        //camps directes
                        $dataProject['cicle']       = $import_data['cicle'];
                        $dataProject['modulId']     = $import_data['modulId'];
                        $dataProject['modul']       = $import_data['modul'];
                        $dataProject['duradaCicle'] = $import_data['duradaCicle'];

                        if (!$import_data["tipusCicle"]) {
                            $dataProject['notaMinimaAC']  = $import_data['notaMinimaAC'];
                            $dataProject['notaMinimaEAF'] = $import_data['notaMinimaEAF'];
                            $dataProject['notaMinimaJT']  = $import_data['notaMinimaJT'];
                            $dataProject['notaMinimaPAF'] = $import_data['notaMinimaPAF'];
                            $dataProject['duradaPAF']     = $import_data['duradaPAF'];
                        
                            //Camps amb importació parcial
                            //taulaInstrumentsAvaluacio
                            if (!is_array($import_data["dadesQualificacioUFs"])) $import_data["dadesQualificacioUFs"]= json_decode ($import_data["dadesQualificacioUFs"], TRUE);
                            if (isset($dataProject["taulaInstrumentsAvaluacio"])){
                                if (!is_array($dataProject["taulaInstrumentsAvaluacio"])) $dataProject["taulaInstrumentsAvaluacio"]= json_decode ($dataProject["taulaInstrumentsAvaluacio"], TRUE);
                            }else{
                                $dataProject["taulaInstrumentsAvaluacio"]=array();
                            }
                            foreach ($import_data["dadesQualificacioUFs"] as $instAvToImport) {
                                $dataProject["taulaInstrumentsAvaluacio"][]=[
                                     "unitat formativa" => $instAvToImport["unitat formativa"]
                                    ,"tipus" => $instAvToImport["tipus qualificació"]
                                    ,"id" => $instAvToImport["abreviació qualificació"]
                                    ,"descripcio" => ""
                                    ,"treballEnEquip" => false
                                    ,"esObligatori" => ($instAvToImport["tipus qualificació"]=="AC"?false:true)
                                    ,"notaMinima" => $this->__getNotaMinimaInstrumentsAvaluacio($instAvToImport["tipus qualificació"], $import_data)
                                    ,"ponderacio" => $instAvToImport["ponderació"]
                                ];
                            }                        
                            //taulaDadesBlocs
                            if (!is_array($dataProject['taulaDadesBlocs'])) $dataProject['taulaDadesBlocs'] = json_decode($dataProject['taulaDadesBlocs'], TRUE);
                            $B = ["mòdul", "1r. bloc", "2n. bloc", "3r. bloc"];
                            $T = ['bloc' => array_search($import_data['tipusBlocModul'], $B),
                                  'horesBloc' => $import_data['durada']];
                            $dataProject['taulaDadesBlocs'][] = $T;
                            
                            //taulaDadesUF
                            if (!is_array($import_data["taulaDadesUF"])) $import_data["taulaDadesUF"]= json_decode ($import_data["taulaDadesUF"], TRUE);
                            if (isset($dataProject["taulaDadesUF"])){
                                if (!is_array($dataProject["taulaDadesUF"])) $dataProject["taulaDadesUF"]= json_decode ($dataProject["taulaDadesUF"], TRUE);
                            }else{
                                $dataProject["taulaDadesUF"]=array();
                            }
                            if (empty($dataProject["taulaDadesUF"])){
                                foreach ($import_data['taulaDadesUF'] as $key => $toImport) {
                                    $dataProject['taulaDadesUF'][] = [
                                        "unitat formativa" => $toImport["unitat formativa"],
                                        "nom" => $toImport["nom"],
                                        "horesMinimes" => $toImport["hores"],
                                        "horesLLiureDisposicio" => 0,
                                        "hores" => $toImport["hores"],
                                        "ponderació" => $toImport["ponderació"],
                                        "avaluacioInicial" => $this->__getAvaluacioInicialUF($import_data["avaluacioInicial"], $key==0),
                                        "bloc" => $toImport["bloc"],
                                        "ordreImparticio" => $key + 1
                                    ];
                                }
                            }
                            //taulaDadesNuclisFormatius
                            if (!is_array($import_data["taulaDadesUnitats"])) $import_data["taulaDadesUnitats"]= json_decode ($import_data["taulaDadesUnitats"], TRUE);
                            if (isset($dataProject["taulaDadesNuclisFormatius"])){
                                if (!is_array($dataProject["taulaDadesNuclisFormatius"])) $dataProject["taulaDadesNuclisFormatius"]= json_decode ($dataProject["taulaDadesNuclisFormatius"], TRUE);
                            }else{
                                $dataProject["taulaDadesNuclisFormatius"]=array();
                            }
                            foreach ($import_data['taulaDadesUnitats'] as $value) {
                                if (isset($U) && $U['unitat formativa'] == $value['unitat formativa']){
                                    $nf++;
                                }else{
                                    $nf=1;
                                }
                                $U['unitat formativa'] = $value['unitat formativa'];
                                $U['nucli formatiu'] = $nf;
                                $U['unitat al pla de treball'] = $value['unitat'];
                                $U['nom'] = $value['nom'];
                                $U['hores'] = $value['hores'];
                                $dataProject['taulaDadesNuclisFormatius'][] = $U;
                            }
                        }else{
                            if (!is_array($dataProject['taulaDadesBlocs'])) $dataProject['taulaDadesBlocs'] = json_decode($dataProject['taulaDadesBlocs'], TRUE);
                            $T = ['bloc' => 0,
                                  'horesBloc' => $import_data['durada']];
                            $dataProject['taulaDadesBlocs'][] = $T;
                        }
                        
                        //resultatsAprenentatge
                        if (!is_array($import_data["resultatsAprenentatge"])) $import_data["resultatsAprenentatge"]= json_decode ($import_data["resultatsAprenentatge"], TRUE);
                        if (isset($dataProject["resultatsAprenentatge"])){
                            if (!is_array($dataProject["resultatsAprenentatge"])) $dataProject["resultatsAprenentatge"]= json_decode ($dataProject["resultatsAprenentatge"], TRUE);
                        }else{
                            $dataProject["resultatsAprenentatge"]=array();
                        }
                        foreach ($import_data["resultatsAprenentatge"] as $value) {
                            if (preg_match('/(UF(\d).{0,1})RA(\d)/', $value['id'], $match)) {
                                $Z['uf'] = $match[2];
                                $Z['ra'] = $match[3];
                            }elseif (preg_match('/RA(\d)(.{0,1}(UF(\d)){0,1})/', $value['id'], $match)) {
                                $Z['uf'] = $match[4];
                                $Z['ra'] = $match[1];
                            }else {
                                $Z['uf'] = "";
                                $Z['ra'] = "";
                            }
                            $Z['descripcio'] = $value['descripcio'];
                            $Z['ponderacio'] = "";
                            $Z['hores'] = 0;
                            $dataProject['resultatsAprenentatge'][] = $Z;
                        }

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
                        if($resp["info"]){
                            $response["info"]= $resp["info"];
                        }
                        if($resp["alert"]){
                            $response["alert"]= $resp["alert"];
                        }

                    }else {
                        $response['info'] = self::generateInfo("error", "El projecte '{$this->params['project_import']}' ja ha estat importat anteriorment.", $projectID);
                        $response['alert'] = "El projecte '{$this->params['project_import']}' ja ha estat importat anteriorment.";
                    }
                }else{
                    $response['alert'] = "El tipus de projecte no és de tipus LOE i no és vàlid per a la importació.";                    
                }
            }else {
                $response['alert'] = "No tens permissos per a importar dades del projecte '{$this->params['project_import']}'.";
            }
        }else {
            $response['alert'] = "El tipus de projecte $importProjectType no és un tipus vàlid per a la importació.";
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
