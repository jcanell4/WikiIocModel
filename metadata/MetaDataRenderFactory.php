<?php
/**
 * Component: Project / MetaData
 * Status: @@Development
 * Purposes:
 * - Render objects supplier
 * @author Miguel Àngel Lozano Márquez<mlozan54@ioc.cat>
 */
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . "lib/plugins/");
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_PLUGIN . "wikiiocmodel/");

require_once( DOKU_INC . "inc/JSON.php");
require_once (WIKI_IOC_MODEL . "metadata/MetaDataExceptions.php");
require_once (WIKI_IOC_MODEL . "metadata/metadataconfig/MetaDataDaoConfig.php");

class MetaDataRenderFactory {
    /**
     * Purpose:
     * - Call DaoConfig to obtain MetaDataRender ns and return an object from this
     * Restrictions:
     * - mandatory all parameters
     * - DaoConfig returns null, Exception ClassRenderNotFound 5080
     * @return MetaDataRender object (from ns returned by MetaDataDaoConfig)
     */
    public static function getObject($projectType, $metaDataSubSet, $persistence, $projectTypeDir) {

        $jSONArray = MetaDataDaoConfig::getMetaDataConfig($projectType, $metaDataSubSet, $persistence, $projectTypeDir);
        $encoder = new JSON();
        $arrayConfigPre = $encoder->decode($jSONArray, true);
        if (!isset($arrayConfigPre->MetaDataRender) || $arrayConfigPre->MetaDataRender == '' || $arrayConfigPre->MetaDataRender == null) {
            throw new ClassRenderNotFound();
        }
        $dir = implode("/", explode("/", $projectTypeDir, -2));
        $metadataPath = "$dir/" . $arrayConfigPre->MetaDataRender . "/metadata/MetaDataRender.php";
        if (! file_exists($metadataPath))
            $metadataPath = WIKI_IOC_MODEL . "projects/" . $arrayConfigPre->MetaDataRender . "/metadata/MetaDataRender.php";
        require_once ($metadataPath);

        $fully_qualified_name = $arrayConfigPre->MetaDataRender . '\\' . "MetaDataRender";
        return new $fully_qualified_name();
    }

}
