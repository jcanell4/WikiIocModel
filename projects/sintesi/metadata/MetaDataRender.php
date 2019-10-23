<?php
/**
 * Component: Project / MetaData
 */
namespace sintesi;

require_once(DOKU_PLUGIN . "wikiiocmodel/metadata/MetaDataRenderAbstract.php");

class MetaDataRender extends \MetaDataRenderAbstract {

    protected function processValues($values){
        return $values;
    }
}
