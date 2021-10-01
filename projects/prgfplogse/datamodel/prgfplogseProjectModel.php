<?php
/**
 * ptfploeProjectModel
 * @culpable Josep Cañellas
 */
if (!defined("DOKU_INC")) die();

class prgfplogseProjectModel extends UniqueContentFileProjectModel{
    
    public function __construct($persistenceEngine)  {
        parent::__construct($persistenceEngine);
        $this->needGenerateAction=false;
        $this->externalCallMethods["createTableRAPonderation"]="__createTableRAPonderation";
    }
    
    public function directGenerateProject() {
        //4. Establece la marca de 'proyecto generado'
        return $this->projectMetaDataQuery->setProjectGenerated();
    }

    public function validateFields($data=NULL){
        //EL responsable no pot ser buit
        if(isset($data["responsable"]) && empty(trim($data["responsable"]))){
            throw new InvalidDataProjectException(
                    $this->id,
                    "El camp responsable no pot quedar buit"
            );            
        }
    }

    public function getErrorFields($data=NULL) {
        $result   = array();
        $iaTable  = $data["taulaInstrumentsAvaluacio"]['value']; if (!is_array($iaTable)) $iaTable = json_decode($iaTable, TRUE);
        $naTable  = $data["taulaNuclisActivitat"]['value'];      if (!is_array($naTable)) $naTable = json_decode($naTable, TRUE);
        $udTable  = $data["taulaDadesUD"]['value'];              if (!is_array($udTable)) $udTable = json_decode($udTable, TRUE);
        $obTable  = $data["objectius"]['value'];                 if (!is_array($obTable)) $obTable = json_decode($obTable, TRUE);
        $conTable = $data["conceptes"]['value'];                 if (!is_array($conTable)) $conTable = json_decode($conTable, TRUE);
        $proTable = $data["procediments"]['value'];              if (!is_array($proTable)) $proTable = json_decode($proTable, TRUE);
        $actTable = $data["actituds"]['value'];                  if (!is_array($actTable)) $actTable = json_decode($actTable, TRUE);
        
        //Camps obligatoris
        $responseType = "SINGLE_MESSAGE";
        $message = WikiIocLangManager::getLang("El camp %s és obligatori. Cal que %s.");
        $campsAComprovar=[
             ["typeField"=>"SF","field"=>"departament", "accioNecessaria"=>"hi poseu el nom del departament"] 
            ,["typeField"=>"SF","field"=>"cicle", "accioNecessaria"=>"hi poseu el nom del cicle"]
            ,["typeField"=>"SF","field"=>"creditId", "accioNecessaria"=>"hi poseu el codi del crèdit"]
            ,["typeField"=>"SF","field"=>"credit", "accioNecessaria"=>"hi poseu el nom del crèdit"]
//            ,["typeField"=>"SF","field"=>"estrategiesMetodologiques", "accioNecessaria"=>"hi poseu les estratègies moetodològiques del crèdit"]
            ,["typeField"=>"TF","field"=>"taulaDadesUD", "accioNecessaria"=>"hi afegiu les unitats formatives del crèdit"]
            ,["typeField"=>"TF","field"=>"taulaInstrumentsAvaluacio", "accioNecessaria"=>"hi afegiu els instruments d'avaluació del crèdit"]
            ,["typeField"=>"TF","field"=>"objectius", "accioNecessaria"=>"hi afegiu els objectius de cada UD"]
            ,["typeField"=>"TF","field"=>"conceptes", "accioNecessaria"=>"hi afegiu els conceptes associats a cada UD"]
            ,["typeField"=>"TF","field"=>"procediments", "accioNecessaria"=>"hi afegiu els procediments associats a cada UD"]
            ,["typeField"=>"TF","field"=>"actituds", "accioNecessaria"=>"hi afegiu les actituds associades a cada UD"]
            ,["typeField"=>"TF","field"=>"taulaNuclisActivitat", "accioNecessaria"=>"hi afegiu els nuclis d'activitat associats a cada UD"]
            ,["typeField"=>"SF","field"=>"cc_raonsModificacio", "accioNecessaria"=>"hi assigneu una raó per la modificació actual de la programació"]
            ,["typeField"=>"SF","field"=>"autor", "accioNecessaria"=>"hi assigneu un autor"]
            ,["typeField"=>"OF","field"=>"cc_dadesAutor#carrec", "accioNecessaria"=>"hi assigneu el càrrec de l'autor"]
            ,["typeField"=>"SF","field"=>"revisor", "accioNecessaria"=>"hi assigneu un revisor"]
            ,["typeField"=>"OF","field"=>"cc_dadesRevisor#carrec", "accioNecessaria"=>"hi assigneu el càrrec del revisor"]
            ,["typeField"=>"SF","field"=>"validador", "accioNecessaria"=>"hi assigneu un validador"]
            ,["typeField"=>"OF","field"=>"cc_dadesValidador#carrec", "accioNecessaria"=>"hi assigneu el càrrec del validador"]
        ];
        foreach ($campsAComprovar as $item) {
            if($item["typeField"]=="SF" && (!isset($data[$item["field"]]) || $data[$item["field"]]["value"]==$data[$item["field"]]["default"])){
                $result["ERROR"][] = [
                        'responseType' => $responseType,
                        'field' => $item["field"],
                        'message' => sprintf($message
                                            ,$item["field"]
                                            ,$item["accioNecessaria"])
                    ];                
            }elseif($item["typeField"]=="TF" && (!isset($data[$item["field"]]) || empty ($data[$item["field"]]["value"]) || $data[$item["field"]]["value"]=="[]" || $data[$item["field"]]["value"]==$data[$item["field"]]["default"])){
                $result["ERROR"][] = [
                        'responseType' => $responseType,
                        'field' => $item["field"],
                        'message' => sprintf($message
                                            ,$item["field"]
                                            ,$item["accioNecessaria"])
                    ];                
            }else if($item["typeField"]=="OF"){
                $keys = explode("#", $item["field"]);
                $error=false;
                $dataf = $data;
                for($i=0; !$error && $i<count($keys); $i++){
                    if(!isset($dataf[$keys[$i]]) || $dataf[$keys[$i]]["value"]==$dataf[$keys[$i]]["default"]){
                        $error=true;
                    }else{
                        $dataf = $dataf[$keys[$i]]["value"];
                    }
                }
                if($error){
                    $result["ERROR"][] = [
                        'responseType' => $responseType,
                        'field' => $item["fieldName"] ? $item["fieldName"] : $item["field"],
                        'message' => sprintf($message
                                            ,$item["field"]
                                            ,$item["accioNecessaria"])
                    ];                     
                }
            }
        }
        
        $totalUDs = array();
        $totalCredit = 0;
        if (!empty($naTable)) {
            foreach ($naTable as $item){
                if(!isset($totalUDs[$item["unitat didàctica"]])){
                    $totalUDs[$item["unitat didàctica"]]=0;
                }
                $totalUDs[$item["unitat didàctica"]] += $item["hores"];
                $totalCredit += $item["hores"];
            }
        }


        if (!empty($udTable)) {
            foreach ($udTable as $item) {
                if ($item["hores"] != $totalUDs[$item["unitat didàctica"]]){
                    $result["ERROR"][] = [
                        'responseType' => $responseType,
                        'field' => 'taulaDadesUD',
                        'message' => sprintf("A la taula d'Unitats Formatives(taulaDadesUD), les hores de la unitat didàctica %s no coincideixen amb la suma de les hores dels seus nuclis d'activitat (hores UD=%d, però suma hoes NA=%d)."
                                            ,$item["unitat didàctica"]
                                            ,$item["hores"]
                                            ,$totalUDs[$item["unitat didàctica"]])
                    ];
                }
            }
        }
        
        //Comporvació ponderacions
        if (!empty($iaTable)) {
            $sum = 0;
            foreach ($iaTable as $item) {
                $sum += $item["ponderacio"];
            }
            
            foreach ($iaTable as $item) {
                if ($item['tipus'] == "PAF" && $item["ponderacio"]/$sum > 0.6) {
                    $result["ERROR"][] = [
                        'responseType' => $responseType,
                        'field' => 'taulaInstrumentsAvaluacio',
                        'message' => sprintf("A la taula dels intsruments d'avaluació (taulaInstrumentsAvaluacio), la ponderació de la PAF pren el valor de %d sobre %d i per tant, supera el llindar del 60%s"
                                            ,$item["ponderacio"]
                                            ,$sum
                                            ,"%")
                    ];
                }
            }
            
            if(abs($sum-100.0)>2.0E-14 && abs($sum-$totalCredit)>2.0E-14){
                $result["WARNING"][] = [
                    'responseType' => $responseType,
                    'field' => 'taulaInstrumentsAvaluacio',
                    'message' => sprintf("A la taula dels intsruments d'avaluació (taulaInstrumentsAvaluacio), la suma de les ponderacions no és 100, ni coincideix amb el nombre d'hores. Reviseu si es tracta d'un error."
                                        ,$key)
                ];
            }
        }
        
        if($data["durada"]['value']!=$totalCredit){
                $result["ERROR"][] = [
                    'responseType' => $responseType,
                    'field' => 'durada',
                    'message' => sprintf("El valor del camp durada (hores del crèdit) no coeincideix amb la suma d'hores de les unitats didàctiques. La durada val %d i al suma d'hores de les unitats %d."
                                        ,$data["durada"]['value']
                                        ,$totalCredit)
                ];
        }
        if(!empty($udTable)){
            foreach ($udTable as $udValue) {
                $trobat=false;
                foreach ($obTable as $obValue) {
                    if($obValue["ud"] == $udValue["unitat didàctica"]){
                        $trobat=TRUE;
                        break;
                    }
                }
                if(!$trobat){
                    $result["ERROR"][] = [
                        'responseType' => $responseType,
                        'field' => 'objectius',
                        'message' => sprintf("No i ha objectius de la unitat diàctica %d. Cal afegir-ne"
                                            ,$udValue["unitat didàctica"]
                                            ,$totalCredit)
                    ];
                }
            }
        }        
        if (empty($result)) {
            $responseType = "NOERROR";
            $result[$responseType] = WikiIocLangManager::getLang("No s'han detectat errors a les dades del projecte");
        }
        return $result;
    }

