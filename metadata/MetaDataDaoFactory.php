<?php
/**
 * Component: Project / MetaData
 * Purposes:
 * - Dao objects supplier
 * @author Miguel Àngel Lozano Márquez<mlozan54@ioc.cat>
 */
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . "lib/plugins/");
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_PLUGIN . "wikiiocmodel/");
require_once (WIKI_IOC_MODEL . "metadata/MetaDataExceptions.php");

class MetaDataDaoFactory {
    /**
     * Purpose:
     * - obtain MetaDataDAO ns and return an object from this
     * Restrictions:
     * - mandatory all parameters
     * @return MetaDataDao object (from ns returned by MetaDataDaoConfig)
     */
    public static function getObject($projectType, $metaDataSubSet, $persistence) {
        $projectMetaDataQuery = $persistence->createProjectMetaDataQuery(FALSE, $metaDataSubSet, $projectType);
        $classesNameSpaces = $projectMetaDataQuery->getMetaDataConfig(ProjectKeys::KEY_METADATA_CLASSES_NAMESPACES);
        $objClassesNameSpaces = json_decode($classesNameSpaces)->$metaDataSubSet;
        if (!isset($objClassesNameSpaces->MetaDataDAO) || $objClassesNameSpaces->MetaDataDAO == NULL) {
            throw new ClassDaoNotFound();
        }
        $nameMetaDataDAO = $objClassesNameSpaces->MetaDataDAO;
        $projectTypeDir = $persistence->createProjectMetaDataQuery()->getProjectTypeDir($projectType);
        $dir = implode("/", explode("/", $projectTypeDir, -2));
        $metadataPath = "$dir/$nameMetaDataDAO/metadata/MetaDataDao.php";
        if (! file_exists($metadataPath))
            $metadataPath = WIKI_IOC_MODEL . "projects/$nameMetaDataDAO/metadata/MetaDataDao.php";
        require_once ($metadataPath);

        $fully_qualified_name =  "$nameMetaDataDAO\\MetaDataDao";
        return new $fully_qualified_name();
    }

}
