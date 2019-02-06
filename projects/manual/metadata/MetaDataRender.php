<?php
/**
 * Component: Project / MetaData
 */
namespace manual;
require_once(DOKU_PLUGIN . 'wikiiocmodel/metadata/MetaDataRenderAbstract.php');

class MetaDataRender extends \MetaDataRenderAbstract {
    //const DEFAULT_SINGLE_VALUES = array("string"=>"", "number" => 0, "boolean" => false);
    public static $DEFAULT_SINGLE_VALUES = ["string"=>"", "number"=>0, "boolean"=>false, "date"=>""];

    /**
     * @param $metaDataEntityWrapper -> Entities array
     */
    public function render($metaDataEntityWrapper) {
        $objAux = json_decode($metaDataEntityWrapper[0]->getArrayFromModel(), true);
        $structure = json_decode($objAux['metaDataStructure'], true);
        $types = json_decode($objAux['metaDataTypesDefinition'], true);
        $values = $this->processValues(json_decode($objAux['metaDataValue'], true));

        $returnTree = [];
        $returnTree = $this->runParser($values, $structure, $types);
//        $returnTree['structure'] = $this->initParser($values, $structure, $types);
//        $returnTree['value'] = $this->flatten($returnTree['structure']);

        return $returnTree;
    }
    
    protected function processValues($values){
        $taulaDadesUF = json_decode($values["taulaDadesUF"], true);
        $taulaDadesUnitats = json_decode($values["taulaDadesUnitats"], true);
        $taulaCalendari = json_decode($values["calendari"], true);
        if($taulaCalendari!=NULL && $taulaDadesUnitats!=NULL){
            $hores = array();
            for($i=0; $i<count($taulaCalendari);$i++){
                $idU = intval($taulaCalendari[$i]["unitat"]);
                if(!isset($hores[$idU])){
                    $hores[$idU]=0;
                }
                $hores[$idU]+= $taulaCalendari[$i]["hores"];                
            }
            
            $horesUF = array();
            $horesUF[0] = 0;
            for($i=0; $i<count($taulaDadesUnitats);$i++){
                $idU = intval($taulaDadesUnitats[$i]["unitat"]);
                if(isset($hores[$idU])){
                    $taulaDadesUnitats[$i]["hores"]=$hores[$idU];
                }
                $idUf = intval($taulaDadesUnitats[$i]["unitat formativa"]);
                if(!isset($horesUF[$idUf])){
                    $horesUF[$idUf]=0;
                }
                $horesUF[0]+= $taulaDadesUnitats[$i]["hores"];
                $horesUF[$idUf]+= $taulaDadesUnitats[$i]["hores"];
            }

            if($taulaDadesUF!=NULL){
                for($i=0; $i<count($taulaDadesUF);$i++){
                     $idUf = intval($taulaDadesUF[$i]["unitat formativa"]);
                     if(isset($horesUF[$idUf])){
                         $taulaDadesUF[$i]["hores"]=$horesUF[$idUf];
                     }
                 }
            }
            $values["durada"] = json_encode($horesUF[0]);
            $values["taulaDadesUnitats"] = json_encode($taulaDadesUnitats);
            $values["taulaDadesUF"] = json_encode($taulaDadesUF);
        }
        return $values;
    }

    protected function runParser($values, $structure, $types){
        return $this->_runParser($values, $structure, $types, "");
    }

    protected function _runCompositeParser($values, &$properties, $types, $newid){
        switch ($properties['type']){
            case 'object':
                if(!isset($properties['renderAsMultiField']) || $properties['renderAsMultiField']){
                    $properties['renderAsMultiField']=true;
                    $_structure = $this->_getObjectStructureKeys($properties, $types);
                    $values = $this->_runParser($values, $_structure, $types, $newid."#");
                }
                break;
            case 'objectArray':
                if(isset($properties['renderAsMultiField']) && $properties['renderAsMultiField']){
                    $_structure = $this->_getObjectStructureKeys($properties, $types);
                    $values = $this->_runArrayItemsParser($values, $_structure, $types, $newid."#");
                }
                break;
            case 'array':
            case 'table':
                if(isset($properties['renderAsMultiField']) && $properties['renderAsMultiField']){
                    $values = $this->_runTableParser($values, $properties, $types, $newid."#");
                }
                break;
        }
        return $values;
    }

