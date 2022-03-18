<?php
/**
 * ptfplogseProjectModel
 * @culpable Rafael Claver
 */
if (!defined("DOKU_INC")) die();

class ptfplogseProjectModel extends MoodleUniqueContentFilesProjectModel {

    public function __construct($persistenceEngine)  {
        parent::__construct($persistenceEngine);
        $this->needGenerateAction=false;
    }

    public function getProjectDocumentName() {
        $ret = $this->getCurrentDataProject();
        return $ret['fitxercontinguts'];
    }

    /**
     * Calcula el valor de los campos calculables
     * @param JSON $data
     */
    public function updateCalculatedFieldsOnRead($data, $originalDataKeyValue=FALSE, $subset=FALSE) {
        if($subset!==FALSE && $subset!=ProjectKeys::VAL_DEFAULTSUBSET){
            return parent::updateCalculatedFieldsOnRead($data, $subset);
        }
        
        $data = parent::updateCalculatedFieldsOnRead($data, $subset);
        $isArray = is_array($data);
        $values = $isArray ? $data : json_decode($data, true);
        $originalValues = $isArray ? $originalDataKeyValue : json_decode($originalDataKeyValue, true);

        $taulaDadesUnitats = IocCommon::toArrayThroughArrayOrJson($values["taulaDadesUD"]);
        $originalTaulaDadesUnitats = IocCommon::toArrayThroughArrayOrJson($originalValues["taulaDadesUD"]);
        $dadesQualificacio = IocCommon::toArrayThroughArrayOrJson($values["dadesQualificacio"]);
        $originalDadesQualificacio = IocCommon::toArrayThroughArrayOrJson($originalValues["dadesQualificacio"]);
        $calendari = IocCommon::toArrayThroughArrayOrJson($values["calendari"]);
        $originalCalendari= IocCommon::toArrayThroughArrayOrJson($originalValues["calendari"]);
        $blocId = array_search($values["tipusBlocCredit"], ["crèdit", "1r. bloc", "2n. bloc", "3r. bloc"]);
        if($values["nsProgramacio"]){
            $dataPrg = $this->getRawDataProjectFromOtherId($values["nsProgramacio"]);
            if(!is_array($dataPrg)){
                $dataPrg = json_decode($dataPrg, true);
            }            
            //valors a sobrescriure  des de la programació, si n'hi ha
            $avaluacioInicial_prg = $dataPrg["avaluacioInicial"];
        }else{
            //valors per defecte si n'hi ha.
        }    
        
        if($dataPrg){
            if(isset($originalValues["cicle"]) && !empty($originalValues["cicle"])){
                $values["cicle"] = $originalValues["cicle"];
            }
            if(isset($originalValues["credit"]) && !empty($originalValues["modul"])){
                $values["credit"] = $originalValues["credit"];
            }
        }
        


        if(!empty($dadesQualificacio)){
            for ($i=0; $i<count($dadesQualificacio); $i++){
               if($dadesQualificacio[$i]["abreviació qualificació"]==$originalDadesQualificacio[$i]["abreviació qualificació"]
                       || $dadesQualificacio[$i]["tipus qualificació"]==$originalDadesQualificacio[$i]["tipus qualificació"]
                       && $dadesQualificacio[$i]["tipus qualificació"]=="PAF"){
                   $pos = $i;
               }else{
                   $pos = -1;
                   $j=0;
                   foreach ($originalDadesQualificacio as $item) {
                       if($item["abreviació qualificació"]==$dadesQualificacio[$i]["abreviació qualificació"]
                                || $dadesQualificacio[$i]["tipus qualificació"]==$item["tipus qualificació"]
                                && $item["tipus qualificació"]=="PAF"){
                           $pos = $j;
                       }
                       $j++;
                   }
               }
               if($pos!=-1 && !empty($originalDadesQualificacio[$pos]["descripció qualificació"])){
                   $dadesQualificacio[$i]["descripció qualificació"] = $originalDadesQualificacio[$pos]["descripció qualificació"];
               }
            }
            $values["dadesQualificacio"] = $dadesQualificacio;
        }
        
        if(!empty($calendari)){
            for ($i=0; $i<count($calendari); $i++){
                $pos = -1;
                $j=0;
                foreach ($originalCalendari as $item) {
                    if($item["unitat didàctica"]==$calendari[$i]["unitat didàctica"]
                             && $calendari[$i]["nucli activitat"]==$item["nucli activitat"]){
                        $pos = $j;
                    }
                    $j++;
                }
               if($pos!=-1){
                   $calendari[$i]["inici"] = $originalCalendari[$pos]["inici"];
                   $calendari[$i]["final"] = $originalCalendari[$pos]["final"];
               }
            }
            $values["calendari"] = $calendari;
        }
        
        if(!empty($taulaDadesUnitats)){
            foreach ($taulaDadesUnitats as $key => $value) {
                $taulaDadesUnitats[$key]["ordreImparticio"] = $originalTaulaDadesUnitats[$key]["ordreImparticio"];
            }
            $values["taulaDadesUD"] = $taulaDadesUnitats;
        }
        
        switch ($avaluacioInicial_prg){
            case "NO":
                $avaluacioInicial = $avaluacioInicial_prg;
                break;
            case "B":
                $avaluacioInicial = $blocId<2?"SI":"NO";
                break;
            case "C":
                $avaluacioInicial = "SI";                
        }
        $values["avaluacioInicial"] = $avaluacioInicial;

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

        $taulaDadesUnitats = IocCommon::toArrayThroughArrayOrJson($values["taulaDadesUD"]);
        $taulaCalendari = IocCommon::toArrayThroughArrayOrJson($values["calendari"]);
        $taulaJT = IocCommon::toArrayThroughArrayOrJson($values["datesJT"]);

        if($values["nsProgramacio"]){
            $dataPrg = $this->getRawDataProjectFromOtherId($values["nsProgramacio"]);
            if(!is_array($dataPrg)){
                $dataPrg = json_decode($dataPrg, true);
            }            

            if($values["cicle"] === $dataPrg["cicle"]){
                $values["cicle"] = "";
            }
            if($values["credit"] === $dataPrg["credit"]){
                $values["credit"] = "";
            }
        }


        if ($taulaJT != NULL) {
            $hiHaRecuperacio = FALSE;
            for ($i=0; !$hiHaRecuperacio && $i<count($taulaJT); $i++) {
                $hiHaRecuperacio = $taulaJT[$i]["hiHaRecuperacio"];
            }
            $values["hiHaRecuperacioPerJT"] = $hiHaRecuperacio;
        }

        $taulaEAF = IocCommon::toArrayThroughArrayOrJson($values["datesEAF"]);

        if ($taulaEAF != NULL) {
            $hiHaSolucio = FALSE;
            $hiHaEnunciatRecuperacio = FALSE;
            for ($i=0; $i<count($taulaEAF); $i++) {
                $hiHaSolucio |= $taulaEAF[$i]["hiHaSolucio"];
                $hiHaEnunciatRecuperacio |= $taulaEAF[$i]["hiHaEnunciatRecuperacio"];
            }
            $values["hiHaSolucioPerEAF"] = $hiHaSolucio === 0 ? FALSE : TRUE ;
            $values["hiHaEnunciatRecuperacioPerEAF"] = $hiHaEnunciatRecuperacio === 0 ? FALSE : TRUE ;
        }

        $taulaAC = IocCommon::toArrayThroughArrayOrJson($values["datesAC"]);

        if ($taulaAC != NULL) {
            $hiHaSolucio = FALSE;
            for ($i=0; !$hiHaSolucio && $i<count($taulaAC); $i++) {
                $hiHaSolucio = $taulaAC[$i]["hiHaSolucio"];
            }
            $values["hiHaSolucioPerAC"] = $hiHaSolucio;
        }

        if ($taulaCalendari != NULL && $taulaDadesUnitats != NULL) {
            $hores = array();
            $hores[0] = 0;
            for ($i=0; $i<count($taulaCalendari);$i++) {
                $idU = intval($taulaCalendari[$i]["unitat didàctica"]);
                if (!isset($hores[$idU])) {
                    $hores[$idU]=0;
                }
                $hores[$idU]+= $taulaCalendari[$i]["hores"];
                $hores[0] += $taulaCalendari[$i]["hores"];
            }

            for ($i=0; $i<count($taulaDadesUnitats);$i++) {
                $idU = intval($taulaDadesUnitats[$i]["unitat didàctica"]);
                if (isset($hores[$idU])) {
                    $taulaDadesUnitats[$i]["hores"]=$hores[$idU];
                }
            }
            $values["durada"] = $hores[0];
            $values["taulaDadesUD"] = $taulaDadesUnitats;
        }

        $data = $isArray?$values:json_encode($values);
        return parent::updateCalculatedFieldsOnSave($data, $originalDataKeyValue);
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
                "title"=>sprintf("%s - inici NA%d-U%d", $data["creditId"], $item["nucli activitat"], $item["unitat didàctica"]),
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
                    "title"=>sprintf("%s - enunciat %s", $data["creditId"], $item['id']),
                    "date"=>$item["enunciat"]
                ];
                $dataEnunciatOld = $item["enunciat"];
            }
            if($item["hiHaSolucio"] && $dataSolucioOld!=$item["solució"]){
                $ret[] = [
                    "title"=>sprintf("%s - solució %s", $data["creditId"], $item['id']),
                    "date"=>$item["solució"]
                ];
                $dataSolucioOld = $item["solució"];
            }
            if($dataQualificacioOld!=$item["qualificació"]){
                $ret[] = [
                    "title"=>sprintf("%s - qualificació %s", $data["creditId"], $item['id']),
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
                    "title"=>sprintf("%s - enunciat %s", $data["creditId"], $item['id']),
                    "date"=>$item["enunciat"]
                ];
                $dataEnunciatOld = $item["enunciat"];
            }
            if($item["hiHaSolucio"] && $dataSolucioOld!=$item["solució"]){
                $ret[] = [
                    "title"=>sprintf("%s - solució %s", $data["creditId"], $item['id']),
                    "date"=>$item["solució"]
                ];
                $dataSolucioOld = $item["solució"];
            }
            if($dataQualificacioOld!=$item["qualificació"]){
                $ret[] = [
                    "title"=>sprintf("%s - qualificació %s", $data["creditId"], $item['id']),
                    "date"=>$item["qualificació"]
                ];
                $dataQualificacioOld = $item["qualificació"];
            }
            if($item["hiHaEnunciatRecuperacio"]){
                if($dataEnunciatRecOld!=$item["enunciat recuperació"]){
                    $ret[] = [
                        "title"=>sprintf("%s - enunciat recuperació %s", $data["creditId"], $item['id']),
                        "date"=>$item["enunciat recuparació"]
                    ];
                    $dataEnunciatRecOld = $item["enunciat recuparació"];
                }
                if($item["hiHaSolucio"] && $dataSolucioRecOld!=$item["solució recuperació"]){
                    $ret[] = [
                        "title"=>sprintf("%s - solució recuperació %s", $data["creditId"], $item['id']),
                        "date"=>$item["solució recuperació"]
                    ];
                    $dataSolucioRecOld = $item["solució recuperació"];
                }
                if($dataQualificacioRecOld!=$item["qualificació recuperació"]){
                    $ret[] = [
                        "title"=>sprintf("%s - qualificació recuperació %s", $data["creditId"], $item['id']),
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
                "title"=>sprintf("%s - inscripció %s", $data["creditId"], $item['id']),
                "date"=>$item["inscripció"]
            ];
            $ret[] = [
                "title"=>sprintf("%s - llista prov. %s", $data["creditId"], $item['id']),
                "date"=>$item["llista provisional"]
            ];
            $ret[] = [
                "title"=>sprintf("%s - llista def. %s", $data["creditId"], $item['id']),
                "date"=>$item["llista definitiva"]
            ];
            $ret[] = [
                "title"=>sprintf("%s - jornada tècnica %s", $data["creditId"], $item['id']),
                "date"=>$item["data JT"]
            ];
            $ret[] = [
                "title"=>sprintf("%s - qualificació JT %s", $data["creditId"], $item['id']),
                "date"=>$item["qualificació"]
            ];
            if($item["hiHaEnunciatRecuperacio"]){
                $ret[] = [
                    "title"=>sprintf("%s - inscripció rec. %s", $data["creditId"], $item['id']),
                    "date"=>$item["inscripció recuperació"]
                ];
                $ret[] = [
                    "title"=>sprintf("%s - llista prov. rec. %s", $data["creditId"], $item['id']),
                    "date"=>$item["llista provisional recuperació"]
                ];
                $ret[] = [
                    "title"=>sprintf("%s - llista def. rec %s", $data["creditId"], $item['id']),
                    "date"=>$item["llista definitiva recuperació"]
                ];
                $ret[] = [
                    "title"=>sprintf("%s - jornada tècnica rec. %s", $data["creditId"], $item['id']),
                    "date"=>$item["data JT recuperació"]
                ];
                $ret[] = [
                    "title"=>sprintf("%s - qualificació JT rec. %s", $data["creditId"], $item['id']),
                    "date"=>$item["qualificació recuperació"]
                ];
            }
        }
        return $ret;
    }

    public function getCourseId() {
        $data = $this->getCurrentDataProject();
        return $data["moodleCourseId"];
    }
}