    public function updateCalculatedFieldsOnSave($data, $originalDataKeyValue=FALSE) {
        $data = parent::updateCalculatedFieldsOnSave($data, $originalDataKeyValue);

        $udTable = $data["taulaDadesUD"];
        if ($udTable && !is_array($udTable)){
            $udTable = json_decode($udTable, TRUE);
        }
        $insAvTable = $data["taulaInstrumentsAvaluacio"];
        if ($insAvTable && !is_array($insAvTable)){
            $insAvTable = json_decode($insAvTable, TRUE);
        }
        //Calcula les hores per bloc i si cal, la pnderació.
        $blocTable = array();
        $total = 0;
        if ($udTable) {
            $blocTotal = 0;
            $i = 0;
            $size = count($udTable);
            while($i < $size) {
                $total += $udTable[$i]["hores"];
                $blocTotal += $udTable[$i]["hores"];
                $currentBloc = $udTable[$i]["bloc"];
                $i++;
                if ($i==$size || $udTable[$i]['bloc'] != $currentBloc){
                    $blocRow = array();
                    $blocRow["bloc"] = $currentBloc;
                    $blocRow["horesBloc"] = $blocTotal;
                    $blocTable[] = $blocRow;
                    $blocTotal = 0;
                 }
            }
        }

        $notaMinimaAC = 10;
        $notaMinimaPAF = 10;
        $notaMinimaEAF = 10;
        $notaMinimaJT = 10;
        if ($insAvTable) {
            foreach ($insAvTable as $item) {
                if ($item["tipus"] == "AC"){
                    if ($notaMinimaAC > $item["notaMinima"]){
                        $notaMinimaAC = $item["notaMinima"];
                    }
                }
                if ($item["tipus"] == "EAF"){
                    if($notaMinimaEAF > $item["notaMinima"]){
                        $notaMinimaEAF = $item["notaMinima"];
                    }
                }
                if ($item["tipus"] == "JT"){
                    if ($notaMinimaJT > $item["notaMinima"]){
                        $notaMinimaJT = $item["notaMinima"];
                    }
                }
                if ($item["tipus"] == "PAF"){
                    if($notaMinimaPAF > $item["notaMinima"]){
                        $notaMinimaPAF = $item["notaMinima"];
                    }
                }
            }
        }
        if ($notaMinimaAC == 10){
            $notaMinimaAC = 0;
        }
        if ($notaMinimaPAF == 10){
            $notaMinimaPAF = 4;
        }
        if ($notaMinimaEAF == 10){
            $notaMinimaEAF = 4;
        }
        if ($notaMinimaJT == 10){
            $notaMinimaJT = 0;
        }

        $data["taulaDadesBlocs"] = $blocTable;
        //$data["durada"] = $total;
        $data["notaMinimaAC"] = $notaMinimaAC;
        $data["notaMinimaEAF"] = $notaMinimaEAF;
        $data["notaMinimaJT"] = $notaMinimaJT;
        $data["notaMinimaPAF"] = $notaMinimaPAF;

        // Dades de la gestió de la darrera modificació
        $this->dadesActualsGestio($data);

        // Històric del control de canvis
        $this->modifyLastHistoricGestioDocument($data);

        return $data;
    }

