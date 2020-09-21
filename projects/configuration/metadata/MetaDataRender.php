<?php
/**
 * Component: Project / MetaData
 */
namespace configuration;
if (!defined("DOKU_INC")) die();

class MetaDataRender extends \MetaDataRenderAbstract {

//    /**
//     * @param $metaDataEntityWrapper -> Entities array
//     */
//    public function render($metaDataEntityWrapper) {
//        $objAux = json_decode($metaDataEntityWrapper[0]->getArrayFromModel(), true);
//        $structure = json_decode($objAux['metaDataStructure'], true);
//        $types = json_decode($objAux['metaDataTypesDefinition'], true);
//        $values = json_decode($objAux['metaDataValue'], true);
//
//        $returnTree = [];
//        $returnTree = $this->runParser($values, $structure, $types);
//
//        return $returnTree;
//    }

//    protected function runParser($values, $structure, $types){
//        return $this->_runParser($values, $structure, $types, "");
//    }

//    protected function _runCompositeParser($values, &$properties, $types, $newid){
//        switch ($properties['type']){
//            case 'object':
//                if (!isset($properties['renderAsMultiField']) || $properties['renderAsMultiField']){
//                    $properties['renderAsMultiField']=true;
//                    $_structure = $this->_getObjectStructureKeys($properties, $types);
//                    $values = $this->_runParser($values, $_structure, $types, $newid."#");
//                }
//                break;
//            case 'objectArray':
//                if (isset($properties['renderAsMultiField']) && $properties['renderAsMultiField']){
//                    $_structure = $this->_getObjectStructureKeys($properties, $types);
//                    $values = $this->_runArrayItemsParser($values, $_structure, $types, $newid."#");
//                }
//                break;
//            case 'array':
//            case 'table':
//                if (isset($properties['renderAsMultiField']) && $properties['renderAsMultiField']){
//                    $values = $this->_runTableParser($values, $properties, $types, $newid."#");
//                }
//                break;
//        }
//        return $values;
//    }

//    protected function _runParser($values, $structure, $types, $id){
//        $tree = [];
//        foreach ($structure as $field => $properties) {
//                $newid = $id.$field;
//                if (array_key_exists($properties['type'], $types)){
//                    $original_type = $properties['type'];
//                    $td = $types[$properties['type']];
//                    $properties['type']=$td['type'] ;
//                    if (isset($td['typeDef'])) {
//                        $properties['typeDef']=$td['typeDef'];
//                    }else {
//                        $properties['typeDef']=$original_type;
//                    }
//                }
//                $_values = $this->_getValue($field, $values, $properties, $types);
//                $_values['value'] = $this->_runCompositeParser($_values['value'], $properties, $types, $newid);
//                $_values['default'] = $this->_runCompositeParser($_values['default'], $properties, $types, $newid);
//
//                $tree[$field] = $properties;
//                $tree[$field]['default'] = $_values['default'];
//                if (isset($_values['defaultRow'])) {
//                    $tree[$field]['defaultRow'] = $_values['defaultRow'];
//                }
//                if (isset($values[$field])) {
//                    $tree[$field]['value'] = $_values['value'];
//                }else{
//                    if ($properties['mandatory']){
//                        $tree[$field]['value'] = $_values['default'];
//                    }
//                }
//                $tree[$field]['id'] = $newid;
//        }
//        return $tree;
//    }

//    protected function _runTableParser($itemValues, $properties, $types, $id){
//        $tree = [];
//        for($i = 0, $len = count($itemValues); $i < $len; $i++) {
//            $newid = $id.$i;
//            if (is_array($itemValues[$i])){
//                $tree [] = $this->_runTableParser($itemValues[$i], $properties, $types, $newid."#");
//            }else{
//                $sproperties = array();
//                $sproperties['type'] = $properties['typeDef'];
//                $sproperties['mandatory'] = $properties['mandatory'];
//                $_values = $this->_getSingleValue($itemValues[$i], $sproperties, $types, self::$DEFAULT_SINGLE_VALUES[$properties['typeDef']]);
//                $tree []= $sproperties;
//                $tree[$i]['value'] = $_values['value'];
//                $tree[$i]['default'] = $_values['default'];
//                $tree[$i]['id'] = $newid;
//            }
//        }
//        return $tree;
//    }
//
//    protected function _runArrayItemsParser($itemValues, $structure, $types, $id){
//        $tree = [];
//        for($i = 0, $len = count($itemValues); $i < $len; $i++) {
//            $newid = $id.$i."#";
//            $tree []= $this->_runParser($itemValues[$i], $structure, $types, $newid);
//        }
//        return $tree;
//    }
//
//
//    private function _getObjectStructureKeys($properties, $types){
//        $ret = NULL;
//        if (array_key_exists($properties['type'], $types)){
//            $ret = $types[$properties['type']]['keys'];
//        }else if (isset($properties['typeDef'])){
//            $ret = $types[$properties['typeDef']]['keys'];
//        }else{
//            $ret = $properties['keys'];
//        }
//        return $ret;
//    }
//
//    private function _getValue($field, $values, $properties, $types){
//        $ret = NULL;
//        switch ($properties["type"]) {
//            case "boolean":
//            case "number":
//            case "string":
//                $dv = self::$DEFAULT_SINGLE_VALUES[$properties["type"]];
//                $ret = $this->_getSingleValue($values[$field], $properties, $types, $dv);
//                break;
//            case "array":
//            case "table":
//                $ret =$this->_getSingleArray($values[$field], $properties, $types);
//                break;
//            case "object":
//                $ret = $this->_getObjectValue($field, $values, $properties, $types);
//                break;
//            case "objectArray":
//                $ret = $this->_getObjectArrayValue($field, $values, $properties, $types);
//                break;
//            default:
//                if (array_key_exists($properties['type'], $types)){
//                    $properties['type']=$types[$properties['type']]['type'] ;
//                    if (isset($types[$properties['type']]['typeDef'])){
//                        $properties['typeDef']=$types[$properties['type']]['typeDef'];
//                    }else if (isset($types[$properties['type']]['keys'])){
//                        $properties['keys']=$types[$properties['type']]['keys'];
//                    }
//                    $ret = $this->_getValue($field, $values, $properties, $types);
//                }else{
//                    throw new \IncorrectParametersException();
//                }
//                break;
//        }
//        return $ret;
//    }
//
//    private function _getDefaultSingleArrayItem($properties, $types){
//        $cols = isset($properties['array_columns']) ? $properties['array_columns'] : 1;
//        $singleValue = $this->_getValue("", array(), array("type" => $properties['typeDef']), $types)['value'];
//        if ($cols > 1){
//            $_vcols = [];
//            for ($j=0; $j<$cols; $j++){
//                $_vcols[] = $singleValue;
//            }
//            $_value = $_vcols;
//        }else{
//            $_value = $singleValue;
//        }
//        return $_value;
//    }
//
//    private function _getDefaultSingleArray($properties, $types, $defaultRow){
//        if (isset($properties['default'])){
//            $_values = $properties['default'];
//        }else{
//            $_values= [];
//            $rows = isset($properties['array_rows']) ? $properties['array_rows'] : 0;
//            for($i=0; $i<$rows; $i++){
//                $_values[]= $defaultRow;
//            }
//        }
//        return $_values;
//    }
//
//    private function _getSingleArray($values, $properties, $types){
//        $_values = [];
//        $_values['defaultRow'] = $this->_getDefaultSingleArrayItem($properties, $types);
//        $_values['default'] = $this->_getDefaultSingleArray($properties, $types, $_values['defaultRow']);
//        $_values['value'] = ($values) ? $values : $_values['default'];
//        return $_values;
//    }
//
//    private function _getSingleValue($values, $properties, $types, $dv){
//        $_values = [];
//        $_values['default'] = isset($properties['default']) ? $properties['default'] : $dv;
//        $_values['value'] = ($values) ? $values : $_values['default'];
//        return $_values;
//    }
//
//    private function _getDefaultObjectValue($properties, $types){
//        if (isset($properties['default'])){
//            $_values = $properties['default'];
//        }else{
//            $_structure = $this->_getObjectStructureKeys($properties, $types);
//            $_values = [];
//            foreach ($_structure as $key => $value) {
//                $_values[$key] = $this->_getValue($key, $_values, $value, $types)['value'];
//            }
//        }
//        return $_values;
//    }
//
//    private function _getObjectValue($field, $values, $properties, $types){
//        $_values = [];
//        $_values['default'] = $this->_getDefaultObjectValue($properties, $types);
//        $_values['value'] = (isset($values[$field])) ? $values[$field] : $_values['default'];
//        return $_values;
//    }
//
//    private function _getDefaultObjectArrayValue($properties, $types, $defaultRow){
//        if (isset($properties['default'])){
//            $_values = $properties['default'];
//        }else{
//            $_values = [];
//            $rows = isset($properties['array_rows']) ? $properties['array_rows'] : 0;
//            for ($i=0; $i<$rows; $i++) {
//                $_values[] = $defaultRow;
//            }
//        }
//        return $_values;
//    }
//
//    private function _getObjectArrayValue($field, $values, $properties, $types){
//        $_values = [];
//        $_values['defaultRow'] = $this->_getObjectValue("", array(), $properties, $types)['value'];
//        $_values['default'] = $this->_getDefaultObjectArrayValue($properties, $types, $_values['defaultRow']);
//        $_values['value'] = (isset($values[$field])) ? $values[$field] : $_values['default'];
//        return $_values;
//    }

}
