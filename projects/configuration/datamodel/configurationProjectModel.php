<?php
/**
 * configurationProjectModel
 * @culpable Rafael Claver
 */
if (!defined("DOKU_INC")) die();

class configurationProjectModel extends AbstractProjectModel{

    public function __construct($persistenceEngine)  {
        parent::__construct($persistenceEngine);
        $this->needGenerateAction=false;
    }

    public function generateProject() {
        //
        // NOTA:
        //    Este proyecto no es generable
        //
    }

    public function getMetaDataActionViews() {
        return $this->projectMetaDataQuery->getMetaDataActionViews();
    }

    public function updateConditions($arraytaula) {
        foreach ($arraytaula as $key1 => $value1) {
            $parameters = (is_string($value1["parameters"]) && strlen($value1["parameters"])>0)?json_decode($value1["parameters"], TRUE):$value1["parameters"];                    
            if(isset($parameters["conditions"])){
                $modif=FALSE;
                foreach ($parameters["conditions"] as $key2 => $condition) {
                    foreach ($condition as $key3 => $value2) {
                        if(is_string($key3)){
                            if($value2[0]=="[" && $value2[-1]=="]"){
                                $parameters["conditions"][$key2][$key3] = "\\[".substr($value2, 1, -1)."\\]";
                                $modif=TRUE;
                            }
                        }else{
                            foreach ($value2 as $key4 => $value3) {
                                if($value3[0]=="[" && $value3[-1]=="]"){
                                    $parameters["conditions"][$key2][$key3][$key4] = "\\[".substr($value3, 1, -1)."\\]";
                                    $modif=TRUE;
                                }
                            }
                        }
                    }
                }
                if($modif){
                    $arraytaula[$key1]["parameters"] = json_encode($parameters);
                }
            }
        }
        return $arraytaula;
    }
        
    public function updateCalculatedFieldsOnRead($data, $originalDataKeyValue = FALSE, $subset = FALSE) {
        $data = parent::updateCalculatedFieldsOnRead($data, $subset);
        $isArray = is_array($data);
        $values = $isArray ? $data : json_decode($data, true);
        $originalValues = $isArray ? $originalDataKeyValue : json_decode($originalDataKeyValue, true);

        $arraytaula =  IocCommon::toArrayThroughArrayOrJson($values["arraytaula"]);
        foreach ($arraytaula as $key1 => $value1) {
            $parameters = (is_string($value1["parameters"]) && strlen($value1["parameters"])>0)?json_decode($value1["parameters"], TRUE):$value1["parameters"];                    
            if(isset($parameters["conditions"])){
                $modif=FALSE;
                foreach ($parameters["conditions"] as $key2 => $condition) {
                    foreach ($condition as $key3 => $value2) {
                        if(is_string($key3)){
                            if($value2[0]=="\"" && $value2[1]=="["
                                    && $value2[-1]=="\"" && $value2[-2]=="]"){
                                $parameters["conditions"][$key2][$key3] = substr($value2, 1, -1);
                                $modif=TRUE;
                            }elseif($value2[0]=="\\" && $value2[1]=="["
                                    && $value2[-1]=="]" && $value2[-2]=="\\"){
                                $parameters["conditions"][$key2][$key3] ="[".substr($value2, 2, -2)."]";
                                $modif=TRUE;
                            }
                        }else{
                            foreach ($value2 as $key4 => $value3) {
                                if($value3[0]=="\"" && $value3[1]=="["
                                        && $value3[-1]=="\"" && $value3[-2]=="]"){
                                    $parameters["conditions"][$key2][$key3][$key4] = substr($value3, 1, -1);
                                    $modif=TRUE;
                                }elseif($value3[0]=="\\" && $value3[1]=="["
                                        && $value3[-1]=="]" && $value3[-2]=="\\"){
                                    $parameters["conditions"][$key2][$key3][$key4] ="[".substr($value3, 2, -2)."]";
                                    $modif=TRUE;
                                }
                            }
                        }
                    }
                }
                if($modif){
                    $values["arraytaula"][$key1]["parameters"] = json_encode($parameters);
                }
            }
        }
        
        $data = $isArray?$values:json_encode($values);
        return $data;
    }
}
