<?php
/**
 * ptfploeProjectModel
 * @culpable Rafael Claver
 */
if (!defined("DOKU_INC")) die();

class prgfploeProjectModel extends UniqueContentFileProjectModel{

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
