<?php
/**
 * Component: Project / MetaData
 * Status: @@Development
 * Purposes:
 * - Default / Class extending basis/MetaDataRender
 * @author Miguel Àngel Lozano Márquez<mlozan54@ioc.cat>
 */

/**
 * Description of MetaDataRender
 *
 * @author professor
 */
namespace testmat;
require_once(DOKU_PLUGIN . 'wikiiocmodel/metadata/MetaDataRenderAbstract.php');


class MetaDataRender extends \MetaDataRenderAbstract
{

    /**
     * Purpose:
     * - Basis function to returns the same array of entities (converting it to JSON)
     * @param $metaDataEntityWrapper -> Entities array
     * Restrictions:
     * @return JSON (convert each Entity model to a JSON element)
     */
    public function render($metaDataEntityWrapper)
    {

        // Alerta[Xavi] Per aquest tipus de projecte no es preveu la necessitat de fer servir un array

        $objAux = json_decode($metaDataEntityWrapper[0]->getArrayFromModel(), true);
        $structure = json_decode($objAux['metaDataStructure'], true);
        $types = json_decode($objAux['metaDataTypesDefinition'], true);
        $values = json_decode($objAux['MetaDataValue'], true);

        $returnTree = [];
        $returnTree['structure'] = $this->initParser($values, $structure, $types);;
        $returnTree['values'] = $this->flatten($returnTree['structure']);


        return $returnTree;
//        return $toReturn;
    }

    protected function flatten($values)
    {
        $flat = [];
        foreach ($values as $key => $value) {
            if (getType($value['value']) === 'array') { // Si es un array s'ha d'aplanar
                $newFlat = $this->flatten($value['value']);
                $flat = array_merge($flat, $newFlat);
            } else if ($value['value']) {
                // Es una fulla
//                $item[$key]['id'] = $value['id'];
//                $item[$key]['value'] = $value['value'];
                $flat[$value['id']] = $value['value'];
            }
        }

        return $flat;
    }

    protected function initParser($values, $structure, $definitionTypes)
    {
        $tree = [];


        // El primer nivell de l'estructura depén de l'estructura

        foreach ($values as $key => $value) {
            $prefix = $key;

            // Si $value es un array fem un parse (branca)
            if ($structure[$key]['tipus'] === 'array') {
                $tree[$key] = $structure[$key];
                $tree[$key]['value'] = $this->parseArray($structure[$key]['itemsType'], $value, $definitionTypes, $prefix);
            } else if ($value['tipus'] === 'object') {
                // TODO[Xavi]
                $tree[$key]['value'] = $this->parseObject($structure[$key]['itemsType'], $value, $definitionTypes, $prefix);
            } else {
                // Si no ho és ho afegim a la estructura (fulla)
                $tree[$key] = $structure[$key];
                $tree[$key]['value'] = $value;
            }

            $tree[$key]['id'] = $prefix;
        }


        return $tree;
    }

    protected function parseArray($type, $values, $definitionTypes, $prefix)
    {
        $tree = [];
        $item = null;

        for ($i = 0, $len = count($values); $i < $len; $i++) {
            $newPrefix = $prefix . '_' . $type . '_' . $i;

            if ($definitionTypes[$type]) {
                $item = $definitionTypes[$type];
                if ($item['tipus'] === 'array') {
                    $item['value'] = $this->parseArray($item['itemsType'], $values[$i], $definitionTypes, $newPrefix);

                } else if ($item['tipus'] === 'object') {
                    // TODO[Xavi]
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

    protected function parseObject($keys, $values, $definitionTypes, $prefix)
    {
        $tree = [];
        $item = null;

        foreach ($keys as $key => $value) {
            $newPrefix = $prefix . '_' . $key;
            // Es recorren les propietats

            $definition = $definitionTypes[$value['tipus']];

            if ($definition) {
                $item = $definition;
            } else {
                // ALERTA[Xavi] Considerar si això és el més correcte o s'han de copiar les propietats del $keys primer i afegir les del $value
                $item = $value;
            }

            // El valor si existeix s'obtè de $values
//            if ($values[$key]) {
//                $item['value'] = $values[$key]; // TODO: Si és un array o objecte s'ha de fer servir el parser, no assingar el valor directament
//            }


            if ($item['tipus'] === 'array') {
                $item['value'] = $this->parseArray($item['itemsType'], $values[$key], $definitionTypes, $newPrefix);
            } else if ($item['tipus'] === 'object') {
                // TODO[Xavi]
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
