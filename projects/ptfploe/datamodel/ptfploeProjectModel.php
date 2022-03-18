<?php
/**
 * ptfploeProjectModel
 * @culpable Rafael Claver
 */
if (!defined("DOKU_INC")) die();

class ptfploeProjectModel extends MoodleUniqueContentFilesProjectModel {

    public function __construct($persistenceEngine)  {
        parent::__construct($persistenceEngine);
        $this->needGenerateAction=false;        
    }

    public function getProjectDocumentName() {
        $ret = $this->getCurrentDataProject();
        return $ret['fitxercontinguts'];
    }

    public function updateCalculatedFieldsOnRead($data, $originalDataKeyValue=FALSE, $subset=FALSE) {
        if($subset!==FALSE && $subset!=ProjectKeys::VAL_DEFAULTSUBSET){
            return parent::updateCalculatedFieldsOnRead($data, $subset);
        }
        
        $data = parent::updateCalculatedFieldsOnRead($data, $subset);
        $isArray = is_array($data);
        $values = $isArray ? $data : json_decode($data, true);
        $originalValues = $isArray ? $originalDataKeyValue : json_decode($originalDataKeyValue, true);

        $ufTable = IocCommon::toArrayThroughArrayOrJson($values["taulaDadesUF"]);
        $originalufTable = IocCommon::toArrayThroughArrayOrJson($originalValues["taulaDadesUF"]);
        $taulaDadesUnitats = IocCommon::toArrayThroughArrayOrJson($values["taulaDadesUnitats"]);
        $originalTaulaDadesUnitats = IocCommon::toArrayThroughArrayOrJson($originalValues["taulaDadesUnitats"]);
        $resultatsAprenentatge = IocCommon::toArrayThroughArrayOrJson($values["resultatsAprenentatge"]);
        $originalResultatsAprenentatge = IocCommon::toArrayThroughArrayOrJson($originalValues["resultatsAprenentatge"]);
        $dadesQualificacioUFs = IocCommon::toArrayThroughArrayOrJson($values["dadesQualificacioUFs"]);
        $originalDadesQualificacioUFs = IocCommon::toArrayThroughArrayOrJson($originalValues["dadesQualificacioUFs"]);
        $blocId = array_search($values["tipusBlocModul"], ["mòdul", "1r. bloc", "2n. bloc", "3r. bloc"]);
        if($values["nsProgramacio"]){
            $dataPrg = $this->getRawDataProjectFromOtherId($values["nsProgramacio"]);
            if(!is_array($dataPrg)){
                $dataPrg = json_decode($dataPrg, true);
            }
            $taulaDadesNF = IocCommon::toArrayThroughArrayOrJson($dataPrg["taulaDadesNuclisFormatius"]);
            $taulaDadesUFPrg = IocCommon::toArrayThroughArrayOrJson($dataPrg["taulaDadesUF"]);
            $taulaDadesNFFiltrada = array();
            if (!empty($taulaDadesNF)) {
                foreach ($taulaDadesNF as $row) {
                    $rowBlocId = $this->getBlocIdFromTaulaUF($taulaDadesUFPrg, $row["unitat formativa"]);
                    if($rowBlocId==$blocId){
                        $taulaDadesNFFiltrada[] = $row;
                    }
                }
            }
        }else{
            $taulaDadesNF = FALSE;
        }
        
        if($dataPrg){
            if(isset($originalValues["cicle"]) && !empty($originalValues["cicle"])){
                $values["cicle"] = $originalValues["cicle"];
            }
            if(isset($originalValues["modul"]) && !empty($originalValues["modul"])){
                $values["modul"] = $originalValues["modul"];
            }
        }
        
        if(!empty($taulaDadesNFFiltrada)){
             for ($i=0; $i<count($taulaDadesUnitats); $i++){
                if(isset($originalTaulaDadesUnitats[$i]["unitat"])){                  
                    $taulaDadesUnitats[$i]["unitat"] = $originalTaulaDadesUnitats[$i]["unitat"];
                }
                if(empty($originalTaulaDadesUnitats[$i]["nom"])){
                    $taulaDadesUnitats[$i]["nom"] = $this->getRowFromField($taulaDadesNFFiltrada, "unitat al pla de treball",  $taulaDadesUnitats[$i]["unitat"], $i, true)["nom"];
                }else{
                    $taulaDadesUnitats[$i]["nom"] = $originalTaulaDadesUnitats[$i]["nom"];
                }
             }
        }
        $values["taulaDadesUnitats"] = $taulaDadesUnitats;

        for ($i=0; $i<count($resultatsAprenentatge); $i++){
           if(!empty($originalResultatsAprenentatge[$i]["id"])){
               $resultatsAprenentatge[$i]["id"] = $originalResultatsAprenentatge[$i]["id"];
           }
        }
        $values["resultatsAprenentatge"] = $resultatsAprenentatge;
        
        for ($i=0; $i<count($dadesQualificacioUFs); $i++){
           if($dadesQualificacioUFs[$i]["abreviació qualificació"]==$originalDadesQualificacioUFs[$i]["abreviació qualificació"]
                   || $dadesQualificacioUFs[$i]["tipus qualificació"]==$originalDadesQualificacioUFs[$i]["tipus qualificació"]
                   && $dadesQualificacioUFs[$i]["tipus qualificació"]=="PAF"){
               $pos = $i;
           }else{
               $pos = -1;
               $j=0;
               foreach ($originalDadesQualificacioUFs as $item) {
                   if($item["abreviació qualificació"]==$dadesQualificacioUFs[$i]["abreviació qualificació"]
                            || $dadesQualificacioUFs[$i]["tipus qualificació"]==$item["tipus qualificació"]
                            && $item["tipus qualificació"]=="PAF"){
                       $pos = $j;
                   }
                   $j++;
               }
           }
           if($pos!=-1 && !empty($originalDadesQualificacioUFs[$pos]["descripció qualificació"])){
               $dadesQualificacioUFs[$i]["descripció qualificació"] = $originalDadesQualificacioUFs[$pos]["descripció qualificació"];
           }
        }
        $values["dadesQualificacioUFs"] = $dadesQualificacioUFs;
        
        foreach ($ufTable as $key => $value) {
            if($ufTable[$key]["ponderació"]=="0"){
                $ufTable[$key]["ponderació"]=$ufTable[$key]["hores"];
            }
            $ufTable[$key]["ordreImparticio"] = $originalufTable[$key]["ordreImparticio"];
        }
        
        $nAvaluacioInicial=0;
        if($taulaDadesUFPrg){
            foreach ($taulaDadesUFPrg as $item) {
                if($blocId == $item["bloc"] && $item["avaluacioInicial"]!=="No en té"){
                    $nAvaluacioInicial++;
                }
            }            
        }
        
        $values["taulaDadesUF"]=$ufTable;
        if($nAvaluacioInicial==0){
            $avaluacioInicial = "NO";
        }elseif($nAvaluacioInicial==1){
            $avaluacioInicial = "INICI";
        }else{
            $avaluacioInicial = "PER_UF";
        }
        $values["avaluacioInicial"]= $avaluacioInicial;

        $data = $isArray?$values:json_encode($values);
        return $data;
    }
    
