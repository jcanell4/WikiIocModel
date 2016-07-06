<?php

/**
 * Component: Project / MetaData
 * Status: @@Development
 * Purposes:
 * - Entity objects supplier
 * @author Miguel Àngel Lozano Márquez<mlozan54@ioc.cat>
 */
if (!defined("DOKU_INC"))
    die();
if (!defined('DOKU_PLUGIN')) {
    define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
}

require_once( DOKU_INC . 'inc/JSON.php' );
require_once (DOKU_PLUGIN . 'wikiiocmodel/metadata/MetaDataExceptions.php');
require_once (DOKU_PLUGIN . 'wikiiocmodel/metadata/metadataconfig/MetaDataDaoConfig.php');

class MetaDataEntityFactory {

    /**
     * Purpose:
     * - Call DaoConfig to obtain MetaDataEntity ns and return an object from this
     * @param String projectType, String metaDataSubset
     * Restrictions:
     * - mandatory $projectType, $metaDataSubSet
     * - DaoConfig returns null, Exception ClassEntityNotFound 5080
     * @return MetaDataEntity object (from ns returned by MetaDataDaoConfig)
     */
    public static function getObject($projectType, $metaDataSubSet,$persistence) {

        $jSONArray = MetaDataDaoConfig::getMetaDataConfig($projectType, $metaDataSubSet,$persistence);
        $encoder = new JSON();
        $arrayConfigPre = $encoder->decode($jSONArray, true);
        if (!isset($arrayConfigPre->MetaDataEntity) || $arrayConfigPre->MetaDataEntity == '' || $arrayConfigPre->MetaDataEntity == null) {
            throw new ClassEntityNotFound();
        }
        require_once (DOKU_PLUGIN . 'wikiiocmodel/projects/' . $arrayConfigPre->MetaDataEntity . '/metadata/MetaDataEntity.php');
        $fully_qualified_name = $arrayConfigPre->MetaDataEntity . '\\' . "MetaDataEntity";
        //getMetaDataStructure($projectType, $metaDataSubset, $persistence, $configSubSet = null)
        return new $fully_qualified_name(MetaDataDaoConfig::getMetaDataStructure($projectType, $metaDataSubSet, $persistence));
        //return new MetaDataEntity();
    }

}