    protected function _runParser($values, $structure, $types, $id){
        $tree = [];
        foreach ($structure as $field => $properties) {
                $newid = $id.$field;
                if(array_key_exists($properties['type'], $types)){
                    $original_type = $properties['type'];
                    $td = $types[$properties['type']];
                    $properties['type']=$td['type'] ;
                    if(isset($td['typeDef'])){
                        $properties['typeDef']=$td['typeDef'];
//                    }else if(isset($td['keys'])){
//                        $properties['keys']=$td['keys'];
                    }else{
                        $properties['typeDef']=$original_type;
                    }
                }
                $_values = $this->_getValue($field, $values, $properties, $types);
                $_values['value'] = $this->_runCompositeParser($_values['value'], $properties, $types, $newid);
                $_values['default'] = $this->_runCompositeParser($_values['default'], $properties, $types, $newid);

                $tree[$field] = $properties;
                $tree[$field]['default'] = $_values['default'];
                if (isset($_values['defaultRow'])) {
                    $tree[$field]['defaultRow'] = $_values['defaultRow'];
                }
                if (isset($values[$field])) {
                    $tree[$field]['value'] = $_values['value'];
                }else{
                    if($properties['mandatory']){
                        $tree[$field]['value'] = $_values['default'];
                    }
                }
                $tree[$field]['id'] = $newid;
        }
        return $tree;
    }

    protected function _runTableParser($itemValues, $properties, $types, $id){
        $tree = [];
        for($i = 0, $len = count($itemValues); $i < $len; $i++) {
            $newid = $id.$i;
            if(is_array($itemValues[$i])){
                $tree [] = $this->_runTableParser($itemValues[$i], $properties, $types, $newid."#");
            }else{
                $sproperties = array();
                $sproperties['type'] = $properties['typeDef'];
                $sproperties['mandatory'] = $properties['mandatory'];
                $_values = $this->_getSingleValue($itemValues[$i], $sproperties, $types, self::$DEFAULT_SINGLE_VALUES[$properties['typeDef']]);
                $tree []= $sproperties;
                $tree[$i]['value'] = $_values['value'];
                $tree[$i]['default'] = $_values['default'];
                $tree[$i]['id'] = $newid;
            }
        }
        return $tree;
    }

    protected function _runArrayItemsParser($itemValues, $structure, $types, $id){
        $tree = [];
        for($i = 0, $len = count($itemValues); $i < $len; $i++) {
            $newid = $id.$i."#";
            $tree []= $this->_runParser($itemValues[$i], $structure, $types, $newid);
        }
        return $tree;
    }


    private function _getObjectStructureKeys($properties, $types){
        $ret;
        if(array_key_exists($properties['type'], $types)){
            $ret = $types[$properties['type']]['keys'];
        }else if(isset($properties['typeDef'])){
            $ret = $types[$properties['typeDef']]['keys'];
        }else{
            $ret = $properties['keys'];
        }
        return $ret;
    }

