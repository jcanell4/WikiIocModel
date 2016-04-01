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
    protected static $CONFIGUSUBSETST = "metaDataProjectStructure";

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
    public static function getMetaDataConfig($projectType, $metaDataSubset, $persistence, $configSubSet = null) {
        if ($configSubSet == null) {
            $configSubSet = self::$CONFIGUSUBSET;
        }
        print_r("configSubSet ES ES: " . $configSubSet);
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
            $jSONArray = $persistence->createProjectMetaDataQuery()->getMetaDataConfig($projectType, $metaDataSubset, $configSubSet);
            /*
             * TO DO ##mlozan54@xtec.cat MDC010 @@mandatori @@END 
             */
            //print_r("getMetaDataConfig -> projectType: ".$projectType);
            print_r("\ngetMetaDataConfig -> jSONArray: " . $jSONArray);

            $encoder = new JSON();
            $arrayConfigPre = $encoder->decode($jSONArray, true);
            if (json_last_error() != JSON_ERROR_NONE) {
                throw new MalFormedJSON();
            }

            $arrayConfig = array();
            print_r("\nAAAAAAAAAAAAAAAAAAAAAA arrayConfigPre\n");
            print_r($arrayConfigPre);
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
     * - Call PERSISTENCE component to obtain file name containing metadata
     * @param String projectType, String metaDataSubset
     * Restrictions:
     * - Persistence returns wellformed JSON
     * - mandatory $projectType, $metaDataSubSet
     * @return JSON with {class:ns, ..., class:ns}
     */
    public static function getMetaDataFileName($projectType, $metaDataSubset, $persistence, $configSubSet = null) {
        if ($configSubSet == null) {
            $configSubSet = self::$CONFIGUSUBSETST;
        }
        print_r("\nconfigSubSet ES ES: " . $configSubSet);

        //Call PERSISTENCE method
        /*
         * TO DO ##mlozan54@xtec.cat MDC010 @@mandatori @@BEGIN
         *      crida efectiva al mètode concret de la persistència
         */
        $jSONArray = $persistence->createProjectMetaDataQuery()->getMetaDataConfig($projectType, $metaDataSubset, $configSubSet);
        /*
         * TO DO ##mlozan54@xtec.cat MDC010 @@mandatori @@END 
         */
        //print_r("getMetaDataConfig -> projectType: ".$projectType);
        print_r("\ngetMetaDataConfig -> jSONArray: " . $jSONArray);

        $encoder = new JSON();
        $arrayConfigPre = $encoder->decode($jSONArray, true);
        if (json_last_error() != JSON_ERROR_NONE) {
            throw new MalFormedJSON();
        }

        $arrayConfig = array();
        print_r("\nAAAAAAAAAAAAAAAAAAAAAA arrayConfigPre\n");
        print_r($arrayConfigPre);
        $arrayConfigPre = get_object_vars($arrayConfigPre);
        print_r("\nAAAAAAAAAAAAAAAAAAAAAAGAIN arrayConfigPre\n");
        print_r($arrayConfigPre);
        print_r("\nAAAAAAAAAAAAAAAAAAAAAAGAIN00000000000000000000 arrayConfigPre\n");
        print_r(reset($arrayConfigPre));
        return reset($arrayConfigPre);
    }

    /**
     * Purpose:
     * - Call PERSISTENCE component to obtain data model from metaDataSubSet (structure)
     * @param String projectType, String metaDataSubset
     * Restrictions:
     * - Persistence returns wellformed JSON
     * - mandatory $projectType, $metaDataSubSet
     * @return JSON with {class:ns, ..., class:ns}
     */
    public static function getMetaDataStructure($projectType, $metaDataSubset, $persistence, $configSubSet = null) {
        if ($configSubSet == null) {
            $configSubSet = self::$CONFIGUSUBSETST;
        }
        print_r("\nconfigSubSet ES ES: " . $configSubSet);

        //Call PERSISTENCE method
        /*
         * TO DO ##mlozan54@xtec.cat MDC010 @@mandatori @@BEGIN
         *      crida efectiva al mètode concret de la persistència
         */
        $jSONArray = $persistence->createProjectMetaDataQuery()->getMetaDataConfig($projectType, $metaDataSubset, $configSubSet);
        /*
         * TO DO ##mlozan54@xtec.cat MDC010 @@mandatori @@END 
         */
        //print_r("getMetaDataConfig -> projectType: ".$projectType);
        print_r("\ngetMetaDataConfig -> jSONArray: " . $jSONArray);

        $encoder = new JSON();
        $arrayConfigPre = $encoder->decode($jSONArray, true);
        if (json_last_error() != JSON_ERROR_NONE) {
            throw new MalFormedJSON();
        }

        $arrayConfig = array();
        print_r("\nAAAAAAAAAAAAAAAAAAAAAA arrayConfigPre\n");
        print_r($arrayConfigPre);
        $arrayConfigPre = get_object_vars($arrayConfigPre);
        print_r("\nAAAAAAAAAAAAAAAAAAAAAAGAIN arrayConfigPre\n");
        print_r($arrayConfigPre);
        print_r("\nAAAAAAAAAAAAAAAAAAAAAAGAIN00000000000000000000 arrayConfigPre\n");
        print_r($arrayConfigPre["keysDefinition"]);
        print_r($encoder->encode($arrayConfigPre["keysDefinition"]));
        return $encoder->encode($arrayConfigPre["keysDefinition"]);
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
    public static function getMetaDataElementsKey($nsRoot, $persistence) {
        //Call PERSISTENCE method
        /*
         * TO DO ##mlozan54@xtec.cat MDC010 @@mandatori @@BEGIN
         *      crida efectiva al mètode concret de la persistència
         */
        $jSONArray = $persistence->createProjectMetaDataQuery()->getMetaDataElementsKey($nsRoot);
        /*
         * TO DO ##mlozan54@xtec.cat MDC010 @@mandatori @@END 
         */
        print_r("\nEEEEEEEEEEEEEEEEEEEE  ElementsKey  Null????????????\n");
        print_r($jSONArray);
        print_r("\nFIFIFIFIFIFIEEEEEEEEEEEEEEEEEEEE  ElementsKey  Null????????????\n");
        $encoder = new JSON();
        $arrayConfigPre = $encoder->decode($jSONArray);
        if (json_last_error() != JSON_ERROR_NONE) {
            throw new MalFormedJSON();
        }
        return $jSONArray;
    }

}
