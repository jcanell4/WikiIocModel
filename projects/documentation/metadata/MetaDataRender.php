<?php
/**
 * Component: Project / MetaData
 */
namespace documentation;
require_once(DOKU_PLUGIN . 'wikiiocmodel/metadata/MetaDataRenderAbstract.php');

class MetaDataRender extends \MetaDataRenderAbstract {
    //const DEFAULT_SINGLE_VALUES = array("string"=>"", "number" => 0, "boolean" => false);
    public static $DEFAULT_SINGLE_VALUES = ["string"=>"", "number"=>0, "boolean"=>false];

    /**
     * @param $metaDataEntityWrapper -> Entities array
     */
    public function render($metaDataEntityWrapper) {
        $objAux = json_decode($metaDataEntityWrapper[0]->getArrayFromModel(), true);
        $structure = json_decode($objAux['metaDataStructure'], true);
        $types = json_decode($objAux['metaDataTypesDefinition'], true);
        $values = json_decode($objAux['metaDataValue'], true);

        $returnTree = [];
        $returnTree = $this->runParser($values, $structure, $types);
//        $returnTree['structure'] = $this->initParser($values, $structure, $types);
//        $returnTree['value'] = $this->flatten($returnTree['structure']);

        return $returnTree;
    }

//    protected function flatten($values) {
//        $flat = [];
//        foreach ($values as $key => $item) {
//            if (is_array($item['value'])) {
//                //si es un array s'ha d'aplanar
//                //$flat[$item['id']] = ($item['value']==NULL) ? "" : $this->flatten($item['value']);
//                //$newFlat = ($item['value']==NULL) ? array($item['id']=>"") : $this->flatten($item['value']);
//                $newFlat = $this->flatten($item['value']);
//                $flat = array_merge($flat, $newFlat);
//            }else if (isset($item['value']) && $item['id']) {
//                //és una fulla
//                $flat[$item['id']] = $item['value'];
//            }else if (is_array($item)) {
//                //$k = key($item);
//                //$flat[$key] = ($item==NULL) ? "" : $this->flatten($item);
//                $new2Flat = ($item==NULL) ? "" : $this->flatten($item);
//                $flat = array_merge($flat, $new2Flat);
//            }else if (gettype($item) === "string") {
//                //és una fulla
//                $flat[$key] = $item;
//            }
//        }
//        return $flat;
//    }

//    protected function initParser($values, &$structure, $types) {
//        $values = $this->defaultFillParser($values, $structure, $types);
//        $tree = $this->parser($values, $structure, $types);
//        return $tree;
//    }
//
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

    private function _getDefaultObjectValue($properties, $types){
        if(isset($properties['default'])){
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

    private function _getObjectValue($field, $values, $properties, $types){
        $_values = [];
        $_values['default'] = $this->_getDefaultObjectValue($properties, $types);
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
        $_values['defaultRow'] = $this->_getObjectValue("", array(), $properties, $types)['value'];
        $_values['default'] = $this->_getDefaultObjectArrayValue($properties, $types, $_values['defaultRow']);
        if (isset($values[$field])) {
            $_values['value'] = $values[$field];
        }else{
            $_values['value'] =$_values['default'];
        }
        return $_values;
    }

//    protected function defaultFillParser($values, &$structure, $definitionTypes) {
//        //Añade al array de campos de valores los campos de la estructura que le falten
//        foreach ($structure as $k => $v) {
//            if (!isset($values[$k])) {
//                if ($v['type']==='string') {
//                    $values[$k] = '';
//                }
//                else if ($v['type']==='array') {
//                    $values[$k] = array();
//                }else {
//                    $values[$k] = $this->defaultFillParser(array(), $types[$k]['keys'], $types);
//                }
//            }
//        }
//        return $values;
//    }
//
//    protected function parser($values, $structure, $definitionTypes) {
//        $tree = [];
//
//        // El primer nivell de l'estructura depén de l'estructura
//        foreach ($values as $key => $value) {
//            $prefix = $key;
//
//            // Si $value es un strig ho afegim a la estructura (fulla)
//            if ($structure[$key]['type'] === 'string') {
//                $tree[$key] = $structure[$key];
//                $tree[$key]['value'] = $value;
//            // Si $value es un array fem un parse (branca)
//            }else if ($structure[$key]['type'] === 'array') {
//                $tree[$key] = $structure[$key];
//                $tree[$key]['value'] = $this->parseArray($structure[$key]['typeDef'], $value, $definitionTypes, $prefix);
//            // Si $value es un object fem un parse (branca)
//            } else if ($structure[$key]['type'] === 'object') {
//                $tree[$key] = $structure[$key];
//                $tree[$key]['value'] = $this->parseObject($structure[$key]['typeDef'], $value, $definitionTypes, $prefix);
//            } else {
//                // Si no ho és busquem el seu tipus
//                $tree[$key] = $structure[$key];
//                $tree[$key]['value'] = $value;
//            }
//            $tree[$key]['id'] = $prefix;
//        }
//        return $tree;
//    }

//    protected function parseArray($type, $values, $definitionTypes, $prefix) {
//        $tree = [];
//        $item = null;
//
//        for ($i = 0, $len = count($values); $i < $len; $i++) {
////            $newPrefix = $prefix . "#" . $type . "#" . $i;
//            $newPrefix = $prefix . "#" . $i;
//
//            if ($definitionTypes[$type]) {
//                $item = $definitionTypes[$type];
//                if ($item['type'] === 'array') {
//                    $item['value'] = $this->parseArray($item['itemsType'], $values[$i], $definitionTypes, $newPrefix);
//
//                } else if ($item['type'] === 'object') {
//                    $item['value'] = $this->parseObject($item['keys'], $values[$i], $definitionTypes, $newPrefix);
//                    // Ja s'han fusionat les keys i els valors, no cal passar les keys
//                    unset($item['keys']);
//                } else {
//                    $item['value'] = $values[$i];
//                }
//
//            } else {
//                // TODO[Xavi] Si no s'ha especificat el tipus que fem?
//            }
//
//            $item['id'] = $newPrefix;
//            $tree[] = $item;
//        }
//
//        return $tree;
//    }
//
//    protected function parseObject($type, $values, $definitionTypes, $prefix) {
//        $tree = [];
//        $item = null;
//        $keys = $definitionTypes[$type]['keys'];
//
//        foreach ($keys as $key => $value) {
//            $newPrefix = $prefix . "#" . $key;
//            $definition = $definitionTypes[$value['tipus']];
//
//            if ($definition) {
//                $item = $definition;
//            } else {
//                // ALERTA[Xavi] Considerar si això és el més correcte o s'han de copiar les propietats del $keys primer i afegir les del $value
//                $item = $value;
//            }
//
//            if ($item['type'] === 'array') {
//                $item['value'] = $this->parseArray($item['itemsType'], $values[$key], $definitionTypes, $newPrefix);
//            } else if ($item['tipus'] === 'object') {
//                $item['value'] = $this->parseObject($item['keys'], $value, $definitionTypes, $newPrefix);
//            } else {
//                $item['value'] = $values[$key];
//            }
//
//            $item['id'] = $newPrefix;
//            $tree[$key] = $item;
//        }
//        return $tree;
//    }

}
