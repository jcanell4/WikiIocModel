<?php
/**
 * sintesiProjectModel
 * @culpable Rafael Claver
 */
if (!defined("DOKU_INC")) die();

class sintesiProjectModel extends MoodleProjectModel {

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

    public function createTemplateDocument($data=NULL){
        StaticUniqueContentFileProjectModel::createTemplateDocument($this);
    }

    /**
     * Calcula el valor de los campos calculables
     * @param JSON $data
     */
    public function updateCalculatedFieldsOnSave($values) {

        $taulaCalendari = (is_array($values["calendari"])) ? $values["calendari"] : json_decode($values["calendari"], true);

        if ($taulaCalendari!=NULL){
            $hores = 0;
            for ($i=0; $i<count($taulaCalendari); $i++){
                $hores+= $taulaCalendari[$i]["hores"];
            }

            $values["durada"] = $hores;
        }
        return parent::updateCalculatedFieldsOnSave($values);
    }

    /**
     * Devuelve la lista de archivos que se generan a partir de la configuración
     * indicada en el archivo 'configRender.json'
     * Esos archivos se guardan en WikiGlobalConfig::getConf('mediadir')
     * El nombre de estos archivos se construyó, en el momento de su creación, usando el nombre del proyecto
     * @param string $base_dir : directori wiki del projecte
     * @param string $old_name : nom actual del projecte
     * @return array : lista de ficheros
     */
    protected function listGeneratedFilesByRender($base_dir, $old_name) {
        $basename = str_replace([":","/"], "_", $base_dir) . "_" . $old_name;
        return [$basename.".zip"];
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