    private function _getValue($field, $values, $properties, $types){
        $ret;
        switch ($properties["type"]) {
            case "date":
            case "boolean":
//                $dv = false;
            case "number":
//                $dv = isset($dv)?$dv:0;
            case "string":
//                $dv = isset($dv)?$dv:"";
                $dv = self::$DEFAULT_SINGLE_VALUES[$properties["type"]];
                $ret = $this->_getSingleValue($values[$field], $properties, $types, $dv);
                break;
            case "array":
            case "table":
                $ret =$this->_getSingleArray($values[$field], $properties, $types);
                break;
            case "object":
                $ret = $this->_getObjectValue($field, $values, $properties, $types);
                break;
            case "objectArray":
                $ret = $this->_getObjectArrayValue($field, $values, $properties, $types);
                break;
            default:
                if(array_key_exists($properties['type'], $types)){
                    $properties['type']=$types[$properties['type']]['type'] ;
                    if(isset($types[$properties['type']]['typeDef'])){
                        $properties['typeDef']=$types[$properties['type']]['typeDef'];
                    }else if(isset($types[$properties['type']]['keys'])){
                        $properties['keys']=$types[$properties['type']]['keys'];
                    }
                    $ret = $this->_getValue($field, $values, $properties, $types);
                }else{
                    throw new \IncorrectParametersException();
                }
                break;
        }
        return $ret;
    }

    private function _getDefaultSingleArrayItem($properties, $types){
        $cols = isset($properties['array_columns'])?$properties['array_columns']:1;
        $singleValue = $this->_getValue("", array(), array("type" => $properties['typeDef']), $types)['value'];
        if($cols>1){
            $_vcols = [];
            for($j=0; $j<$cols; $j++){
                $_vcols[]= $singleValue;
            }
            $_value = $_vcols;
        }else{
            $_value = $singleValue;
        }
        return $_value;
    }

    private function _getDefaultSingleArray($properties, $types, $defaultRow){
        if(isset($properties['default'])){
            $_values = $properties['default'];
        }else{
            $_values= [];

            $rows = isset($properties['array_rows'])?$properties['array_rows']:0;
            for($i=0; $i<$rows; $i++){
                $_values[]= $defaultRow;
            }
        }
        return $_values;
    }

    private function _getSingleArray($values, $properties, $types){
        $_values = [];
        $_values['defaultRow'] = $this->_getDefaultSingleArrayItem($properties, $types);
        $_values['default'] = $this->_getDefaultSingleArray($properties, $types, $_values['defaultRow']);
        if ($values) {
            $_values['value'] = $values;
        }else{
            $_values['value'] =$_values['default'];
        }
        return $_values;
    }

    private function _getSingleValue($values, $properties, $types, $dv){
        $_values = [];
        $_values['default'] = isset($properties['default'])?$properties['default']:$dv;
        if ($values) {
            $_values['value'] = $values;
        }else{
            $_values['value'] = $_values['default'];
        }
        return $_values;
    }

    private function _getDefaultObjectValue($properties, $types, $defRow=FALSE){
        if(!$defRow && isset($properties['default'])){
            $_values = $properties['default'];
        }else{
            $_structure = $this->_getObjectStructureKeys($properties, $types);
            $_values = [];
            foreach ($_structure as $key => $value) {
                $_values[$key] = $this->_getValue($key, $_values, $value, $types)['value'];
            }
        }
        return $_values;
    }

    private function _getObjectValue($field, $values, $properties, $types, $defRow=FALSE){
        $_values = [];
        $_values['default'] = $this->_getDefaultObjectValue($properties, $types, $defRow);
        if (isset($values[$field])) {
            $_values['value'] = $values[$field];
        }else{
            $_values['value'] =$_values['default'];
        }
        return $_values;
    }

    private function _getDefaultObjectArrayValue($properties, $types, $defaultRow){
        if(isset($properties['default'])){
            $_values = $properties['default'];
        }else{
            $_values= [];

            $rows = isset($properties['array_rows'])?$properties['array_rows']:0;
            for($i=0; $i<$rows; $i++){
                $_values[]= $defaultRow;
            }
        }
        return $_values;
    }

    private function _getObjectArrayValue($field, $values, $properties, $types){
        $_values = [];
        $_values['defaultRow'] = $this->_getObjectValue("", array(), $properties, $types, true)['value'];
        $_values['default'] = $this->_getDefaultObjectArrayValue($properties, $types, $_values['defaultRow']);
        if (isset($values[$field])&&!empty($values[$field])) {
            $_values['value'] = $values[$field];
        }else{
            $_values['value'] =$_values['default'];
        }
        return $_values;
    }
}
