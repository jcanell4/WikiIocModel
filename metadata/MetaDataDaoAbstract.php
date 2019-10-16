<?php
/**
 * Component: Project / MetaData
 * Purposes: Abstract class that must inherit all Dao
 * @author Miguel Àngel Lozano Márquez<mlozan54@ioc.cat>
 */
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');

require_once (DOKU_INC . 'inc/JSON.php');
require_once (DOKU_PLUGIN . 'wikiiocmodel/metadata/MetaDataDaoInterface.php');
require_once (DOKU_PLUGIN . 'wikiiocmodel/metadata/metadataconfig/MetaDataDaoConfig.php');
require_once (DOKU_PLUGIN . 'wikiiocmodel/metadata/MetaDataExceptions.php');
require_once (DOKU_PLUGIN . 'ajaxcommand/defkeys/ProjectKeys.php');

abstract class MetaDataDaoAbstract implements MetaDataDaoInterface {
    const K_METADATASUBSET  = ProjectKeys::KEY_METADATA_SUBSET;
    const K_PROJECTTYPE     = ProjectKeys::KEY_PROJECT_TYPE;
    const K_IDRESOURCE      = ProjectKeys::KEY_ID_RESOURCE;
    const K_PERSISTENCE     = ProjectKeys::KEY_PERSISTENCE;
    const K_REVISION        = ProjectKeys::KEY_REV;

    /**
     * Purpose: Call PERSISTENCE component to obtain metadata (only one element)
     * @param Array $MetaDataRequestMessage
     * Restrictions:
     * - Persistence returns wellformed JSON
     * - mandatory idResource,projectType,metaDataSubSet in param array $MetaDataRequestMessage
     * - if doesn't exist metadata, then WikiIocModelException -> MetaDataNotFound
     * @return JSON {keymd1:valormd1,...,keymdx:valormdx}
     */
    public function getMeta($MetaDataRequestMessage) {
        //Check parameters mandatories
        if ( ! (isset($MetaDataRequestMessage[self::K_PERSISTENCE]) &&
                isset($MetaDataRequestMessage[self::K_IDRESOURCE]) &&
                $MetaDataRequestMessage[self::K_IDRESOURCE] != '' &&
                isset($MetaDataRequestMessage[self::K_PROJECTTYPE]) &&
                $MetaDataRequestMessage[self::K_PROJECTTYPE] != '' &&
                isset($MetaDataRequestMessage[self::K_METADATASUBSET]) &&
                $MetaDataRequestMessage[self::K_METADATASUBSET] != '')) {
            throw new WrongParams();
        }

        $jsonObj = $this->__getMetaPersistence($MetaDataRequestMessage);
        if (!isset($jsonObj) || $jsonObj == NULL) {
            // Se usa cuando todavía no hay datos en el fichero de proyecto, entonces se recoge la lista de campos del tipo de proyecto
            // aunque, tal vez, habría que retornar NULL (eliminando la Excepción)
            $jsonObj = $this->__getStructureMetaDataConfig($MetaDataRequestMessage);
        }
        if (!isset($jsonObj) || $jsonObj == NULL) {
            throw new MetaDataNotFound();
        }

        //Persistence returns wellformed JSON
        MetaDataDaoConfig::controlMalFormedJson($jsonObj);

        return $jsonObj;
    }

    /**
     * Purpose:
     * - Create object from class ProjectMetaDataQuery and call getMeta of this persistence query object
     * - It's possible personalize this method to especific (projectType, metaDataSubSet) MetaDataDao class
     * @param array
     * @return json with metadata values
     */
    public function __getMetaPersistence($MetaDataRequestMessage) {
        return $MetaDataRequestMessage[self::K_PERSISTENCE]
                    ->createProjectMetaDataQuery(
                            $MetaDataRequestMessage[self::K_IDRESOURCE],
                            $MetaDataRequestMessage[self::K_METADATASUBSET],
                            $MetaDataRequestMessage[self::K_PROJECTTYPE],
                            $MetaDataRequestMessage[self::K_REVISION]
                        )
                    ->getMeta($MetaDataRequestMessage[self::K_METADATASUBSET], $MetaDataRequestMessage[self::K_REVISION]);
    }

    /**
     * Obtiene la estructura de campos de la definición del tipo de proyecto
     * Se usa cuando todavía no hay datos en el fichero de proyecto, entonces se recoge la lista de campos del tipo de proyecto
     * @param array $MetaDataRequestMessage
     * @return JSON
     */
    public function __getStructureMetaDataConfig($MetaDataRequestMessage) {
        return $MetaDataRequestMessage[self::K_PERSISTENCE]
                    ->createProjectMetaDataQuery(FALSE,
                                                $MetaDataRequestMessage[self::K_METADATASUBSET],
                                                $MetaDataRequestMessage[self::K_PROJECTTYPE])
                    ->getStructureMetaDataConfig();
    }

    /**
     * Purpose:
     * - Call PERSISTENCE component to updtate metadata (only one element)
     * @param Array $MetaDataRequestMessage
     * Restrictions:
     * - mandatory idResource,projectType,metaDataSubSet in param array $MetaDataRequestMessage
     * - mandatory: MetaDataEntity->metaDataValue
     * - MetaDataEntity->metaDataValue wellformed JSON --> this restriction is managed by MetaDataEntityAbstract
     * - if persistence return not true, then wellformed JSON
     * - if persistence returns {"error","5120"}, then WikiIocModelException->PersistenceNsNotFound
     * - if persistence returns {"error","5090"}, then WikiIocModelException->MetaDataNotUpdated
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
                if (isset($metaDataValue)) {
                    $checkParameters = true;
                }
        }
        if (!$checkParameters) {
            throw new WrongParams();
        }

        /*
         * Per a la persistència
         * Crida [PERSISTENCE].setMeta(idResource,projectType,metaDataSubSet,metaDataValue)
                    paràmetres String i metaDataValue JSON {keymd1:valormd1,...,keymdx:valormdx}
          Restriccions
          Si idResource no existeix, llavors el component de Persistència haurà de retornar {"error":"5120"}
          Qualsevol error, la persistència retornarà {"error":"5090"}
         */
        $jSONArray = $this->__setMetaPersistence($MetaDataEntity, $MetaDataRequestMessage);

        $returnType = gettype($jSONArray);
        if ($returnType == "boolean" && $jSONArray == true) {
            return $jSONArray;
        } else {
            //Persistence returns wellformed JSON
            $arrayConfigPre = MetaDataDaoConfig::controlMalFormedJson($jSONArray);
            foreach ($arrayConfigPre as $value) {
                switch ($value) {
                    case "5120":
                        throw new PersistenceNsNotFound();
                    case "5090":
                        throw new MetaDataNotUpdated();
                    default :
                        throw new MalFormedJSON();
                }
            }
        }
    }

    /**
     * Purpose:
     * - Create object from class ProjectMetaDataQuery and call setMeta of this persistence query object
     * - It's possible personalize this method to especific (projectType, metaDataSubSet) MetaDataDao class
     * @param array
     * @return success -> true, error --> json with error value
     */
    public function __setMetaPersistence($MetaDataEntity, $MetaDataRequestMessage) {
        return $MetaDataRequestMessage[self::K_PERSISTENCE]
                    ->createProjectMetaDataQuery($MetaDataRequestMessage[self::K_IDRESOURCE],
                                $MetaDataRequestMessage[self::K_METADATASUBSET],
                                $MetaDataRequestMessage[self::K_PROJECTTYPE])
                    ->setMeta($MetaDataEntity->getMetaDataValue());
    }

}