    /**
     * Calcula el valor de los campos calculables
     * @param JSON $data
     */
    public function updateCalculatedFieldsOnSave($data, $originalDataKeyValue=FALSE, $subset=FALSE) {
        if($subset!==FALSE && $subset!=ProjectKeys::VAL_DEFAULTSUBSET){
            return parent::updateCalculatedFieldsOnSave($data, $subset, $subset);
        }

        $isArray = is_array($data);
        $values = $isArray?$data:json_decode($data, true);

        $taulaDadesUF = IocCommon::toArrayThroughArrayOrJson($values["taulaDadesUF"]);
        $taulaDadesUnitats = IocCommon::toArrayThroughArrayOrJson($values["taulaDadesUnitats"]);
        $taulaCalendari = IocCommon::toArrayThroughArrayOrJson($values["calendari"]);
        $resultatsAprenentatge = IocCommon::toArrayThroughArrayOrJson($values["resultatsAprenentatge"]);

        if (!empty($values["nsProgramacio"])){
            $dataPrg = $this->getRawDataProjectFromOtherId($values["nsProgramacio"]);
            if(!is_array($dataPrg)){
                $dataPrg = json_decode($dataPrg, true);
            }
            $taulaDadesNF = IocCommon::toArrayThroughArrayOrJson($dataPrg["taulaDadesNuclisFormatius"]);
            $taulaDadesUFPrg = IocCommon::toArrayThroughArrayOrJson($dataPrg["taulaDadesUF"]);
            $taulaDadesNFFiltrada = array();
            $blocId = array_search($values["tipusBlocModul"], ["mòdul", "1r. bloc", "2n. bloc", "3r. bloc"]);
            foreach ($taulaDadesNF as $row) {
                $rowBlocId = $this->getBlocIdFromTaulaUF($taulaDadesUFPrg, $row["unitat formativa"]);
                if($rowBlocId==$blocId){
                    $taulaDadesNFFiltrada[] = $row;
                }
            }
            $resultatsAprenentatgePrg = IocCommon::toArrayThroughArrayOrJson($dataPrg["resultatsAprenentatge"]);
            $resultatsAprenentatgeFiltrats = array();
            foreach ($resultatsAprenentatgePrg as $row) {
                $rowBlocId = $this->getBlocIdFromTaulaUF($taulaDadesUFPrg, $row["uf"]);
                if($rowBlocId==$blocId){
                    $resultatsAprenentatgeFiltrats[] = $row;
                }
            }
        }else{
            $taulaDadesNF = FALSE;
        }
        
        if($dataPrg){
            if($values["cicle"] === $dataPrg["cicle"]){
                $values["cicle"] = "";
            }
            if($values["modul"] === $dataPrg["modul"]){
                $values["modul"] = "";
            }
        }
        
        if (!empty($taulaCalendari) && !empty($taulaDadesUnitats)){
            $hores = array();
            for ($i=0; $i<count($taulaCalendari); $i++){
                $idU = intval($taulaCalendari[$i]["unitat"]);
                if (!isset($hores[$idU])){
                    $hores[$idU]=0;
                }
                $hores[$idU]+= $taulaCalendari[$i]["hores"];
            }

            $horesUF = array();
            $horesUF[0] = 0;
            for ($i=0; $i<count($taulaDadesUnitats); $i++){
                $idU = intval($taulaDadesUnitats[$i]["unitat"]);
                if (isset($hores[$idU])){
                    $taulaDadesUnitats[$i]["hores"]=$hores[$idU];
                }
                $idUf = intval($taulaDadesUnitats[$i]["unitat formativa"]);
                if (!isset($horesUF[$idUf])){
                    $horesUF[$idUf]=0;
                }
                $horesUF[0]+= $taulaDadesUnitats[$i]["hores"];
                $horesUF[$idUf]+= $taulaDadesUnitats[$i]["hores"]; 
                if(!empty($taulaDadesNFFiltrada)){
                    if($taulaDadesUnitats[$i]["nom"]==$this->getRowFromField($taulaDadesNFFiltrada, "unitat al pla de treball",  $taulaDadesUnitats[$i]["unitat"], $i, true)["nom"]){
                        $taulaDadesUnitats[$i]["nom"] = "";
                    }
                }
            }
            
            if($resultatsAprenentatge){
                for ($i=0; $i<count($resultatsAprenentatge); $i++){
                    if(!empty($resultatsAprenentatgeFiltrats)){
                        if($resultatsAprenentatge[$i]["id"]=="UF".$resultatsAprenentatgeFiltrats[$i]["uf"].".RA".$resultatsAprenentatgeFiltrats[$i]["ra"]){
                            $resultatsAprenentatge[$i]["id"] = "";                            
                        }elseif($resultatsAprenentatge[$i]["id"]=="RA".$resultatsAprenentatgeFiltrats[$i]["ra"].".UF".$resultatsAprenentatgeFiltrats[$i]["uf"]){
                            $resultatsAprenentatge[$i]["id"] = "";
                        }
                    }
                }
            }

            if (!empty($taulaDadesUF)){
                for ($i=0; $i<count($taulaDadesUF); $i++){
                    $idUf = intval($taulaDadesUF[$i]["unitat formativa"]);
                    if (isset($horesUF[$idUf])){
                        $taulaDadesUF[$i]["hores"]=$horesUF[$idUf];
                    }
                    if($taulaDadesUF[$i]["ponderació"]==$taulaDadesUF[$i]["hores"]){
                        $taulaDadesUF[$i]["ponderació"]==0;
                    }
                }
            }

            $values["resultatsAprenentatge"]=$resultatsAprenentatge;
            $values["durada"] = $horesUF[0];
            $values["taulaDadesUnitats"] = $taulaDadesUnitats;
            $values["taulaDadesUF"] = $taulaDadesUF;
        }

        $taulaJT = IocCommon::toArrayThroughArrayOrJson($values["datesJT"]);

        if (!empty($taulaJT)){
            $hiHaRecuperacio = FALSE;
            for ($i=0; !$hiHaRecuperacio && $i<count($taulaJT); $i++){
                $hiHaRecuperacio = $taulaJT[$i]["hiHaRecuperacio"];
            }
            $values["hiHaRecuperacioPerJT"] = $hiHaRecuperacio;
        }

        $taulaEAF = IocCommon::toArrayThroughArrayOrJson($values["datesEAF"]);

        if (!empty($taulaEAF)){
            $hiHaSolucio = FALSE;
            $hiHaEnunciatRecuperacio = FALSE;
            for ($i=0; $i<count($taulaEAF); $i++){
                $hiHaSolucio |= $taulaEAF[$i]["hiHaSolucio"];
                $hiHaEnunciatRecuperacio |= $taulaEAF[$i]["hiHaEnunciatRecuperacio"];
            }

            $values["hiHaSolucioPerEAF"] = $hiHaSolucio === 0 ? FALSE : TRUE ;
            $values["hiHaEnunciatRecuperacioPerEAF"] = $hiHaEnunciatRecuperacio === 0 ? FALSE : TRUE ;
        }

        $taulaAC = IocCommon::toArrayThroughArrayOrJson($values["datesAC"]);

        if (!empty($taulaAC)){
            $hiHaSolucio = FALSE;
            for ($i=0; !$hiHaSolucio && $i<count($taulaAC); $i++){
                $hiHaSolucio = $taulaAC[$i]["hiHaSolucio"];
            }
            $values["hiHaSolucioPerAC"] = $hiHaSolucio;
        }

        $data = $isArray?$values:json_encode($values);
        return parent::updateCalculatedFieldsOnSave($data, $originalDataKeyValue);
    }
    