    private function dadesActualsGestio(&$data) {
        if ($data['autor']) $data['cc_dadesAutor']['nomGestor'] = $this->getUserName($data['autor']);
        if ($data['revisor']) $data['cc_dadesRevisor']['nomGestor'] = $this->getUserName($data['revisor']);
        if ($data['validador']) $data['cc_dadesValidador']['nomGestor'] = $this->getUserName($data['validador']);
    }

    public function clearQualityRolesData(&$data){
        if(!is_array($data['cc_dadesAutor'])){
            $data['cc_dadesAutor'] = json_decode($data['cc_dadesAutor'], TRUE);
        }
        if(!is_array($data['cc_dadesRevisor'])){
            $data['cc_dadesRevisor'] = json_decode($data['cc_dadesRevisor'], TRUE);
        }
        if(!is_array($data['cc_dadesValidador'])){
            $data['cc_dadesValidador'] = json_decode($data['cc_dadesValidador'], TRUE);
        }
        $data['cc_dadesAutor']['dataDeLaGestio'] = "";
        $data['cc_dadesAutor']['signatura'] = "pendent";
        $data['cc_dadesRevisor']['dataDeLaGestio'] = "";
        $data['cc_dadesRevisor']['signatura'] = "pendent";
        $data['cc_dadesValidador']['dataDeLaGestio'] = "";
        $data['cc_dadesValidador']['signatura'] = "pendent";        
        $data['cc_raonsModificacio'] = "";        
    }

