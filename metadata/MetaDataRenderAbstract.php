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

    /**
     * Purpose:
     * - Basis function to returns the same array of entities (converting it to JSON)
     * @param $metaDataEntityWrapper -> Entities array
     * Restrictions:
     * @return JSON (convert each Entity model to a JSON element)
     */
    public function render($metaDataEntityWrapper) {
        //print_r("\nMetaDataRender -> metaDataEntityWrapper0: " . $metaDataEntityWrapper[0]->getMetaDataValue());
        //print_r("\nMetaDataRender -> metaDataEntityWrapper1: " . $metaDataEntityWrapper[1]->getMetaDataValue());
        $toReturn = array();
        
        for ($i = 0; $i < sizeof($metaDataEntityWrapper); $i++) {
            $toReturn[$i]=$metaDataEntityWrapper[$i]->getArrayFromModel();            
        }
        //$encoder = new JSON();
        //return $encoder->encode($toReturn);
        return $toReturn;
    }

}
