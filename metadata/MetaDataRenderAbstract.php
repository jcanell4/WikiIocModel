<?php

/**
 * Component: Project / MetaData
 * Status: @@Tested
 * Purposes:
 * - Abstract class that must inherit all entities
 * @author Miguel Àngel Lozano Márquez<mlozan54@ioc.cat>
 */
if (!defined("DOKU_INC"))
    die();
if (!defined('DOKU_PLUGIN')) {
    define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
}

require_once( DOKU_INC . 'inc/JSON.php' );
require_once (DOKU_PLUGIN . 'wikiiocmodel/metadata/MetaDataRenderInterface.php');
require_once (DOKU_PLUGIN . 'wikiiocmodel/metadata/MetaDataExceptions.php');

abstract class MetaDataRenderAbstract implements MetaDataRenderInterface {
    public static $DEFAULT_SINGLE_VALUES = ["string"=>"", "textarea"=>"", "number"=>0, "boolean"=>false, "date"=>""];
    private $projectId;
    private $values;

//
//    /**
//     * Purpose:
//     * - Basis function to returns the same array of entities (converting it to JSON)
//     * @param $metaDataEntityWrapper -> Entities array
//     * Restrictions:
//     * @return JSON (convert each Entity model to a JSON element)
//     */
//    public function render($metaDataEntityWrapper) {
//        $toReturn = array();
//
//        for ($i = 0; $i < sizeof($metaDataEntityWrapper); $i++) {
//            $toReturn[$i]=$metaDataEntityWrapper[$i]->getArrayFromModel();
//        }
//
//        return $toReturn;
//    }

    /**
     * @param $metaDataEntityWrapper -> Entities array
     */
    public function render($metaDataEntityWrapper) {
        $objAux = json_decode($metaDataEntityWrapper[0]->getArrayFromModel(), true);
        $this->projectId = $objAux["idResource"];
        $structure = json_decode($objAux['metaDataStructure'], true);
        $types = json_decode($objAux['metaDataTypesDefinition'], true);
        $this->values = $this->processValues(json_decode($objAux['metaDataValue'], true));

        $returnTree = [];
        $returnTree = $this->runParser($this->values, $structure, $types);

        return $returnTree;
    }

    protected function processValues($values){
        return $values;
    }

    protected function runParser($values, $structure, $types){
        //retorna els valors estructurats d'acord amb el seu tipus, el valor emmagatzemat, el valor per defecte i les seves propietats
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
                if (isset($_values['rowTypes'])) {
                    $tree[$field]['rowTypes'] = $_values['rowTypes'];
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
            case "number":
            case "decimal":
            case "string":
            case "textarea":
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
                    $typeDef = $properties['type'];
                    $properties['type']=$types[$properties['type']]['type'] ;
//                    if(isset($types[$properties['type']]['typeDef'])){
                    if(isset($types[$typeDef]['typeDef'])){
                        $properties['typeDef']=$types[$typeDef]['typeDef'];
                    }else if(isset($types[$typeDef]['keys'])){
                        $properties['keys']=$types[$typeDef]['keys'];
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
        if(isset($properties["calculatedDefault"])){
            $_values = $this->getCalculateValue($properties["calculatedDefault"]);
//        }elseif(isset($properties["calculate"])){
//            $_values = $this->getCalculateValue($properties["calculate"]);
        }elseif(isset($properties['default'])){
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
            $_values['value'] = is_string($values)?json_decode($values, true):$values;
            $rows = isset($properties['array_rows'])?$properties['array_rows']:0;
            for($i= count($_values['value']); $i<$rows; $i++){
                $_values['value'][]= $_values['defaultRow'];
            }
        }else{
            $_values['value'] =$_values['default'];
        }
        return $_values;
    }

    private function _getSingleValue($values, $properties, $types, $dv){
        $_values = [];
        if(isset($properties["calculatedDefault"])){
            $_values['default'] = $this->getCalculateValue($properties["calculatedDefault"]);
//        }elseif(isset($properties["calculate"])){
//            $_values['default'] = $this->getCalculateValue($properties["calculate"]);
        }else{
            $_values['default'] = isset($properties['default'])?$properties['default']:$dv;
        }
        if ($values) {
            $_values['value'] = $values;
        }else{
            $_values['value'] = $_values['default'];
        }
        return $_values;
    }

    private function _getDefaultObjectValue($properties, $types, $defRow=FALSE){
        if(!$defRow && (isset($properties['default']) || isset($properties['calculatedDefault']) || isset($properties['calculate']))){
            if(isset($properties['calculatedDefault'])){
                $_values = $this->getCalculateValue($properties["calculatedDefault"]);
//            }elseif(isset($properties['calculate'])){
//                $_values = $this->getCalculateValue($properties["calculate"]);
            }else{
                $_values = $properties['default'];
            }
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

    private function _getObjectArrayTypes($properties, $types){
        $_structure = $this->_getObjectStructureKeys($properties, $types);
        $_types = [];
        foreach ($_structure as $key => $value) {
            $_types[$key] = $value["type"];
        }
        return $_types;
    }

    private function _getDefaultObjectArrayValue($properties, $types, $defaultRow){
        if(isset($properties["calculatedDefault"])){
            $_values = $this->getCalculateValue($properties["calculatedDefault"]);
//        }elseif(isset($properties["calculate"])){
//            $_values = $this->getCalculateValue($properties["calculate"]);
        }elseif(isset($properties['default'])){
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
        $_values["rowTypes"] = $this->_getObjectArrayTypes($properties, $types);
        if (isset($values[$field]) && !empty($values[$field])) {
            $_values['value'] = $values[$field];
        }else{
            $_values['value'] = $_values['default'];
        }
        return $_values;
    }

    private function getCalculateValue($calcDefProp) {
        return IocCommon::getCalculateFieldFromFunction($calcDefProp, $this->projectId, $this->values);
    }
}
