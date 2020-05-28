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
    
    public function validateFields(){
        $details="";
        $nfTable = $data["taulaDadesNuclisFormatius"];
        if(!is_array($nfTable)){
            $nfTable = json_decode($nfTable, TRUE);
        }
        $ufTable = $data["taulaDadesUF"];
        if(!is_array($ufTable)){
             $ufTable = json_decode($ufTable, TRUE);
        }
        $totalUfs=array();
        foreach ($nfTable as $item){
            if(!isset($totalUfs[$item["unitat formativa"]])){
                $totalUfs[$item["unitat formativa"]]=0;
            }
            $totalUfs[$item["unitat formativa"]] += $item["hores"];
        }
        
        foreach ($ufTable as $item) {
            if($item["hores"]!=$totalUfs[$item["unitat formativa"]]){
                throw new InvalidDataProjectException(
                    $this->id, 
                    sprintf("Les hores de la unitat formativa %s no coincideixen amb la suma de les hores dels seus nuclis foormatius (hores UF=%d, però suma hoes NF=%d)."
                            ,$item["unitat formativa"]
                            ,$item["hores"]
                            , $totalUfs[$item["unitat formativa"]])
                );
            }
        }
    }
    
     public function updateCalculatedFieldsOnRead($data) {
         $ufTable = $data["taulaDadesUF"];
         if(!is_array($ufTable)){
             $ufTable = json_decode($ufTable, TRUE);
         }
         foreach ($ufTable as $key => $value) {
             if($ufTable[$key]["ponderació"]=="0"){
                 $ufTable[$key]["ponderació"]=$ufTable[$key]["hores"];
             }
         }
         $data["taulaDadesUF"]=$ufTable;
         return $data;
     }
     
     public function updateCalculatedFieldsOnSave($data) {
        $ufTable = $data["taulaDadesUF"];
        if(!is_array($ufTable)){
             $ufTable = json_decode($ufTable, TRUE);
        }
        $blocTotal = 0;
        $i=0;
        $size = count($ufTable);
        while($i<$size) {
             $ufTable[$i]["hores"] = $ufTable[$i]["horesMinimes"]+$ufTable[$i]["horesLLiureDisposicio"];
             $blocTotal += $ufTable[$i]["hores"];
             $currentBloc=$ufTable[$i]["bloc"];
             $i++;
             if($i==$size || $ufTable[$i]['bloc']!=$currentBloc){
                $ufTable[$i-1]["bloc"]=$blocTotal;
                $blocTotal=0;
             }
        }
        $data["taulaDadesUF"]=$ufTable;
        return $data;
    }
}
