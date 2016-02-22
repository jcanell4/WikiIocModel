<?php

/**
 * Component: Project / MetaData
 * Status: @@Development
 * Purposes:
 * - Render objects supplier
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

class MetaDataRenderFactory {

    /**
     * Purpose:
     * - Call DaoConfig to obtain MetaDataRender ns and return an object from this
     * @param String projectType, String metaDataSubset
     * Restrictions:
     * - mandatory $projectType, $metaDataSubSet
     * - DaoConfig returns null, Exception ClassRenderNotFound 5080
     * @return MetaDataRender object (from ns returned by MetaDataDaoConfig)
     */
    public static function getObject($projectType, $metaDataSubSet,$persistence) {

        $jSONArray = MetaDataDaoConfig::getMetaDataConfig($projectType, $metaDataSubset,$persistence);
        $encoder = new JSON();
        $arrayConfigPre = $encoder->decode($jSONArray, true);
        if (!isset($arrayConfigPre->MetaDataRender) || $arrayConfigPre->MetaDataRender == '' || $arrayConfigPre->MetaDataRender == null) {
            throw new ClassRenderNotFound();
        }
        require_once (DOKU_PLUGIN . 'wikiiocmodel/metadata/classes/' . $arrayConfigPre->MetaDataRender . '/MetaDataRender.php');
        $fully_qualified_name = "ns" . $arrayConfigPre->MetaDataRender . '\\' . "MetaDataRender";

        return new $fully_qualified_name();
        //return new MetaDataRender();
    }

}
