<?php
/**
 * Component: Project / MetaData
 * Purposes: Abstract class that must inherit all Repository
 * @author Miguel Àngel Lozano Márquez<mlozan54@ioc.cat>
 */
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');

require_once( DOKU_INC . 'inc/JSON.php' );
require_once (DOKU_PLUGIN . 'wikiiocmodel/metadata/MetaDataRepositoryInterface.php');
require_once (DOKU_PLUGIN . 'wikiiocmodel/metadata/MetaDataExceptions.php');
require_once (DOKU_PLUGIN . 'wikiiocmodel/metadata/MetaDataDaoFactory.php');
require_once (DOKU_PLUGIN . 'wikiiocmodel/metadata/MetaDataEntityFactory.php');
require_once (DOKU_PLUGIN . 'ajaxcommand/defkeys/ProjectKeys.php');

class MetaDataRepository implements MetaDataRepositoryInterface {
    const K_METADATASUBSET  = ProjectKeys::KEY_METADATA_SUBSET;
    const K_PROJECTTYPE     = ProjectKeys::KEY_PROJECT_TYPE;
    const K_IDRESOURCE      = ProjectKeys::KEY_ID_RESOURCE;
    const K_PERSISTENCE     = ProjectKeys::KEY_PERSISTENCE;

    /**
     * Call Dao to obtain metadata (only one element) and build an Entity that returns
     * @param Array $MetaDataRequestMessage
     * @return a MetaDataEntity object
     */
    public function getMeta($MetaDataRequestMessage) {
        //Check parameters mandatories
        $checkParameters =  isset($MetaDataRequestMessage[self::K_PERSISTENCE]) &&
                            isset($MetaDataRequestMessage[self::K_IDRESOURCE]) &&
                            $MetaDataRequestMessage[self::K_IDRESOURCE] != '' &&
                            isset($MetaDataRequestMessage[self::K_PROJECTTYPE]) &&
                            $MetaDataRequestMessage[self::K_PROJECTTYPE] != '' &&
                            isset($MetaDataRequestMessage[self::K_METADATASUBSET]) &&
                            $MetaDataRequestMessage[self::K_METADATASUBSET] != '';
        if (!$checkParameters) {
            throw new WrongParams();
        }

        try {
            $metaDataDao = MetaDataDaoFactory::getObject($MetaDataRequestMessage[self::K_PROJECTTYPE], $MetaDataRequestMessage[self::K_METADATASUBSET], $MetaDataRequestMessage[self::K_PERSISTENCE]);
            $jsonDataProject = $metaDataDao->getMeta($MetaDataRequestMessage);
            $metaDataEntity = MetaDataEntityFactory::getObject($MetaDataRequestMessage[self::K_PROJECTTYPE], $MetaDataRequestMessage[self::K_METADATASUBSET], $MetaDataRequestMessage[self::K_PERSISTENCE]);
            $metaDataEntity->setProjectType($MetaDataRequestMessage[self::K_PROJECTTYPE]);
            $metaDataEntity->setMetaDataSubSet($MetaDataRequestMessage[self::K_METADATASUBSET]);
            $metaDataEntity->setNsRoot($MetaDataRequestMessage[self::K_IDRESOURCE]);
            $metaDataEntity->setMetaDataValue($jsonDataProject);
            return $metaDataEntity;
        } catch (MetaDataNotFound $exnf) {
            $metaDataDao = MetaDataDaoFactory::getObject($MetaDataRequestMessage[self::K_PROJECTTYPE], $MetaDataRequestMessage[self::K_METADATASUBSET], $MetaDataRequestMessage[self::K_PERSISTENCE]);
            $metaDataEntity = MetaDataEntityFactory::getObject($MetaDataRequestMessage[self::K_PROJECTTYPE], $MetaDataRequestMessage[self::K_METADATASUBSET], $MetaDataRequestMessage[self::K_PERSISTENCE]);
            $metaDataEntity->setProjectType($MetaDataRequestMessage[self::K_PROJECTTYPE]);
            $metaDataEntity->setMetaDataSubSet($MetaDataRequestMessage[self::K_METADATASUBSET]);
            $metaDataEntity->setNsRoot($MetaDataRequestMessage[self::K_IDRESOURCE]);
            //$metaDataEntity->setMetaDataValue($jsonDataProject);
            return $metaDataEntity;
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Call DAO to updtate metadata (only one element)
     * @param Array $MetaDataRequestMessage
     * @return success:true
     */
    public function setMeta($MetaDataEntity, $MetaDataRequestMessage) {

        //Check parameters mandatories
        $checkParameters = false;
        if (isset($MetaDataRequestMessage[self::K_PERSISTENCE]) &&
            isset($MetaDataRequestMessage[self::K_IDRESOURCE]) &&
            $MetaDataRequestMessage[self::K_IDRESOURCE] != '' &&
            isset($MetaDataRequestMessage[self::K_PROJECTTYPE]) &&
            $MetaDataRequestMessage[self::K_PROJECTTYPE] != '' &&
            isset($MetaDataRequestMessage[self::K_METADATASUBSET]) &&
            $MetaDataRequestMessage[self::K_METADATASUBSET] != '' &&
            isset($MetaDataEntity)) {
                $metaDataValue = $MetaDataEntity->getMetaDataValue();
                $checkParameters = isset($metaDataValue);
        }
        if (!$checkParameters) {
            throw new WrongParams();
        }

        try {
            $metaDataDao = MetaDataDaoFactory::getObject($MetaDataRequestMessage[self::K_PROJECTTYPE], $MetaDataRequestMessage[self::K_METADATASUBSET], $MetaDataRequestMessage[self::K_PERSISTENCE]);
            $jSONArray = $metaDataDao->setMeta($MetaDataEntity, $MetaDataRequestMessage);
            return $jSONArray;
        } catch (Exception $ex) {
            throw $ex;
        }
    }

}