    public function updateSignature(&$data, $role, $date=FALSE) {        
        $keyConverter = ["cc_dadesAutor" =>"autor", "cc_dadesRevisor" => "revisor", "cc_dadesValidador" => "validador"];
        $data[$role]['nomGestor'] = $this->getUserName($data[$keyConverter[$role]]);;
        $data[$role]['dataDeLaGestio'] = $date?$date:date("Y-m-d");
        $data[$role]['signatura'] = "signat";
    }
    
    public function modifyLastHistoricGestioDocument(&$data, $date=false) {
        if ($data['cc_historic'] === '"[]"') {
            $data['cc_historic'] = array();
        }elseif (!is_array($data['cc_historic'])){
            $data['cc_historic'] = json_decode($data['cc_historic'], true);
        }
        if (is_array($data['cc_historic'])) {
            $hist['data'] = $date ? $date : date("Y-m-d");
            $hist['autor'] = $this->getUserName($data['autor']);
            $hist['modificacions'] = $data['cc_raonsModificacio'] ? $data['cc_raonsModificacio'] : "";
            $c = (count($data['cc_historic']) < 1) ? 0 : count($data['cc_historic'])-1;
            $data['cc_historic'][$c] = $hist;
        }
    }
    
    public function addHistoricGestioDocument(&$data) {
        if (!is_array($data['cc_historic'])){
            $data['cc_historic'] = json_decode($data['cc_historic'], true);
        }
        $hist['data'] = date("Y-m-d");
        $hist['autor'] = $this->getUserName($data['autor']);
        $hist['modificacions'] = $data['cc_raonsModificacio'] ? $data['cc_raonsModificacio'] : "";
        $data['cc_historic'][] = $hist;
    }

    private function getUserName($users) {
        global $auth;
        $retUser = "";
        $u = explode(",", $users);
        foreach ($u as $user) {
            $retUser .= $auth->getUserData($user)['name'] . ", ";
        }
        return trim($retUser, ", ");
    }

    /**
     * @override Guarda los datos en el momento de la cración
     * @param array $toSet (s'ha generat a l'Action corresponent)
     */
    public function createData($toSet) {
        parent::createData($toSet);

        //Creació de l'arxiu de metadades corresponent al workflow
        $subSet = "management";
        $metaDataQuery = $this->getPersistenceEngine()->createProjectMetaDataQuery($this->id, $subSet, $this->projectType);
        $metaDataManagement['workflow']['currentState'] = "creating";
        $metaDataQuery->setMeta(json_encode($metaDataManagement), $subSet, "creació", NULL);
    }

}
