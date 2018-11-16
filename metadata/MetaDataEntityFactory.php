<?php
/**
 * MetaDataEntityFactory
 * @author Miguel Àngel Lozano Márquez<mlozan54@ioc.cat>
 */
if (!defined('DOKU_INC')) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . "lib/plugins/");
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_PLUGIN . "wikiiocmodel/");

require_once (DOKU_INC . "inc/JSON.php");
require_once (WIKI_IOC_MODEL . "metadata/MetaDataExceptions.php");
require_once (WIKI_IOC_MODEL . "metadata/metadataconfig/MetaDataDaoConfig.php");

class MetaDataEntityFactory {
    /**
     * Call DaoConfig to obtain MetaDataEntity ns and return an object from this
     * @return MetaDataEntity object (from ns returned by MetaDataDaoConfig)
     */
    public static function getObject($projectType, $metaDataSubSet, $persistence) {

        $classesNameSpaces = MetaDataDaoConfig::getMetaDataConfig($projectType, $metaDataSubSet, $persistence);
        $encoder = new JSON();
        $objClassesNameSpaces = $encoder->decode($classesNameSpaces);
        if (!isset($objClassesNameSpaces->MetaDataEntity) || $objClassesNameSpaces->MetaDataEntity == NULL) {
            throw new ClassEntityNotFound();
        }
        $projectTypeDir = $persistence->createProjectMetaDataQuery(FALSE, $metaDataSubSet, $projectType)->getProjectTypeDir();        
        $dir = implode("/", explode("/", $projectTypeDir, -2)); 
        $metadataPath = "$dir/" . $objClassesNameSpaces->MetaDataEntity . "/metadata/MetaDataEntity.php";
        if (! file_exists($metadataPath))
            $metadataPath = WIKI_IOC_MODEL . "projects/" . $objClassesNameSpaces->MetaDataEntity . "/metadata/MetaDataEntity.php";
        require_once ($metadataPath);

        $fully_qualified_name = $objClassesNameSpaces->MetaDataEntity . "\\MetaDataEntity";

        return new $fully_qualified_name(MetaDataDaoConfig::getMetaDataStructure($projectType, $metaDataSubSet, $persistence),
                                         MetaDataDaoConfig::getMetaDataTypesDefinition($projectType, $metaDataSubSet, $persistence));
    }

}
