<?php
/**
 * ptfpprj24ProjectModel
 * @culpable Rafael Claver
 */
if (!defined("DOKU_INC")) die();

class ptfpprj24ProjectModel extends MoodleUniqueContentFilesProjectModel {

    public function __construct($persistenceEngine)  {
        parent::__construct($persistenceEngine);
        $this->needGenerateAction=false;        
    }

    public function getProjectDocumentName() {
        $ret = $this->getCurrentDataProject();
        return $ret['fitxercontinguts'];
    }

    //TODO: Resta pendent el crear la nova programació per pt loe 24 i adaptar llavors aquí
    // updateCalculateFieldsOnRead() en funció de la nova defició de la programació
    public function updateCalculatedFieldsOnRead($data, $originalDataKeyValue=FALSE, $subset=FALSE) {
        if($subset!==FALSE && $subset!=ProjectKeys::VAL_DEFAULTSUBSET){
            return parent::updateCalculatedFieldsOnRead($data, $subset);
        }
        
        $data = parent::updateCalculatedFieldsOnRead($data, $subset);
        $isArray = is_array($data);
        $values = $isArray ? $data : json_decode($data, true);
        $originalValues = $isArray ? $originalDataKeyValue : json_decode($originalDataKeyValue, true);

        $unTable = IocCommon::toArrayThroughArrayOrJson($values["taulaDadesUn"]);
        $originalufTable = IocCommon::toArrayThroughArrayOrJson($originalValues["taulaDadesUn"]);
        $taulaDadesUn = IocCommon::toArrayThroughArrayOrJson($values["taulaDadesUn"]);//marjose: anteriorment taulaDadesUnitats
        $originalTaulaDadesUn = IocCommon::toArrayThroughArrayOrJson($originalValues["taulaDadesUn"]);//marjose: anteriorment taulaDadesUnitats
        $resultatsAprenentatge = IocCommon::toArrayThroughArrayOrJson($values["resultatsAprenentatge"]);
        $originalResultatsAprenentatge = IocCommon::toArrayThroughArrayOrJson($originalValues["resultatsAprenentatge"]);
        $dadesQualificacioUns = IocCommon::toArrayThroughArrayOrJson($values["dadesQualificacioUns"]);
        $originalDadesQualificacioUns = IocCommon::toArrayThroughArrayOrJson($originalValues["dadesQualificacioUns"]);
        $taulaCalendari = IocCommon::toArrayThroughArrayOrJson($values["calendari"]);
        $blocId = array_search($values["tipusBlocModul"], ["mòdul", "1r. bloc", "2n. bloc", "3r. bloc"]);
        if($values["nsProgramacio"]){
            $dataPrg = $this->getRawDataProjectFromOtherId($values["nsProgramacio"]);
            if(!is_array($dataPrg)){
                $dataPrg = json_decode($dataPrg, true);
            }
            $taulaDadesNF = IocCommon::toArrayThroughArrayOrJson($dataPrg["taulaDadesNuclisFormatius"]);
            $taulaDadesUnPrg = IocCommon::toArrayThroughArrayOrJson($dataPrg["taulaDadesUn"]);
            $taulaDadesNFFiltrada = array();
            if (!empty($taulaDadesNF)) {
                foreach ($taulaDadesNF as $row) {
                    $rowBlocId = $this->getBlocIdFromTaulaUn($taulaDadesUnPrg, $row["unitat formativa"]);
                    if($rowBlocId==$blocId){
                        $taulaDadesNFFiltrada[] = $row;
                    }
                }
            }
            $resultatsAprenentatgePrg = IocCommon::toArrayThroughArrayOrJson($dataPrg["resultatsAprenentatge"]);
            $taulaResultatsAprenentatgeFiltrada = array();
            if (!empty($resultatsAprenentatgePrg)) {
                foreach ($resultatsAprenentatgePrg as $row) {
                    $rowBlocId = $this->getBlocIdFromTaulaUn($taulaDadesUnPrg, $row["uf"]);
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
        
        //Marjose: AQUI: MIRAR PERQUÈ FA SERVIR TAULADADES UNITATS. DE MOMENT RES PERQUÈ NO HI HA PROGRAMACIO
        //PER TANT, tauladadesNFFiltrada estara buida.
        // s'ha canviat aquí taulaDadesUnitats per taulaDadesUn
        if(!empty($taulaDadesNFFiltrada)){
            $originalRow = $this->getRowFromField($originalValues, "unitat",  $taulaDadesNFFiltrada[$i]["unitat al pla de treball"], $i, true);
            $taulaDadesUn = array();
            for($i=0; $i<count($taulaDadesNFFiltrada); $i++){
                $taulaDadesUn[$i]["unitat formativa"] = $taulaDadesNFFiltrada[$i]["unitat formativa"];//Marjose: caldrà eliminar unitat formativa i passar-lo a unitat
                $taulaDadesUn[$i]["unitat"] = $taulaDadesNFFiltrada[$i]["unitat al pla de treball"];
                if(empty($originalRow) || empty($originalRow["nom"])){
                    $taulaDadesUn[$i]["nom"] = $taulaDadesNFFiltrada[$i]["nom"];
                }else{
                    $taulaDadesUn[$i]["nom"] = $originalRow["nom"];
                }
                $taulaDadesUn[$i]["hores"] = $taulaDadesNFFiltrada[$i]["hores"];
            }
        }
        $values["taulaDadesUn"] = $taulaDadesUn;
        
        $values["calendari"] = $this->getCalendariFieldFromMix($values, $taulaCalendari);
        
        //Marjose: AQUI: REVISAR SI CAL AJUSTAR TAULARESULTATSAPRENENTATGE AFEGINT CAMP PONDERACIO
        //$taulaResultatsAprenentatgeFiltrada només tindrà contingut si ve de la programació. Per ara, res.
        if(!empty($taulaResultatsAprenentatgeFiltrada)){
            for ($i=0; $i<count($taulaResultatsAprenentatgeFiltrada); $i++){
                if(empty($originalResultatsAprenentatge[$i]["id"])){
                    $resultatsAprenentatge[$i]["id"] = "Un".$taulaResultatsAprenentatgeFiltrada[$i]["uf"].".RA".$taulaResultatsAprenentatgeFiltrada[$i]["ra"];
                }else{
                    $resultatsAprenentatge[$i]["id"] = $originalResultatsAprenentatge[$i]["id"];
                }
                $resultatsAprenentatge[$i]["descripcio"]= $taulaResultatsAprenentatgeFiltrada[$i]["descripcio"];
            }            
        }
        $values["resultatsAprenentatge"] = $resultatsAprenentatge;
        
        for ($i=0; $i<count($dadesQualificacioUns); $i++){
           if($dadesQualificacioUns[$i]["abreviació qualificació"]==$originalDadesQualificacioUns[$i]["abreviació qualificació"]
                   || $dadesQualificacioUns[$i]["tipus qualificació"]==$originalDadesQualificacioUns[$i]["tipus qualificació"]
                   && $dadesQualificacioUns[$i]["tipus qualificació"]=="PAF"){
               $pos = $i;
           }else{
               $pos = -1;
               $j=0;
               foreach ($originalDadesQualificacioUns as $item) {
                   if($item["abreviació qualificació"]==$dadesQualificacioUns[$i]["abreviació qualificació"]
                            || $dadesQualificacioUns[$i]["tipus qualificació"]==$item["tipus qualificació"]
                            && $item["tipus qualificació"]=="PAF"){
                       $pos = $j;
                   }
                   $j++;
               }
           }
           if($pos!=-1 && !empty($originalDadesQualificacioUns[$pos]["descripció qualificació"])){
               $dadesQualificacioUns[$i]["descripció qualificació"] = $originalDadesQualificacioUns[$pos]["descripció qualificació"];
           }
        }
        $values["dadesQualificacioUns"] = $dadesQualificacioUns;
        
        //Marjose: $unTable contains $taulaDadesUn
        foreach ($unTable as $key => $value) {
            if($unTable[$key]["ponderació"]=="0"){
                $unTable[$key]["ponderació"]=$unTable[$key]["hores"];
            }
//            $unTable[$key]["ordreImparticio"] = $originalufTable[$key]["ordreImparticio"];
        }
        
        $nAvaluacioInicial=0;
        if($taulaDadesUnPrg){
            foreach ($taulaDadesUnPrg as $item) {
                if($blocId == $item["bloc"] && $item["avaluacioInicial"]!=="No en té"){
                    $nAvaluacioInicial++;
                }
            }            
        }
        
        $values["taulaDadesUn"]=$unTable;
        if($nAvaluacioInicial==0){
            $avaluacioInicial = "NO";
        }elseif($nAvaluacioInicial==1){
            $avaluacioInicial = "INICI";
        }else{
            $avaluacioInicial = "PER_Un";
        }
        $values["avaluacioInicial"]= $avaluacioInicial;

                
        //MARJOSE
        //afegir calcul de la nota minima EAF
        //Si te blocs
        //busco nota minima bloc 1
        //busco nota minima bloc 2
        //si fos array a la pos 0 la nota minima de modul, 1 bloc1, 2 bloc2
        //$taulaDadesNF = IocCommon::toArrayThroughArrayOrJson($dataPrg["taulaDadesNuclisFormatius"]);
        //END MARJOSE
        
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

        $taulaDadesUn = IocCommon::toArrayThroughArrayOrJson($values["taulaDadesUn"]);
        //$taulaDadesUnitats = IocCommon::toArrayThroughArrayOrJson($values["taulaDadesUnitats"]);
        $taulaCalendari = IocCommon::toArrayThroughArrayOrJson($values["calendari"]);
        $resultatsAprenentatge = IocCommon::toArrayThroughArrayOrJson($values["resultatsAprenentatge"]);

        if (!empty($values["nsProgramacio"])){
            $dataPrg = $this->getRawDataProjectFromOtherId($values["nsProgramacio"]);
            if(!is_array($dataPrg)){
                $dataPrg = json_decode($dataPrg, true);
            }
            $taulaDadesNF = IocCommon::toArrayThroughArrayOrJson($dataPrg["taulaDadesNuclisFormatius"]);
            $taulaDadesUnPrg = IocCommon::toArrayThroughArrayOrJson($dataPrg["taulaDadesUn"]);
            $taulaDadesNFFiltrada = array();
            $blocId = array_search($values["tipusBlocModul"], ["mòdul", "1r. bloc", "2n. bloc", "3r. bloc"]);
            foreach ($taulaDadesNF as $row) {
                $rowBlocId = $this->getBlocIdFromTaulaUn($taulaDadesUnPrg, $row["unitat formativa"]);
                if($rowBlocId==$blocId){
                    $taulaDadesNFFiltrada[] = $row;
                }
            }
            $resultatsAprenentatgePrg = IocCommon::toArrayThroughArrayOrJson($dataPrg["resultatsAprenentatge"]);
            $resultatsAprenentatgeFiltrats = array();
            foreach ($resultatsAprenentatgePrg as $row) {
                $rowBlocId = $this->getBlocIdFromTaulaUn($taulaDadesUnPrg, $row["uf"]); //Marjose: per actualitzar quan actualtizem programació a LOE24
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
        
        if (!empty($taulaCalendari) && !empty($taulaDadesUn)){           
            $horesUn = array();
            $horesUn[0] = 0;

            for ($i=0; $i<count($taulaDadesUn); $i++){
                $horesUn[0]+= $taulaDadesUn[$i]["hores"];//Sobre $horesUn[0] guarda la suma d'hores
                $horesUn[$idUn]+= $taulaDadesUn[$i]["hores"]; 
                if(!empty($taulaDadesNFFiltrada)){
                    if($taulaDadesUn[$i]["nom"]==$this->getRowFromField($taulaDadesNFFiltrada, "unitat al pla de treball",  $taulaDadesUn[$i]["unitat"], $i, true)["nom"]){
                        $taulaDadesUn[$i]["nom"] = "";
                    }
                }
            }
            
            if($resultatsAprenentatge){ 
                for ($i=0; $i<count($resultatsAprenentatge); $i++){
                    if(!empty($resultatsAprenentatgeFiltrats)){
                        if($resultatsAprenentatge[$i]["id"]=="Un".$resultatsAprenentatgeFiltrats[$i]["uf"].".RA".$resultatsAprenentatgeFiltrats[$i]["ra"]){
                            $resultatsAprenentatge[$i]["id"] = "";                            
                        }elseif($resultatsAprenentatge[$i]["id"]=="RA".$resultatsAprenentatgeFiltrats[$i]["ra"].".Un".$resultatsAprenentatgeFiltrats[$i]["uf"]){
                            $resultatsAprenentatge[$i]["id"] = "";
                        }
                    }
                }
            }
            //marjose: Si hi ha registrat sobre $idUn un número d'hores, aquest és 
            //trepitja sobre $taulaDadesUn
            if (!empty($taulaDadesUn)){
                for ($i=0; $i<count($taulaDadesUn); $i++){
                    $idUn = intval($taulaDadesUn[$i]["unitat"]);
                    if ($taulaDadesUn[$i]["ponderació"]==$taulaDadesUn[$i]["hores"]){
                        $taulaDadesUn[$i]["ponderació"] = 0;
                    }
                }
            }

            $values["resultatsAprenentatge"] = $resultatsAprenentatge;
            $values["durada"] = $horesUn[0];
            $values["taulaDadesUn"] = $taulaDadesUn;
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
    
    private function getBlocIdFromTaulaUn($taulaUn, $uf){
        $rowBlocId = -1;
        foreach ($taulaUn as $item) {
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
    
    //Marjose: 
    //Comprova que les hores de les unitats a taulaDadesUn siguin coherents amb
    //les detallades al calendari - ELIMINAT
    public function validateFields($data = NULL, $subset=FALSE){
        if($subset!==FALSE && $subset!=ProjectKeys::VAL_DEFAULTSUBSET){
            parent::validateFields($data, $subset);
        }else{
            parent::validateFields($data);            
        }
    }

    public function getCourseId() {
        $data = $this->getCurrentDataProject();
        return $data["moodleCourseId"];
    }
}
