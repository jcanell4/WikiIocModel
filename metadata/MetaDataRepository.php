<?php

/**
 * Component: Project / MetaData
 * Status: @@Development
 * Purposes:
 * - Abstract class that must inherit all Repository
 * @author Miguel Àngel Lozano Márquez<mlozan54@ioc.cat>
 */
if (!defined("DOKU_INC"))
    die();
if (!defined('DOKU_PLUGIN')) {
    define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
}

require_once( DOKU_INC . 'inc/JSON.php' );
require_once (DOKU_PLUGIN . 'wikiiocmodel/metadata/MetaDataRepositoryInterface.php');
require_once (DOKU_PLUGIN . 'wikiiocmodel/metadata/MetaDataExceptions.php');
require_once (DOKU_PLUGIN . 'wikiiocmodel/metadata/MetaDataDaoFactory.php');
require_once (DOKU_PLUGIN . 'wikiiocmodel/metadata/MetaDataEntityFactory.php');

class MetaDataRepository implements MetaDataRepositoryInterface {

    /**
     * Purpose:
     * - Call Dao to obtain metadata (only one element) and build an Entity that returns
     * @param Array $MetaDataRequestMessage
     * Restrictions:
     * - mandatory idResource,projectType,metaDataSubSet in param array $MetaDataRequestMessage
     * - other exceptions are delegate
     * @return a MetaDataEntity object
     */
    public function getMeta($MetaDataRequestMessage) {
        //Check parameters mandatories
        $checkParameters = false;
        if (isset($MetaDataRequestMessage['idResource'])) {
            if ($MetaDataRequestMessage['idResource'] != '') {
                if (isset($MetaDataRequestMessage['projectType'])) {
                    if ($MetaDataRequestMessage['projectType'] != '') {
                        if (isset($MetaDataRequestMessage['metaDataSubSet'])) {
                            if ($MetaDataRequestMessage['metaDataSubSet'] != '') {
                                $checkParameters = true;
                            }
                        }
                    }
                }
            }
        }
        if (!$checkParameters) {
            throw new WrongParams();
        }

        try {
            $metaDataDao = MetaDataDaoFactory::getObject($MetaDataRequestMessage['projectType'], $MetaDataRequestMessage['metaDataSubSet']);
            $jSONArray = $metaDataDao->getMeta($MetaDataRequestMessage);
            $metaDataEntity = MetaDataEntityFactory::getObject($MetaDataRequestMessage['projectType'], $MetaDataRequestMessage['metaDataSubSet']);
            $metaDataEntity->setProjectType($MetaDataRequestMessage['projectType']);
            $metaDataEntity->setmetaDataSubSet($MetaDataRequestMessage['metaDataSubSet']);
            $metaDataEntity->setNsRoot($MetaDataRequestMessage['idResource']);
            $metaDataEntity->setMetaDataValue($jSONArray);
            return $metaDataEntity;
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Purpose:
     * - Call DAO to updtate metadata (only one element)
     * @param Array $MetaDataRequestMessage
     * Restrictions:     
     * - mandatory idResource,projectType,metaDataSubSet in param array $MetaDataRequestMessage
     * - mandatory: MetaDataEntity->MetaDataValue
     * - other exceptions are delegate
     * @return success:true
     */
    public function setMeta($MetaDataEntity, $MetaDataRequestMessage) {

        //Check parameters mandatories
        $checkParameters = false;
        if (isset($MetaDataRequestMessage['idResource'])) {
            if ($MetaDataRequestMessage['idResource'] != '') {
                if (isset($MetaDataRequestMessage['projectType'])) {
                    if ($MetaDataRequestMessage['projectType'] != '') {
                        if (isset($MetaDataRequestMessage['metaDataSubSet'])) {
                            if ($MetaDataRequestMessage['metaDataSubSet'] != '') {
                                if (isset($MetaDataEntity)) {
                                    $metaDataValue = $MetaDataEntity->getMetaDataValue();
                                    if (isset($metaDataValue)) {
                                        $checkParameters = true;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        if (!$checkParameters) {
            throw new WrongParams();
        }

        try {
            //print_r("\nRA setMeta projectType: ".$MetaDataRequestMessage['projectType']."\n");
            //print_r("\nRA setMeta metaDataSubSet: ".$MetaDataRequestMessage['metaDataSubSet']."\n");
            //print_r("\nRA setMeta MetaDataEntity: ".$MetaDataEntity->getMetaDataValue()."\n");
            $metaDataDao = MetaDataDaoFactory::getObject($MetaDataRequestMessage['projectType'], $MetaDataRequestMessage['metaDataSubSet']);
            $jSONArray = $metaDataDao->setMeta($MetaDataEntity, $MetaDataRequestMessage);
            return $jSONArray;
        } catch (Exception $ex) {
            throw $ex;
        }
    }

}
