<?php
/**
 * ptfploeProjectModel
 * @culpable Rafael Claver
 */
if (!defined("DOKU_INC")) die();

class ptfploeProjectModel extends MoodleProjectModel {

    public function __construct($persistenceEngine)  {
        parent::__construct($persistenceEngine);
    }

    public function getProjectDocumentName() {
        $ret = $this->getCurrentDataProject();
        return $ret['fitxercontinguts'];
    }

    protected function getContentDocumentIdFromResponse($responseData){
        if ($responseData['projectMetaData']["fitxercontinguts"]['value']){
            $contentName = $responseData['projectMetaData']["fitxercontinguts"]['value'];
        }else{
            $contentName = end(explode(":", $this->getTemplateContentDocumentId($responseData)));
        }
        return $this->id.":" .$contentName;
    }

    public function generateProject() {
        $ret = array();
        //0. Obtiene los datos del proyecto
        $ret = $this->getData();   //obtiene la estructura y el contenido del proyecto

        //2. Establece la marca de 'proyecto generado'
        $ret[ProjectKeys::KEY_GENERATED] = $this->getProjectMetaDataQuery()->setProjectGenerated();

        if ($ret[ProjectKeys::KEY_GENERATED]) {
            try {
                //3. Otorga, a las Persons, permisos sobre el directorio de proyecto y añade enlace a dreceres
                $params = $this->buildParamsToPersons($ret['projectMetaData'], NULL);
                $this->modifyACLPageAndShortcutToPerson($params);
            }
            catch (Exception $e) {
                $ret[ProjectKeys::KEY_GENERATED] = FALSE;
                $this->getProjectMetaDataQuery()->setProjectSystemStateAttr("generated", FALSE);
            }
        }

        return $ret;
    }

    public function createTemplateDocument($data = NULL){
        StaticUniqueContentFileProjectModel::createTemplateDocument($this, $data);
//        $pdir = $this->getProjectMetaDataQuery()->getProjectTypeDir()."metadata/plantilles/";
//        // TODO: $file ha de ser el nom del fitxer de la plantilla, amb extensió?
//        $file = $this->getTemplateContentDocumentId($data) . ".txt";
//
//        $plantilla = file_get_contents($pdir.$file);
//        $name = substr($file, 0, -4);
//        $destino = $this->getContentDocumentId($name);
//        $this->dokuPageModel->setData([PageKeys::KEY_ID => $destino,
//                                       PageKeys::KEY_WIKITEXT => $plantilla,
//                                       PageKeys::KEY_SUM => "generate project"]);
    }

    public function updateCalculatedFieldsOnRead($data, $originalDataKeyValue=FALSE) {
        $data = parent::updateCalculatedFieldsOnRead($data);
        $isArray = is_array($data);
        $values = $isArray?$data:json_decode($data, true);
        $originalValues = $isArray?$originalDataKeyValue:json_decode($originalDataKeyValue, true);

        $taulaDadesUnitats = (is_array($values["taulaDadesUnitats"])) ? $values["taulaDadesUnitats"] : json_decode($values["taulaDadesUnitats"], true);
        $originalTaulaDadesUnitats = (is_array($originalValues["taulaDadesUnitats"])) ? $originalValues["taulaDadesUnitats"] : json_decode($originalValues["taulaDadesUnitats"], true);
        if($values["nsProgramacio"]){
            $dataPrg = $this->getRawDataProjectFromOtherId($values["nsProgramacio"]);
            if(!is_array($dataPrg)){
                $dataPrg = json_decode($dataPrg, true);
            }
            $taulaDadesNF = (is_array($dataPrg["taulaDadesNuclisFormatius"])) ? $dataPrg["taulaDadesNuclisFormatius"] : json_decode($dataPrg["taulaDadesNuclisFormatius"], true);

            $taulaDadesUFPrg = (is_array($dataPrg["taulaDadesUF"])) ? $dataPrg["taulaDadesUF"] : json_decode($dataPrg["taulaDadesUF"], true);
            $taulaDadesNFFiltrada = array();
            $blocId = array_search($values["tipusBlocModul"], ["mòdul", "1r. bloc", "2n. bloc"]);
            foreach ($taulaDadesNF as $row) {
                $rowBlocId = $this->getBlocIdFromTaulaUF($taulaDadesUFPrg, $row["unitat formativa"]);
                if($rowBlocId==$blocId){
                    $taulaDadesNFFiltrada[] = $row;
                }
            }
        }else{
            $taulaDadesNF = FALSE;
        }
        
        if(!empty($taulaDadesNFFiltrada)){
             for ($i=0; $i<count($taulaDadesUnitats); $i++){
                if(empty($originalTaulaDadesUnitats[$i]["nom"])){
                    $taulaDadesUnitats[$i]["nom"] = $taulaDadesNFFiltrada[$i]["nom"];
                }else{
                    $taulaDadesUnitats[$i]["nom"] = $originalTaulaDadesUnitats[$i]["nom"];
                }
             }
        }
        $values["taulaDadesUnitats"] = $taulaDadesUnitats;
        
        $ufTable = $values["taulaDadesUF"];
        if(!is_array($ufTable)){
            $ufTable = json_decode($ufTable, TRUE);
        }
        foreach ($ufTable as $key => $value) {
            if($ufTable[$key]["ponderació"]=="0"){
                $ufTable[$key]["ponderació"]=$ufTable[$key]["hores"];
            }
        }
        $values["taulaDadesUF"]=$ufTable;

        $data = $isArray?$values:json_encode($values);
        return $data;
    }
    