    private function getRowFromField($taula, $field, $value, $fromPosition=0, $defaultFromPossition=false){
        $trobat = false;
        $max = count($taula);
        if ($fromPosition < $max){
            $i = $fromPosition;
            do {
                $trobat = $taula[$i][$field] == $value;
                if ($taula[$i][$field] == $value){
                    $trobat = true;
                }else{
                    $i = ($i+1)%$max;
                }
            }while ($i != $fromPosition && !$trobat);
        }
        if ($defaultFromPossition){
            $default = $taula[$fromPosition];
        }else{
            $default = false;
        }
        return $trobat ? $taula[$i] : $default;
    }
    
    private function getBlocIdFromTaulaUF($taulaUF, $uf){
        $rowBlocId = -1;
        foreach ($taulaUF as $item) {
            if($item["unitat formativa"]==$uf){
                $rowBlocId = $item["bloc"];
                break;
            }            
        }      
        return $rowBlocId;
    }

    /**
     * Llista de les dates a pujar al calendari amb el format següent:
     *  - title
     *  - date (en format yyyy-mm-dd)
     *  - description
     */
    public function getCalendarDates() {
        $ret = array();
        $data = $this->getCurrentDataProject();
        if(is_string($data["calendari"])){
            $calendari = json_decode($data["calendari"], true);
        }else{
            $calendari = $data["calendari"];
        }
        foreach ($calendari as $item) {
            $ret[] = [
                "title"=>sprintf("%s - inici %s %d U%d", $data["modulId"], $item['tipus període'], $item["període"], $item["unitat"]),
                "date"=>$item["inici"]
            ];
        }

        $dataEnunciatOld ="";
        $dataSolucioOld ="";
        $dataQualificacioOld ="";
        if(is_string($data["datesAC"])){
            $datesAC = json_decode($data["datesAC"], true);
        }else{
            $datesAC = $data["datesAC"];
        }
        foreach ($datesAC as $item) {
            if($dataEnunciatOld!=$item["enunciat"]){
                $ret[] = [
                    "title"=>sprintf("%s - enunciat %s", $data["modulId"], $item['id']),
                    "date"=>$item["enunciat"]
                ];
                $dataEnunciatOld = $item["enunciat"];
            }
            if($item["hiHaSolucio"] && $dataSolucioOld!=$item["solució"]){
                $ret[] = [
                    "title"=>sprintf("%s - solució %s", $data["modulId"], $item['id']),
                    "date"=>$item["solució"]
                ];
                $dataSolucioOld = $item["solució"];
            }
            if($dataQualificacioOld!=$item["qualificació"]){
                $ret[] = [
                    "title"=>sprintf("%s - qualificació %s", $data["modulId"], $item['id']),
                    "date"=>$item["qualificació"]
                ];
                $dataQualificacioOld = $item["qualificació"];
            }
        }

        $dataEnunciatOld ="";
        $dataSolucioOld ="";
        $dataQualificacioOld ="";
        $dataEnunciatRecOld ="";
        $dataSolucioRecOld ="";
        $dataQualificacioRecOld ="";
        if(is_string($data["datesEAF"])){
            $datesEAF = json_decode($data["datesEAF"], true);
        }else{
            $datesEAF = $data["datesEAF"];
        }
        foreach ($datesEAF as $item) {
            if($dataEnunciatOld!=$item["enunciat"]){
                $ret[] = [
                    "title"=>sprintf("%s - enunciat %s", $data["modulId"], $item['id']),
                    "date"=>$item["enunciat"]
                ];
                $dataEnunciatOld = $item["enunciat"];
            }
            if($item["hiHaSolucio"] && $dataSolucioOld!=$item["solució"]){
                $ret[] = [
                    "title"=>sprintf("%s - solució %s", $data["modulId"], $item['id']),
                    "date"=>$item["solució"]
                ];
                $dataSolucioOld = $item["solució"];
            }
            if($dataQualificacioOld!=$item["qualificació"]){
                $ret[] = [
                    "title"=>sprintf("%s - qualificació %s", $data["modulId"], $item['id']),
                    "date"=>$item["qualificació"]
                ];
                $dataQualificacioOld = $item["qualificació"];
            }
            if($item["hiHaEnunciatRecuperacio"]){
                if($dataEnunciatRecOld!=$item["enunciat recuperació"]){
                    $ret[] = [
                        "title"=>sprintf("%s - enunciat recuperació %s", $data["modulId"], $item['id']),
                        "date"=>$item["enunciat recuparació"]
                    ];
                    $dataEnunciatRecOld = $item["enunciat recuparació"];
                }
                if($item["hiHaSolucio"] && $dataSolucioRecOld!=$item["solució recuperació"]){
                    $ret[] = [
                        "title"=>sprintf("%s - solució recuperació %s", $data["modulId"], $item['id']),
                        "date"=>$item["solució recuperació"]
                    ];
                    $dataSolucioRecOld = $item["solució recuperació"];
                }
                if($dataQualificacioRecOld!=$item["qualificació recuperació"]){
                    $ret[] = [
                        "title"=>sprintf("%s - qualificació recuperació %s", $data["modulId"], $item['id']),
                        "date"=>$item["qualificació recuperació"]
                    ];
                    $dataQualificacioRecOld = $item["qualificació recuperació"];
                }
            }
        }

        if(is_string($data["datesJT"])){
            $datesJT = json_decode($data["datesJT"], true);
        }else{
            $datesJT = $data["datesJT"];
        }
        foreach ($datesJT as $item) {
            $ret[] = [
                "title"=>sprintf("%s - inscripció %s", $data["modulId"], $item['id']),
                "date"=>$item["inscripció"]
            ];
            $ret[] = [
                "title"=>sprintf("%s - llista prov. %s", $data["modulId"], $item['id']),
                "date"=>$item["llista provisional"]
            ];
            $ret[] = [
                "title"=>sprintf("%s - llista def. %s", $data["modulId"], $item['id']),
                "date"=>$item["llista definitiva"]
            ];
            $ret[] = [
                "title"=>sprintf("%s - jornada tècnica %s", $data["modulId"], $item['id']),
                "date"=>$item["data JT"]
            ];
            $ret[] = [
                "title"=>sprintf("%s - qualificació JT %s", $data["modulId"], $item['id']),
                "date"=>$item["qualificació"]
            ];
            if($item["hiHaEnunciatRecuperacio"]){
                $ret[] = [
                    "title"=>sprintf("%s - inscripció rec. %s", $data["modulId"], $item['id']),
                    "date"=>$item["inscripció recuperació"]
                ];
                $ret[] = [
                    "title"=>sprintf("%s - llista prov. rec. %s", $data["modulId"], $item['id']),
                    "date"=>$item["llista provisional recuperació"]
                ];
                $ret[] = [
                    "title"=>sprintf("%s - llista def. rec %s", $data["modulId"], $item['id']),
                    "date"=>$item["llista definitiva recuperació"]
                ];
                $ret[] = [
                    "title"=>sprintf("%s - jornada tècnica rec. %s", $data["modulId"], $item['id']),
                    "date"=>$item["data JT recuperació"]
                ];
                $ret[] = [
                    "title"=>sprintf("%s - qualificació JT rec. %s", $data["modulId"], $item['id']),
                    "date"=>$item["qualificació recuperació"]
                ];
            }
        }
        return $ret;
    }
    
    public function validateFields($data = NULL, $subset=FALSE){
        if($subset!==FALSE && $subset!=ProjectKeys::VAL_DEFAULTSUBSET){
            return parent::validateFields($data, $subset);
        }

        parent::validateFields($data);
        //[TODO]
        //comprova si avaluació inicialde pla i progamació coincideixen
        //validar la ponderció de AC+PAF+EAF*...
        //Validar les nomes mínimes
    }

    public function getCourseId() {
        $data = $this->getCurrentDataProject();
        return $data["moodleCourseId"];
    }
}
