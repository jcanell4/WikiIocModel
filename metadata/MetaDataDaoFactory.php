<?php

/**
 * Component: Project / MetaData
 * Status: @@Development
 * Purposes:
 * - Dao objects supplier
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

class MetaDataDaoFactory {

    /**
     * Purpose:
     * - Call DaoConfig to obtain MetaDataDAO ns and return an object from this
     * @param String projectType, String metaDataSubset
     * Restrictions:
     * - mandatory $projectType, $metaDataSubSet
     * - DaoConfig returns null, Exception ClassDaoNotFound 5060
     * @return MetaDataDao object (from ns returned by MetaDataDaoConfig)
     */
    public static function getObject($projectType, $metaDataSubSet) {

        $jSONArray = MetaDataDaoConfig::getMetaDataConfig($projectType, $metaDataSubset);
        $encoder = new JSON();
        $arrayConfigPre = $encoder->decode($jSONArray,true);
        if (!isset($arrayConfigPre->MetaDataDAO) || $arrayConfigPre->MetaDataDAO == '' || $arrayConfigPre->MetaDataDAO == null) {
            throw new ClassDaoNotFound();
        }
        require_once (DOKU_PLUGIN . 'wikiiocmodel/metadata/classes/' . $arrayConfigPre->MetaDataDAO . '/MetaDataDao.php');
        return new MetaDataDao();

    }


}