    /**
     * Calcula el valor de los campos calculables
     * @param JSON $data
     */
    public function updateCalculatedFieldsOnSave($data, $originalDataKeyValue=FALSE) {

        $isArray = is_array($data);
        $values = $isArray?$data:json_decode($data, true);

        $taulaDadesUF = (is_array($values["taulaDadesUF"])) ? $values["taulaDadesUF"] : json_decode($values["taulaDadesUF"], true);
        $taulaDadesUnitats = (is_array($values["taulaDadesUnitats"])) ? $values["taulaDadesUnitats"] : json_decode($values["taulaDadesUnitats"], true);
        $taulaCalendari = (is_array($values["calendari"])) ? $values["calendari"] : json_decode($values["calendari"], true);

        if($values["nsProgramacio"]){
            $dataPrg = $this->getRawDataProjectFromOtherId($values["nsProgramacio"]);
            if(!is_array($dataPrg)){
                $dataPrg = json_decode($dataPrg, true);
            }
            $taulaDadesNF = (is_array($dataPrg["taulaDadesNuclisFormatius"])) ? $dataPrg["taulaDadesNuclisFormatius"] : json_decode($dataPrg["taulaDadesNuclisFormatius"], true);
            $taulaDadesUFPrg = (is_array($dataPrg["taulaDadesUF"])) ? $dataPrg["taulaDadesUF"] : json_decode($dataPrg["taulaDadesUF"], true);
            $taulaDadesNFFiltrada = array();
            $blocId = array_search($values["tipusBlocModul"], ["mòdul", "1r. bloc", "2n. bloc"]);
            foreach ($taulaDadesNF as $row) {
                $rowBlocId = $this->getBlocIdFromTaulaUF($taulaDadesUFPrg, $row["unitat formativa"]);
                if($rowBlocId==$blocId){
                    $taulaDadesNFFiltrada[] = $row;
                }
            }
        }else{
            $taulaDadesNF = FALSE;
        }
        
        if ($taulaCalendari!=NULL && $taulaDadesUnitats!=NULL){
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
                    if($taulaDadesUnitats[$i]["nom"]==$taulaDadesNFFiltrada[$i]["nom"]){
                        $taulaDadesUnitats[$i]["nom"] = "";
                    }
                }
            }

            if ($taulaDadesUF!=NULL){
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

            $values["durada"] = $horesUF[0];
            $values["taulaDadesUnitats"] = $taulaDadesUnitats;
            $values["taulaDadesUF"] = $taulaDadesUF;
        }

        $taulaJT = (is_array($values["datesJT"])) ? $values["datesJT"] : json_decode($values["datesJT"], true);

        if ($taulaJT!=NULL){
            $hiHaRecuperacio = FALSE;
            for ($i=0; !$hiHaRecuperacio && $i<count($taulaJT); $i++){
                $hiHaRecuperacio = $taulaJT[$i]["hiHaRecuperacio"];
            }
            $values["hiHaRecuperacioPerJT"] = $hiHaRecuperacio;
        }

        $taulaEAF = (is_array($values["datesEAF"])) ? $values["datesEAF"] : json_decode($values["datesEAF"], true);

        if ($taulaEAF!=NULL){
            $hiHaSolucio = FALSE;
            $hiHaEnunciatRecuperacio = FALSE;
            for ($i=0; $i<count($taulaEAF); $i++){
                $hiHaSolucio |= $taulaEAF[$i]["hiHaSolucio"];
                $hiHaEnunciatRecuperacio |= $taulaEAF[$i]["hiHaEnunciatRecuperacio"];
            }

            $values["hiHaSolucioPerEAF"] = $hiHaSolucio === 0 ? FALSE : TRUE ;
            $values["hiHaEnunciatRecuperacioPerEAF"] = $hiHaEnunciatRecuperacio === 0 ? FALSE : TRUE ;
        }

        $taulaAC = (is_array($values["datesAC"])) ? $values["datesAC"] : json_decode($values["datesAC"], true);

        if ($taulaAC!=NULL){
            $hiHaSolucio = FALSE;
            for ($i=0; !$hiHaSolucio && $i<count($taulaAC); $i++){
                $hiHaSolucio = $taulaAC[$i]["hiHaSolucio"];
            }
            $values["hiHaSolucioPerAC"] = $hiHaSolucio;
        }

        $data = $isArray?$values:json_encode($values);
        return parent::updateCalculatedFieldsOnSave($data, $originalDataKeyValue);
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
                    $dataEnunciaRectOld = $item["enunciat recuparació"];
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

    public function getCourseId() {
        $data = $this->getCurrentDataProject();
        return $data["moodleCourseId"];
    }
}
