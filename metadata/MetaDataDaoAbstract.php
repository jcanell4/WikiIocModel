<?php

/**
 * Component: Project / MetaData
 * Status: @@Development
 * Purposes:
 * - Abstract class that must inherit all Dao
 * @author Miguel Àngel Lozano Márquez<mlozan54@ioc.cat>
 */
if (!defined("DOKU_INC"))
    die();
if (!defined('DOKU_PLUGIN')) {
    define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
}

require_once( DOKU_INC . 'inc/JSON.php' );
require_once (DOKU_PLUGIN . 'wikiiocmodel/metadata/MetaDataDaoInterface.php');
require_once (DOKU_PLUGIN . 'wikiiocmodel/metadata/MetaDataExceptions.php');

/*
 * TO DO ##mlozan54@xtec.cat MDC010 @@mandatori @@BEGIN
 *      Elements necessaris per a la crida efectiva al component de PERSISTÈNCIA
 *      require_once, ...
 */
require_once (DOKU_PLUGIN . 'wikiiocmodel/metadata/persistencesimul/PersistenceSimul.php');
/*
 * TO DO ##mlozan54@xtec.cat MDC010 @@mandatori @@END 
 */

abstract class MetaDataDaoAbstract implements MetaDataDaoInterface {

    /**
     * Purpose:
     * - Call PERSISTENCE component to obtain metadata (only one element)
     * @param Array $MetaDataRequestMessage
     * Restrictions:
     * - Persistence returns wellformed JSON
     * - mandatory idResource,projectType,metaDataSubSet in param array $MetaDataRequestMessage
     * - if doesn't exist metadata, then WikiIocModelException -> MetaDataNotFound
     * @return JSON {keymd1:valormd1,...,keymdx:valormdx}
     */
    public function getMeta($MetaDataRequestMessage) {
        //Check parameters mandatories
        $checkParameters = false;
        if (isset($MetaDataRequestMessage['persistence'])) {
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
        }
        if (!$checkParameters) {
            throw new WrongParams();
        }
        //Call PERSISTENCE method
        /*
         * TO DO ##mlozan54@xtec.cat MDC010 @@mandatori @@BEGIN
         *      crida efectiva al mètode concret de la persistència
         */
        $jSONArray = $this->__getMetaPersistence($MetaDataRequestMessage);
        
        /*
         * TO DO ##mlozan54@xtec.cat MDC010 @@mandatori @@END 
         */

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
        return $MetaDataRequestMessage['persistence']->createProjectMetaDataQuery()->getMeta($MetaDataRequestMessage['idResource'], $MetaDataRequestMessage['projectType'], $MetaDataRequestMessage['metaDataSubSet']);
    }

    /**
     * Purpose:
     * - Call PERSISTENCE component to updtate metadata (only one element)
     * @param Array $MetaDataRequestMessage
     * Restrictions:     
     * - mandatory idResource,projectType,metaDataSubSet in param array $MetaDataRequestMessage
     * - mandatory: MetaDataEntity->MetaDataValue
     * - MetaDataEntity->MetaDataValue wellformed JSON --> this restriction is managed by MetaDataEntityAbstract
     * - if persistence return not true, then wellformed JSON
     * - if persistence returns {"error","5120"}, then WikiIocModelException->PersistenceNsNotFound
     * - if persistence returns {"error","5090"}, then WikiIocModelException->MetaDataNotUpdated
     * @return success:true
     */
    public function setMeta($MetaDataEntity, $MetaDataRequestMessage) {

        /*
         * Per a la persistència
         *  Crida → [PERSISTENCE].setMeta(idResource,projectType,metaDataSubSet,metaDataValue) 
          //paràmetres String i metaDataValue és JSON {keymd1:valormd1,...,keymdx:valormdx}
          Restriccions →
          Si idResource no existeix, llavors el component de Persistència haurà de retornar {"error":"5120"}
          Qualsevol error, la persistència retornarà {"error":"5090"}
         */

        //Check parameters mandatories
        $checkParameters = false;
        if (isset($MetaDataRequestMessage['persistence'])) {
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
        }
        if (!$checkParameters) {
            throw new WrongParams();
        }
        //MetaDataEntity->MetaDataValue wellformed JSON --> this restriction is managed by MetaDataEntityAbstract
        /* $encoder = new JSON();
          $arrayConfigPre = $encoder->decode($MetaDataEntity->getMetaDataValue());
          if (json_last_error() != JSON_ERROR_NONE) {
          throw new MalFormedJSON();
          } */

        //Call PERSISTENCE method
        /*
         * TO DO ##mlozan54@xtec.cat MDC010 @@mandatori @@BEGIN
         *      crida efectiva al mètode concret de la persistència
         */
        
        $jSONArray = $this->__setMetaPersistence($MetaDataEntity,$MetaDataRequestMessage);
        
        /*
         * TO DO ##mlozan54@xtec.cat MDC010 @@mandatori @@END 
         */

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
            //if persistence returns {"error","5120"}, then WikiIocModelException->PersistenceNsNotFound
            //if persistence returns {"error","5090"}, then WikiIocModelException->MetaDataNotUpdated
            $arrayConfig = array();
            foreach ($arrayConfigPre as $obj => $value) {
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
     * Restrictions:
     * - 
     * @return success -> true, error --> json with error value
     */
    public function __setMetaPersistence($MetaDataEntity,$MetaDataRequestMessage) {
        return $MetaDataRequestMessage['persistence']->createProjectMetaDataQuery()->setMeta($MetaDataRequestMessage['idResource'], $MetaDataRequestMessage['projectType'], $MetaDataRequestMessage['metaDataSubSet'], $MetaDataEntity->getMetaDataValue());
        
    }

}
