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
require_once (DOKU_PLUGIN . 'wikiiocmodel/metadata/MetaDataExceptions.php');
require_once (DOKU_PLUGIN . 'ajaxcommand/defkeys/ProjectKeys.php');

abstract class MetaDataDaoAbstract implements MetaDataDaoInterface {
    const K_METADATASUBSET  = ProjectKeys::KEY_METADATA_SUBSET;
    const K_PROJECTTYPE     = ProjectKeys::KEY_PROJECT_TYPE;
    const K_IDRESOURCE      = ProjectKeys::KEY_ID_RESOURCE;
    const K_PERSISTENCE     = ProjectKeys::KEY_PERSISTENCE;
    const K_PROJECT_FILENAME= ProjectKeys::KEY_PROJECT_FILENAME;

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
        $checkParameters = isset($MetaDataRequestMessage[self::K_PERSISTENCE]) &&
                            isset($MetaDataRequestMessage[self::K_IDRESOURCE]) &&
                            $MetaDataRequestMessage[self::K_IDRESOURCE] != '' &&
                            isset($MetaDataRequestMessage[self::K_PROJECTTYPE]) &&
                            $MetaDataRequestMessage[self::K_PROJECTTYPE] != '' &&
                            isset($MetaDataRequestMessage[self::K_METADATASUBSET]) &&
                            $MetaDataRequestMessage[self::K_METADATASUBSET] != '';

        if (!$checkParameters) {
            throw new WrongParams();
        }

        $jSONArray = $this->__getMetaPersistence($MetaDataRequestMessage);

        //if doesn't exist metadata, then WikiIocModelException -> MetaDataNotFound
        if (!isset($jSONArray) || $jSONArray == null) {
            throw new MetaDataNotFound();
        }

        //Persistence returns wellformed JSON
        $encoder = new JSON();
        $arrayConfigPre = $encoder->decode($jSONArray);
        if (json_last_error() != JSON_ERROR_NONE) {
            throw new MalFormedJSON();
        }

        return $jSONArray;
    }

    /**
     * Purpose:
     * - Create object from class ProjectMetaDataQuery and call getMeta of this persistence query object
     * - It's possible personalize this method to especific (projectType, metaDataSubSet) MetaDataDao class
     * @param array
     * Restrictions:
     * -
     * @return json with metadata values
     */
    public function __getMetaPersistence($MetaDataRequestMessage) {
        return $MetaDataRequestMessage[self::K_PERSISTENCE]->createProjectMetaDataQuery()->getMeta($MetaDataRequestMessage[self::K_IDRESOURCE], $MetaDataRequestMessage[self::K_PROJECTTYPE], $MetaDataRequestMessage[self::K_METADATASUBSET],$MetaDataRequestMessage[self::K_PROJECT_FILENAME]);
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
            $encoder = new JSON();
            $arrayConfigPre = $encoder->decode($jSONArray);
            if (json_last_error() != JSON_ERROR_NONE) {
                throw new MalFormedJSON();
            }
            foreach ($arrayConfigPre as $value) {
                switch ($value) {
                    case "5120":
                        throw new PersistenceNsNotFound();
                        break;
                    case "5090":
                        throw new MetaDataNotUpdated();
                        break;
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
                    ->createProjectMetaDataQuery()
                    ->setMeta($MetaDataRequestMessage[self::K_IDRESOURCE],
                                $MetaDataRequestMessage[self::K_PROJECTTYPE],
                                $MetaDataRequestMessage[self::K_METADATASUBSET],
                                $MetaDataRequestMessage[self::K_PROJECT_FILENAME],
                                $MetaDataEntity->getMetaDataValue()
                            );
    }

}
