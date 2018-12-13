<?php
/**
 * Component: Project / MetaData
 * Purposes:
 * - Render objects supplier
 * @author Miguel Àngel Lozano Márquez<mlozan54@ioc.cat>
 */
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . "lib/plugins/");
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_PLUGIN . "wikiiocmodel/");
require_once (WIKI_IOC_MODEL . "metadata/MetaDataExceptions.php");

class MetaDataRenderFactory {
    /**
     * Purpose:
     * - obtain MetaDataRender ns and return an object from this
     * Restrictions:
     * - mandatory all parameters
     * @return MetaDataRender object (from ns returned by MetaDataDaoConfig)
     */
    public static function getObject($projectType, $metaDataSubSet, $persistence) {
        $projectMetaDataQuery = $persistence->createProjectMetaDataQuery(FALSE, $metaDataSubSet, $projectType);
        $classesNameSpaces = $projectMetaDataQuery->getMetaDataConfig(ProjectKeys::KEY_METADATA_CLASSES_NAMESPACES);
        $objClassesNameSpaces = json_decode($classesNameSpaces)->$metaDataSubSet;
        if (!isset($objClassesNameSpaces->MetaDataRender) || $objClassesNameSpaces->MetaDataRender == NULL) {
            throw new ClassRenderNotFound();
        }
        $nameMetaDataRender = $objClassesNameSpaces->MetaDataRender;
        $projectTypeDir = $projectMetaDataQuery->getProjectTypeDir();
        $dir = implode("/", explode("/", $projectTypeDir, -2));
        $metadataPath = "$dir/$nameMetaDataRender/metadata/MetaDataRender.php";
        if (! file_exists($metadataPath))
            $metadataPath = WIKI_IOC_MODEL . "projects/$nameMetaDataRender/metadata/MetaDataRender.php";
        require_once ($metadataPath);

        $fully_qualified_name = "$nameMetaDataRender\\MetaDataRender";
        return new $fully_qualified_name();
    }

}
