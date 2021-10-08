<?php
/**
 * sintesiProjectModel
 * @culpable Rafael Claver
 */
if (!defined("DOKU_INC")) die();

class sintesiProjectModel extends MoodleUniqueContentFilesProjectModel{

    public function __construct($persistenceEngine)  {
        parent::__construct($persistenceEngine);
        $this->needGenerateAction=false;
    }

    public function getProjectDocumentName() {
        $ret = $this->getCurrentDataProject();
        return $ret['fitxercontinguts'];
    }

    public function renameProject($ns, $new_name, $persons) {
        ProgramacioProjectModel::renameProject($ns, $new_name, $persons);
    }

//    public function generateProject() {
//        $ret = array();
//        //0. Obtiene los datos del proyecto
//        $ret = $this->getData();   //obtiene la estructura y el contenido del proyecto
//
//        //2. Establece la marca de 'proyecto generado'
//        $ret[ProjectKeys::KEY_GENERATED] = $this->getProjectMetaDataQuery()->setProjectGenerated();
//
//        if ($ret[ProjectKeys::KEY_GENERATED]) {
//            try {
//                //3. Otorga, a las Persons, permisos sobre el directorio de proyecto y añade enlace a dreceres
//                $params = $this->buildParamsToPersons($ret['projectMetaData'], NULL);
//                $this->modifyACLPageAndShortcutToPerson($params);
//            }
//            catch (Exception $e) {
//                $ret[ProjectKeys::KEY_GENERATED] = FALSE;
//                $this->getProjectMetaDataQuery()->setProjectSystemStateAttr("generated", FALSE);
//            }
//        }
//
//        return $ret;
//    }

//    public function createTemplateDocument($data=NULL){
//        StaticUniqueContentFileProjectModel::createTemplateDocument($this);
//    }

    public function updateCalculatedFieldsOnRead($data, $originalDataKeyValue=FALSE) {
        $data = parent::updateCalculatedFieldsOnRead($data);
        $isArray = is_array($data);
        $values = $isArray?$data:json_decode($data, true);
        $originalValues = $isArray?$originalDataKeyValue:json_decode($originalDataKeyValue, true);

        $resultatsAprenentatge = (is_array($values["resultatsAprenentatge"])) ? $values["resultatsAprenentatge"] : json_decode($values["resultatsAprenentatge"], true);
        $originalResultatsAprenentatge = (is_array($originalValues["resultatsAprenentatge"])) ? $originalValues["resultatsAprenentatge"] : json_decode($originalValues["resultatsAprenentatge"], true);
        $blocId = 0;
        if($values["nsProgramacio"]){
            $dataPrg = $this->getRawDataProjectFromOtherId($values["nsProgramacio"]);
            if(!is_array($dataPrg)){
                $dataPrg = json_decode($dataPrg, true);
            }
            $taulaDadesNF = (is_array($dataPrg["taulaDadesNuclisFormatius"])) ? $dataPrg["taulaDadesNuclisFormatius"] : json_decode($dataPrg["taulaDadesNuclisFormatius"], true);

            $taulaDadesUFPrg = (is_array($dataPrg["taulaDadesUF"])) ? $dataPrg["taulaDadesUF"] : json_decode($dataPrg["taulaDadesUF"], true);
        }else{
            $taulaDadesNF = FALSE;
        }
        
        
        for ($i=0; $i<count($resultatsAprenentatge); $i++){
           if(!empty($originalResultatsAprenentatge[$i]["id"])){
               $resultatsAprenentatge[$i]["id"] = $originalResultatsAprenentatge[$i]["id"];
           }
        }
        $values["resultatsAprenentatge"] = $resultatsAprenentatge;
        

        $data = $isArray?$values:json_encode($values);
        return $data;
    }

    /**
     * Calcula el valor de los campos calculables
     * @param JSON $data
     */
    public function updateCalculatedFieldsOnSave($values, $originalDataKeyValue=FALSE) {

        $taulaCalendari = (is_array($values["calendari"])) ? $values["calendari"] : json_decode($values["calendari"], true);

        if ($taulaCalendari!=NULL){
            $hores = 0;
            for ($i=0; $i<count($taulaCalendari); $i++){
                $hores+= $taulaCalendari[$i]["hores"];
            }

            $values["durada"] = $hores;
        }
        return parent::updateCalculatedFieldsOnSave($values, $originalDataKeyValue);
    }

    public function getCalendarDates() {
        $ret = array();
        $data = $this->getCurrentDataProject();
        if(is_string($data['calendari'])){
            $calendari = json_decode($data["calendari"], true);
        }else{
            $calendari = $data["calendari"];
        }
        foreach ($calendari as $item) {
            $ret[] = [
                "title"=>sprintf("%s - inici %s %d", $data["modulId"], $data["nomPeriode"], $item["període"]),
                "date"=>$item["inici"]
            ];
        }

        $dataEnunciatOld ="";
        $dataSolucioOld ="";
        $dataQualificacioOld ="";
        $datesAC = json_decode($data["dadesAC"], true);
        if(is_string($data['dadesAC'])){
            $datesAC = json_decode($data["dadesAC"], true);
        }else{
            $datesAC = $data["dadesAC"];
        }
        foreach ($datesAC as $item) {
            if($dataEnunciatOld!=$item["enunciat"]){
                $ret[] = [
                    "title"=>sprintf("%s - enunciat %s", $data["modulId"], $item['id']),
                    "date"=>$item["enunciat"]
                ];
                $dataEnunciatOld = $item["enunciat"];
            }
            if($dataQualificacioOld!=$item["qualificació"]){
                $ret[] = [
                    "title"=>sprintf("%s - qualificació %s", $data["modulId"], $item['id']),
                    "date"=>$item["qualificació"]
                ];
                $dataQualificacioOld = $item["qualificació"];
            }
        }
        return $ret;
    }

    public function getCourseId() {
        $data = $this->getCurrentDataProject();
        return $data["moodleCourseId"];
    }
}
