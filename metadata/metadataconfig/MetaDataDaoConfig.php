<?php

/**
 * Component: Project / MetaData
 * Status: @@Tested + @@pending: could act as cache
 * Purposes:
 * - Class giving config utils
 * - Config utils are obtained from Persistence Component
 * - Only are calling Persistence, if it not has data yet (as an inMemory behavior)
 * @author Miguel Àngel Lozano Márquez<mlozan54@ioc.cat>
 */
if (!defined("DOKU_INC"))
    die();
if (!defined('DOKU_PLUGIN')) {
    define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
}
require_once( DOKU_INC . 'inc/JSON.php' );
require_once (DOKU_PLUGIN . 'wikiiocmodel/metadata/MetaDataExceptions.php');
/*
 * TO DO ##mlozan54@xtec.cat MDC020 @@advisable  @@BEGIN
 *  - Aquesta classe podria actuar com a cache amb temps per buidar $ClassesNameSpaces
 */
/*
 * TO DO ##mlozan54@xtec.cat MDC020 @@advisable  @@END
 */

/*
 * TO DO ##mlozan54@xtec.cat MDC010 @@mandatori @@BEGIN
 *      Elements necessaris per a la crida efectiva al component de PERSISTÈNCIA
 *      require_once, ...
 */
require_once (DOKU_PLUGIN . 'wikiiocmodel/metadata/persistencesimul/PersistenceSimul.php');
/*
 * TO DO ##mlozan54@xtec.cat MDC010 @@mandatori @@END 
 */

class MetaDataDaoConfig {
    /*
     * Constant to define key to obtain metadad configuration from Persistence
     */

    protected static $CONFIGUSUBSET = "metaDataClassesNameSpaces";

    /*
     * Array bidimensional containing 
     * projectType => array(
     *                      metaDataSubset => JSON class:ns)
     */
    private static $ClassesNameSpaces = array();

    static function getClassesNameSpaces() {
        return self::$ClassesNameSpaces;
    }

    static function setClassesNameSpaces($ClassesNameSpaces) {
        self::$ClassesNameSpaces = $ClassesNameSpaces;
    }

    /**
     * Purpose:
     * - Call PERSISTENCE component to obtain ns classes Repository, DAO, Entity i Render
     * @param String projectType, String metaDataSubset
     * Restrictions:
     * - Persistence returns wellformed JSON
     * - mandatory $projectType, $metaDataSubSet
     * @return JSON with {class:ns, ..., class:ns}
     */
    public static function getMetaDataConfig($projectType, $metaDataSubset) {
        $exists = false;
        if (array_key_exists($projectType, self::$ClassesNameSpaces)) {
            if (array_key_exists($metaDataSubset, self::$ClassesNameSpaces[$projectType])) {
                $exists = true;
            }
        }
        if (!$exists) {
            //Call PERSISTENCE method
            /*
             * TO DO ##mlozan54@xtec.cat MDC010 @@mandatori @@BEGIN
             *      crida efectiva al mètode concret de la persistència
             */
            $jSONArray = PersistenceSimul::getMetaDataConfig($projectType, $metaDataSubset, self::$CONFIGUSUBSET);
            /*
             * TO DO ##mlozan54@xtec.cat MDC010 @@mandatori @@END 
             */
            //print_r("getMetaDataConfig -> projectType: ".$projectType);
            //print_r("getMetaDataConfig -> jSONArray: ".$jSONArray);
            
            $encoder = new JSON();
            $arrayConfigPre = $encoder->decode($jSONArray);
            if (json_last_error() != JSON_ERROR_NONE) {
                throw new MalFormedJSON();
            }

            $arrayConfig = array();
            foreach ($arrayConfigPre as $obj1 => $value1) {
                foreach ($value1 as $obj => $value) {
                    $arrayConfig[$obj] = $value;
                }
            }
            self::$ClassesNameSpaces[$projectType][$metaDataSubset] = $encoder->encode($arrayConfig);
        }
        return self::$ClassesNameSpaces[$projectType][$metaDataSubset];
    }

    /**
     * Purpose:
     * - Call PERSISTENCE component to obtain ns containing project metadata and his projectType (starting from a nsRoot given)
     * @param String ns
     * Restrictions:
     * - Persistence returns wellformed JSON
     * - mandatory $nsRoot
     * @return {ns:projectType,...,ns:projectType}
     */
    public static function getMetaDataElementsKey($nsRoot) {
        //Call PERSISTENCE method
        /*
         * TO DO ##mlozan54@xtec.cat MDC010 @@mandatori @@BEGIN
         *      crida efectiva al mètode concret de la persistència
         */
        $jSONArray = PersistenceSimul::getMetaDataElementsKey($nsRoot);
        /*
         * TO DO ##mlozan54@xtec.cat MDC010 @@mandatori @@END 
         */
        $encoder = new JSON();
        $arrayConfigPre = $encoder->decode($jSONArray);
        if (json_last_error() != JSON_ERROR_NONE) {
            throw new MalFormedJSON();
        }
        return $jSONArray;
    }

}
