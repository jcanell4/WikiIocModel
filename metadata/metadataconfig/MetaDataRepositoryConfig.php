<?php

/**
 * Component: Project / MetaData
 * Status: @@Development
 * Purposes:
 * - Class giving config utils
 * - Config utils are obtained from DaoConfig
 * @author Miguel Àngel Lozano Márquez<mlozan54@ioc.cat>
 */
if (!defined("DOKU_INC"))
    die();
if (!defined('DOKU_PLUGIN')) {
    define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
}

require_once (DOKU_PLUGIN . 'wikiiocmodel/metadata/MetaDataExceptions.php');
require_once (DOKU_PLUGIN . 'wikiiocmodel/metadata/metadataconfig/MetaDataDaoConfig.php');

class MetaDataRepositoryConfig {

    /**
     * Purpose:
     * - Call DaoConfig to obtain ns containing project metadata and his projectType (starting from a nsRoot given)
     * @param String ns
     * Restrictions:
     * - DaoConfig returns wellformed JSON  (throw exceptions by Dao) (all exceptions are delegated)
     * - mandatory $nsRoot
     * @return {ns:projectType,...,ns:projectType}
     */
    function getMetaDataElementsKey($nsRoot,$persistence) {
        try {
            $mdDaoConfig = new MetaDataDaoConfig();
            $jSONArray = $mdDaoConfig->getMetaDataElementsKey($nsRoot,$persistence);
        } catch (Exception $ex) {
            throw $ex;
        }
        return $jSONArray;
    }

}
