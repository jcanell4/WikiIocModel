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
namespace defaultProject;
require_once (DOKU_PLUGIN . 'wikiiocmodel/metadata/MetaDataRenderAbstract.php');


class MetaDataRender extends \MetaDataRenderAbstract{

    /**
     * Purpose:
     * - Basis function to returns the same array of entities (converting it to JSON)
     * @param $metaDataEntityWrapper -> Entities array
     * Restrictions:
     * @return JSON (convert each Entity model to a JSON element)
     */
    public function render($metaDataEntityWrapper) {
        $toReturn = array();

        for ($i = 0; $i < sizeof($metaDataEntityWrapper); $i++) {
            $toReturn[$i]=$metaDataEntityWrapper[$i]->getArrayFromModel();
        }

        return $toReturn;
    }
}
