<?php
/**
 * Component: Project / MetaData
 * Purposes: Abstract class that must inherit all Repository
 * @author Miguel Àngel Lozano Márquez<mlozan54@ioc.cat>
 */
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . "lib/plugins/");
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_PLUGIN . "wikiiocmodel/");

require_once( DOKU_INC . "inc/JSON.php");
require_once (WIKI_IOC_MODEL . "metadata/MetaDataRepositoryInterface.php");
require_once (WIKI_IOC_MODEL . "metadata/MetaDataExceptions.php");
require_once (WIKI_IOC_MODEL . "metadata/MetaDataDaoFactory.php");
require_once (WIKI_IOC_MODEL . "metadata/MetaDataEntityFactory.php");

class MetaDataRepository implements MetaDataRepositoryInterface {
    const K_METADATASUBSET  = ProjectKeys::KEY_METADATA_SUBSET;
    const K_PROJECTTYPE     = ProjectKeys::KEY_PROJECT_TYPE;
    const K_IDRESOURCE      = ProjectKeys::KEY_ID_RESOURCE;
    const K_PERSISTENCE     = ProjectKeys::KEY_PERSISTENCE;

    /**
     * Call Dao to obtain metadata (only one element) and build an Entity that returns
     * @param Array $MetaDataRequest
     * @return a MetaDataEntity object
     */
    public function getMeta($MetaDataRequest) {
        //Check parameters mandatories
        if (! ( isset($MetaDataRequest[self::K_PERSISTENCE]) &&
                isset($MetaDataRequest[self::K_IDRESOURCE]) &&
                $MetaDataRequest[self::K_IDRESOURCE] != '' &&
                isset($MetaDataRequest[self::K_PROJECTTYPE]) &&
                $MetaDataRequest[self::K_PROJECTTYPE] != '' &&
                isset($MetaDataRequest[self::K_METADATASUBSET]) &&
                $MetaDataRequest[self::K_METADATASUBSET] != '')) {
            throw new WrongParams();
        }

        $idResource     = $MetaDataRequest[self::K_IDRESOURCE];
        $projectType    = $MetaDataRequest[self::K_PROJECTTYPE];
        $metaDataSubSet = $MetaDataRequest[self::K_METADATASUBSET];
        $persistence    = $MetaDataRequest[self::K_PERSISTENCE];

        try {
            $metaDataDao = MetaDataDaoFactory::getObject($projectType, $metaDataSubSet, $persistence);

            //ATENCIÓN
            //Cuando todavía no hay datos en el fichero de proyecto, he hecho que se recoja la lista de campos del tipo de proyecto
            $jsonDataProject = $metaDataDao->getMeta($MetaDataRequest);
            //sin embargo, podría devolverse NULL (ahora está cotrolado por una excepción que no lo permite) y
            //utilizar en su lugar el valor JSON de $MetaDataRequest['metaDataValue']

            $metaDataEntity = MetaDataEntityFactory::getObject($projectType, $metaDataSubSet, $persistence);
            $metaDataEntity->setProjectType($projectType);
            $metaDataEntity->setMetaDataSubSet($metaDataSubSet);
            $metaDataEntity->setNsRoot($idResource);
            $metaDataEntity->setMetaDataValue($jsonDataProject);
            return $metaDataEntity;
        } catch (MetaDataNotFound $exnf) {
            $metaDataDao = MetaDataDaoFactory::getObject($projectType, $metaDataSubSet, $persistence);
            $metaDataEntity = MetaDataEntityFactory::getObject($projectType, $metaDataSubSet, $persistence);
            $metaDataEntity->setProjectType($projectType);
            $metaDataEntity->setMetaDataSubSet($metaDataSubSet);
            $metaDataEntity->setNsRoot($idResource);
            //$metaDataEntity->setMetaDataValue($jsonDataProject);
            return $metaDataEntity;
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Call DAO to updtate metadata (only one element)
     * @param Array $MetaDataRequest
     * @return success:true
     */
    public function setMeta($MetaDataEntity, $MetaDataRequest) {

        //Check parameters mandatories
        $checkParameters = false;
        if (isset($MetaDataRequest[self::K_PERSISTENCE]) &&
            isset($MetaDataRequest[self::K_IDRESOURCE]) &&
            $MetaDataRequest[self::K_IDRESOURCE] != '' &&
            isset($MetaDataRequest[self::K_PROJECTTYPE]) &&
            $MetaDataRequest[self::K_PROJECTTYPE] != '' &&
            isset($MetaDataRequest[self::K_METADATASUBSET]) &&
            $MetaDataRequest[self::K_METADATASUBSET] != '' &&
            isset($MetaDataEntity)) {
                $metaDataValue = $MetaDataEntity->getMetaDataValue();
                $checkParameters = isset($metaDataValue);
        }
        if (!$checkParameters) {
            throw new WrongParams();
        }

        $projectType    = $MetaDataRequest[self::K_PROJECTTYPE];
        $metaDataSubSet = $MetaDataRequest[self::K_METADATASUBSET];
        $persistence    = $MetaDataRequest[self::K_PERSISTENCE];

        try {
            $metaDataDao = MetaDataDaoFactory::getObject($projectType, $metaDataSubSet, $persistence);
            $jSONArray = $metaDataDao->setMeta($MetaDataEntity, $MetaDataRequest);
            return $jSONArray;
        } catch (Exception $ex) {
            throw $ex;
        }
    }

}
