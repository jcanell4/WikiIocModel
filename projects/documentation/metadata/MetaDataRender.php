<?php
/**
 * Component: Project / MetaData
 * @author Miguel Àngel Lozano Márquez<mlozan54@ioc.cat>
 */
namespace documentation;
require_once(DOKU_PLUGIN . 'wikiiocmodel/metadata/MetaDataRenderAbstract.php');

class MetaDataRender extends \MetaDataRenderAbstract {

    /**
     * @param $metaDataEntityWrapper -> Entities array
     */
    public function render($metaDataEntityWrapper) {
        $objAux = json_decode($metaDataEntityWrapper[0]->getArrayFromModel(), true);
        $structure = json_decode($objAux['metaDataStructure'], true);
        $types = json_decode($objAux['metaDataTypesDefinition'], true);
        $values = json_decode($objAux['metaDataValue'], true);

        $returnTree = [];
        $returnTree['structure'] = $this->initParser($values, $structure, $types);
        $returnTree['values'] = $this->flatten($returnTree['structure']);

        return $returnTree;
    }

    protected function flatten($values) {
        $flat = [];
        foreach ($values as $key => $item) {
            if (is_array($item['value'])) {
                //si es un array s'ha d'aplanar
                //$flat[$item['id']] = ($item['value']==NULL) ? "" : $this->flatten($item['value']);
                //$newFlat = ($item['value']==NULL) ? array($item['id']=>"") : $this->flatten($item['value']);
                $newFlat = $this->flatten($item['value']);
                $flat = array_merge($flat, $newFlat);
            }else if (isset($item['value']) && $item['id']) {
                //és una fulla
                $flat[$item['id']] = $item['value'];
            }else if (is_array($item)) {
                //$k = key($item);
                //$flat[$key] = ($item==NULL) ? "" : $this->flatten($item);
                $new2Flat = ($item==NULL) ? "" : $this->flatten($item);
                $flat = array_merge($flat, $new2Flat);
            }else if (gettype($item) === "string") {
                //és una fulla
                $flat[$key] = $item;
            }
        }
        return $flat;
    }

    protected function initParser($values, $structure, $types) {
        $values = $this->defaultFillParser($values, $structure, $types);
        $tree = $this->parser($values, $structure, $types);
        return $tree;
    }

    protected function defaultFillParser($values, $structure, $types) {
        //Añade al array de campos de valores los campos de la estructura que le falten
        foreach ($structure as $k => $v) {
            if (!isset($values[$k])) {
                if ($v['type']==='string') {
                    $values[$k] = '';
                }
                else if ($v['type']==='array') {
                    $values[$k] = array();
                }
                else {
                    $values[$k] = $this->defaultFillParser(array(), $types[$k]['keys'], $types);
                }
            }
        }
        return $values;
    }

    protected function parser($values, $structure, $definitionTypes) {
        $tree = [];

        // El primer nivell de l'estructura depén de l'estructura
        foreach ($values as $key => $value) {
            $prefix = $key;

            // Si $value es un array fem un parse (branca)
            if ($structure[$key]['type'] === 'array') {
                $tree[$key] = $structure[$key];
                $tree[$key]['value'] = $this->parseArray($structure[$key]['itemsType'], $value, $definitionTypes, $prefix);
            } else if ($structure[$key]['type'] === 'object') {
                $tree[$key] = $structure[$key];
                $tree[$key]['value'] = $this->parseObject($structure[$key]['typeDef'], $value, $definitionTypes, $prefix);
            } else {
                // Si no ho és ho afegim a la estructura (fulla)
                $tree[$key] = $structure[$key];
                $tree[$key]['value'] = $value;
            }
            $tree[$key]['id'] = $prefix;
        }
        return $tree;
    }

    protected function parseArray($type, $values, $definitionTypes, $prefix) {
        $tree = [];
        $item = null;

        for ($i = 0, $len = count($values); $i < $len; $i++) {
//            $newPrefix = $prefix . "#" . $type . "#" . $i;
            $newPrefix = $prefix . "#" . $i;

            if ($definitionTypes[$type]) {
                $item = $definitionTypes[$type];
                if ($item['type'] === 'array') {
                    $item['value'] = $this->parseArray($item['itemsType'], $values[$i], $definitionTypes, $newPrefix);

                } else if ($item['type'] === 'object') {
                    $item['value'] = $this->parseObject($item['keys'], $values[$i], $definitionTypes, $newPrefix);
                    // Ja s'han fusionat les keys i els valors, no cal passar les keys
                    unset($item['keys']);
                } else {
                    $item['value'] = $values[$i];
                }

            } else {
                // TODO[Xavi] Si no s'ha especificat el tipus que fem?
            }

            $item['id'] = $newPrefix;
            $tree[] = $item;
        }

        return $tree;
    }

    protected function parseObject($type, $values, $definitionTypes, $prefix) {
        $tree = [];
        $item = null;
        $keys = $definitionTypes[$type]['keys'];

        foreach ($keys as $key => $value) {
            $newPrefix = $prefix . "#" . $key;
            $definition = $definitionTypes[$value['tipus']];

            if ($definition) {
                $item = $definition;
            } else {
                // ALERTA[Xavi] Considerar si això és el més correcte o s'han de copiar les propietats del $keys primer i afegir les del $value
                $item = $value;
            }

            if ($item['type'] === 'array') {
                $item['value'] = $this->parseArray($item['itemsType'], $values[$key], $definitionTypes, $newPrefix);
            } else if ($item['tipus'] === 'object') {
                $item['value'] = $this->parseObject($item['keys'], $value, $definitionTypes, $newPrefix);
            } else {
                $item['value'] = $values[$key];
            }

            $item['id'] = $newPrefix;
            $tree[$key] = $item;
        }
        return $tree;
    }

}
