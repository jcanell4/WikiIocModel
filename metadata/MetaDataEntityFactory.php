<?php
/**
 * MetaDataEntityFactory
 * @author Miguel Àngel Lozano Márquez<mlozan54@ioc.cat>
 */
if (!defined('DOKU_INC')) die();
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_INC . "lib/plugins/wikiiocmodel/");

require_once (DOKU_INC . "inc/JSON.php");
require_once (WIKI_IOC_MODEL . "metadata/MetaDataExceptions.php");
require_once (WIKI_IOC_MODEL . "metadata/metadataconfig/MetaDataDaoConfig.php");

class MetaDataEntityFactory {

    /**
     * Call DaoConfig to obtain MetaDataEntity ns and return an object from this
     * @param string $projectType, $metaDataSubset
     * @return MetaDataEntity object (from ns returned by MetaDataDaoConfig)
     */
    public static function getObject($projectType, $metaDataSubSet, $persistence) {

        $classesNameSpaces = MetaDataDaoConfig::getMetaDataConfig($projectType, $metaDataSubSet, $persistence);
        $encoder = new JSON();
        $objClassesNameSpaces = $encoder->decode($classesNameSpaces);
        if (!isset($objClassesNameSpaces->MetaDataEntity) || $objClassesNameSpaces->MetaDataEntity == NULL) {
            throw new ClassEntityNotFound();
        }
        require_once (WIKI_IOC_MODEL . "projects/" . $objClassesNameSpaces->MetaDataEntity . "/metadata/MetaDataEntity.php");
        $fully_qualified_name = $objClassesNameSpaces->MetaDataEntity . "\\MetaDataEntity";

        return new $fully_qualified_name(MetaDataDaoConfig::getMetaDataStructure($projectType, $metaDataSubSet, $persistence),
                                         MetaDataDaoConfig::getMetaDataTypesDefinition($projectType, $metaDataSubSet, $persistence));
    }

}
