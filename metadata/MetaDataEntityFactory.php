<?php
/**
 * MetaDataEntityFactory
 * @author Miguel Àngel Lozano Márquez<mlozan54@ioc.cat>
 */
if (!defined('DOKU_INC')) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . "lib/plugins/");
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_PLUGIN . "wikiiocmodel/");
require_once (WIKI_IOC_MODEL . "metadata/MetaDataExceptions.php");

class MetaDataEntityFactory {
    /**
     * obtain MetaDataEntity ns and return an object from this
     * @return MetaDataEntity object (from ns returned by MetaDataDaoConfig)
     */
    public static function getObject($projectType, $metaDataSubSet, $persistence) {
        $projectMetaDataQuery = $persistence->createProjectMetaDataQuery(FALSE, $metaDataSubSet, $projectType);
        $classesNameSpaces = $projectMetaDataQuery->getMetaDataConfig(ProjectKeys::KEY_METADATA_CLASSES_NAMESPACES);
        $objClassesNameSpaces = json_decode($classesNameSpaces)->$metaDataSubSet;
        if (!isset($objClassesNameSpaces->MetaDataEntity) || $objClassesNameSpaces->MetaDataEntity == NULL) {
            throw new ClassEntityNotFound();
        }
        $nameMetaDataEntity = $objClassesNameSpaces->MetaDataEntity;
        $projectTypeDir = $projectMetaDataQuery->getProjectTypeDir();
        $dir = implode("/", explode("/", $projectTypeDir, -2));
        $metadataPath = "$dir/$nameMetaDataEntity/metadata/MetaDataEntity.php";
        if (! file_exists($metadataPath))
            $metadataPath = WIKI_IOC_MODEL . "projects/$nameMetaDataEntity/metadata/MetaDataEntity.php";
        require_once ($metadataPath);

        $fully_qualified_name = "$nameMetaDataEntity\\MetaDataEntity";

        return new $fully_qualified_name(MetaDataDaoConfig::getMetaDataStructure($projectType, $metaDataSubSet, $persistence),
                                         MetaDataDaoConfig::getMetaDataTypesDefinition($projectType, $metaDataSubSet, $persistence));
    }

}
