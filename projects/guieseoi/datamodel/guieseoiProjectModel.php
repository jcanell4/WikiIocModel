<?php
/**
 * guieseoiProjectModel
 * @culpable Rafael Claver
 * @aprenent Marjose
 */
if (!defined("DOKU_INC")) die();

class guieseoiProjectModel extends MoodleContentFilesProjectModel {

    public function __construct($persistenceEngine)  {
        parent::__construct($persistenceEngine);
        $this->needGenerateAction=false;        
    }

    /* s'eliminara perquè...
    public function getProjectDocumentName() {
        $ret = $this->getCurrentDataProject();
        return $ret['fitxercontinguts'];
    }*/

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
        $taulaCalendari = IocCommon::toArrayThroughArrayOrJson($values["calendari"]);
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
            $resultatsAprenentatgePrg = IocCommon::toArrayThroughArrayOrJson($dataPrg["resultatsAprenentatge"]);
            $taulaResultatsAprenentatgeFiltrada = array();
            if (!empty($resultatsAprenentatgePrg)) {
                foreach ($resultatsAprenentatgePrg as $row) {
                    $rowBlocId = $this->getBlocIdFromTaulaUF($taulaDadesUFPrg, $row["uf"]);
                    if($rowBlocId==$blocId){
                        $taulaResultatsAprenentatgeFiltrada[] = $row;
                    }
                }
            }
            
        }else{
            $taulaDadesNF = FALSE;
        }
        
        if($dataPrg){
            if(isset($originalValues["cicle"]) && !empty($originalValues["cicle"])){
                $values["cicle"] = $originalValues["cicle"];
            }else{
                $values["cicle"] = $dataPrg["cicle"];
            }
            if(isset($originalValues["modul"]) && !empty($originalValues["modul"])){
                $values["modul"] = $originalValues["modul"];
            }else{
                $values["modul"] = $dataPrg["modul"];
            }
        }
        
        if(!empty($taulaDadesNFFiltrada)){
            $originalRow = $this->getRowFromField($originalValues, "unitat",  $taulaDadesNFFiltrada[$i]["unitat al pla de treball"], $i, true);
            $taulaDadesUnitats = array();
            for($i=0; $i<count($taulaDadesNFFiltrada); $i++){
                $taulaDadesUnitats[$i]["unitat formativa"] = $taulaDadesNFFiltrada[$i]["unitat formativa"];
                $taulaDadesUnitats[$i]["unitat"] = $taulaDadesNFFiltrada[$i]["unitat al pla de treball"];
                if(empty($originalRow) || empty($originalRow["nom"])){
                    $taulaDadesUnitats[$i]["nom"] = $taulaDadesNFFiltrada[$i]["nom"];
                }else{
                    $taulaDadesUnitats[$i]["nom"] = $originalRow["nom"];
                }
                $taulaDadesUnitats[$i]["hores"] = $taulaDadesNFFiltrada[$i]["hores"];
            }
        }
        $values["taulaDadesUnitats"] = $taulaDadesUnitats;
        
        $values["calendari"] = $this->getCalendariFieldFromMix($values, $taulaCalendari);
        
        if(!empty($taulaResultatsAprenentatgeFiltrada)){
            for ($i=0; $i<count($taulaResultatsAprenentatgeFiltrada); $i++){
                if(empty($originalResultatsAprenentatge[$i]["id"])){
                    $resultatsAprenentatge[$i]["id"] = "UF".$taulaResultatsAprenentatgeFiltrada[$i]["uf"].".RA".$taulaResultatsAprenentatgeFiltrada[$i]["ra"];
                }else{
                    $resultatsAprenentatge[$i]["id"] = $originalResultatsAprenentatge[$i]["id"];
                }
                $resultatsAprenentatge[$i]["descripcio"]= $taulaResultatsAprenentatgeFiltrada[$i]["descripcio"];
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
//            $ufTable[$key]["ordreImparticio"] = $originalufTable[$key]["ordreImparticio"];
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
     * 
     * 
     * marjose: 
     * $originalDataKeyValue és el que ve del client. Quan usuari modifica, primer passa pel calculateonread o calculateonsave
     * $originalDataKeyValue és el que ve directament del client sense haver passat pel client. 
     * el $data és el que em ve del calculatedataread o calculatedataonsave
     * per mi això pot estar buit perquè no he de fer cap càlcul
     * 
     * potser fins i tot carregarme tota la funcio
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
        
        $taulaCalendari = $this->getCalendariFieldFromMix($values, $taulaCalendari);
        $values["calendari"] = $taulaCalendari;
        
        if (!empty($taulaCalendari) && !empty($taulaDadesUnitats)){
            $hores = array();
            if (empty($values["nsProgramacio"])){
                for ($i=0; $i<count($taulaCalendari); $i++){
                    $idU = intval($taulaCalendari[$i]["unitat"]);
                    if (!isset($hores[$idU])){
                        $hores[$idU]=0;
                    }
                    $hores[$idU]+= $taulaCalendari[$i]["hores"];
                }
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
                        $taulaDadesUF[$i]["hores"] = $horesUF[$idUf];
                    }
                    if ($taulaDadesUF[$i]["ponderació"]==$taulaDadesUF[$i]["hores"]){
                        $taulaDadesUF[$i]["ponderació"] = 0;
                    }
                }
            }

            $values["resultatsAprenentatge"] = $resultatsAprenentatge;
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
    
    private function getCalendariFieldFromMix(&$values, $taulaCalendari){
        $dataFromMix = false;
        if(isset($values["moodleCourseId"]) && $values["moodleCourseId"]>0){            
            $dataFromMix = $this->getMixDataLessons($values["moodleCourseId"]);
            if($dataFromMix){
                $mixLen = count($dataFromMix);
                if($mixLen>0){
                    $modulId = trim($values["modulId"]);
                    if(preg_match("/$modulId/i", $dataFromMix[0]->shortname)){ 
//                        error_log("D0.3.- A punt d'actualitzar.");
                        $aux = $taulaCalendari;
                        $calLen = count($aux);
                        $taulaCalendari = array();
                        for($i=0; $i<$mixLen; $i++){                   
                            $taulaCalendari []= array(
                                "unitat" => $dataFromMix[$i]->unitid,
                                 "període" => $dataFromMix[$i]->lessonid,
                                 "tipus període" => "lliçó",
                                 "descripció període" => $dataFromMix[$i]->lessontitle,
                                 "hores" => $dataFromMix[$i]->lessonhours,
                                 "inici" => ($i<$calLen)?$aux[$i]["inici"]:"",
                                 "final" => ($i<$calLen)?$aux[$i]["final"]:"",
                            );
                        }
                        $dataFromMix = true;
                    }
                }
            }
        }
        $values["dataFromMix"] =$dataFromMix;
        return $taulaCalendari;        
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
            parent::validateFields($data, $subset);
        }else{
            parent::validateFields($data);
            $values = is_array($data)?$data:json_decode($data, true);
            $taulaDadesUnitats = IocCommon::toArrayThroughArrayOrJson($values["taulaDadesUnitats"]);
            $taulaDadesUF = IocCommon::toArrayThroughArrayOrJson($values["taulaDadesUF"]);
            $taulaCalendari = IocCommon::toArrayThroughArrayOrJson($values["calendari"]);

            // Comprovació de la correspondència entre "unitat formativa" i "bloc"
            $verifica = false;
            $bloc = array_search($values['tipusBlocModul'], ["mòdul","1r. bloc","2n. bloc","3r. bloc"]);
            if (empty($bloc)) $bloc = 0;
            foreach ($taulaDadesUF as $uf) {
                $verifica |= ($uf['bloc'] == $bloc);
            }
            if (!$verifica && !empty($taulaDadesUF)) {
                throw new InconsistentDataException("No hi ha cap unitat formativa que pertanyi al bloc ({$taulaDadesUF['bloc']})[$bloc] definit en aquest pla de treball");
            }
            
            if (!empty($values["nsProgramacio"])){
                $hores = array();
                for ($i=0; $i<count($taulaCalendari); $i++){
                    $idU = intval($taulaCalendari[$i]["unitat"]);
                    if (!isset($hores[$idU])){
                        $hores[$idU]=0;
                    }
                    $hores[$idU]+= $taulaCalendari[$i]["hores"];
                }
                $horesU = array();
                for ($i=0; $i<count($taulaDadesUnitats); $i++){
                    $idU = intval($taulaDadesUnitats[$i]["unitat"]);
                    $idUf = intval($taulaDadesUnitats[$i]["unitat formativa"]);
                    if (!isset($horesU[$idU])){
                        $horesU[$idU]=0;
                    }
                    $horesU[$idU]+= $taulaDadesUnitats[$i]["hores"]; 
                }
                foreach ($hores as $i => $h){
                    if($hores[$i] !=$horesU[$i]){
                        throw new InconsistentDataException("Les hores de la unitat $i ({$horesU[$i]} h) no es corrresponen amb les que apareixen al calendari ({$hores[$i]} h)");
                    }
                }
            }
        }
    }

    public function getCourseId() {
        $data = $this->getCurrentDataProject();
        return $data["moodleCourseId"];
    }
}
