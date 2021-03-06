<?php
/**
 * Component: Project / MetaData
 * Purposes:
 * - Class giving config utils
 * - Config utils are obtained from Persistence Component
 * - Only are calling Persistence, if it not has data yet (as an inMemory behavior)
 * @author Miguel Àngel Lozano Márquez<mlozan54@ioc.cat>
 * @modified by Rafael Claver
 */
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . "lib/plugins/");
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_PLUGIN . "wikiiocmodel/");

require_once( DOKU_INC . 'inc/JSON.php' );
require_once (WIKI_IOC_MODEL."metadata/MetaDataExceptions.php");

class MetaDataDaoConfig {

    /*
     * Array bidimensional containing
     * projectType => array(metaDataSubset => JSON class:ns)
     */
    private static $ClassesNameSpaces = array();

    static function getClassesNameSpaces() {
        return self::$ClassesNameSpaces;
    }

    static function setClassesNameSpaces($ClassesNameSpaces) {
        self::$ClassesNameSpaces = $ClassesNameSpaces;
    }

    /**
     * Call PERSISTENCE component to obtain ns classes Repository, DAO, Entity i Render
     * @param string $projectType, $metaDataSubset
     * @return JSON with {class:ns, ..., class:ns}
     */
    //
    //ESTA FUNCIÓN YA no la utiliza nadie
    //Ha sido sustituida por la llamada directa al método correspondiente de la clase ProjectMetaDataQuery
    //
//    public static function getMetaDataConfig($projectType, $metaDataSubset, $persistence, $configSubSet=NULL) {
//        if ($configSubSet === NULL) {
//            $configSubSet = ProjectKeys::KEY_METADATA_CLASSES_NAMESPACES;
//        }
//        $exists = false;
//        if (array_key_exists($projectType, self::$ClassesNameSpaces)) {
//            if (array_key_exists($metaDataSubset, self::$ClassesNameSpaces[$projectType])) {
//                $exists = true;
//            }
//        }
//        if (!$exists) {
//            $jSONArray = $persistence->createProjectMetaDataQuery(FALSE, $metaDataSubset, $projectType)->getMetaDataConfig($configSubSet);
//            $arrayConfigPre = self::controlMalFormedJson($jSONArray, "array");
//            $arrayConfig = array_values($arrayConfigPre)[0];
//
//            self::$ClassesNameSpaces[$projectType][$metaDataSubset] = json_encode($arrayConfig);
//        }
//        return self::$ClassesNameSpaces[$projectType][$metaDataSubset];
//    }

    //ESTA FUNCIÓN YA no la utiliza nadie
//    public static function getMetaDataFileName($projectType, $metaDataSubset, $persistence) {
//        $arrConfigProject = self::getMetaDataDefinition($projectType, $metaDataSubset, $persistence, ProjectKeys::KEY_METADATA_PROJECT_STRUCTURE);
//        return $arrConfigProject[$metaDataSubset];
//    }

    private static function getMetaDataDefinition($projectType, $metaDataSubset, $persistence, $configSubSet=NULL) {
        if ($configSubSet === NULL) {
            $configSubSet = ProjectKeys::KEY_METADATA_PROJECT_STRUCTURE;
        }
        $jsonConfigProject = $persistence->createProjectMetaDataQuery(FALSE, $metaDataSubset, $projectType)->getMetaDataConfig($configSubSet);
        $arrConfigProject = self::controlMalFormedJson($jsonConfigProject, "array");
        return $arrConfigProject;
    }

    /**
     * Call PERSISTENCE component to obtain data model from metaDataSubSet (structure)
     * @param string $projectType, $metaDataSubset
     * @return JSON con las keys, del tipo principal, del archivo de configuración
     */
    public static function getMetaDataStructure($projectType, $metaDataSubset, $persistence, $configSubSet=NULL) {
        $ret = self::getMetaDataDefinition($projectType, $metaDataSubset, $persistence, $configSubSet);
        $type = $ret['mainType']['typeDef'];
        return json_encode($ret['typesDefinition'][$type]['keys']);
    }

    public static function getMetaDataTypesDefinition($projectType, $metaDataSubset, $persistence, $configSubSet=NULL) {
        $ret = self::getMetaDataDefinition($projectType, $metaDataSubset, $persistence, $configSubSet);
        return json_encode($ret['typesDefinition']);
    }

    //[TRASPASADO] a ProjectMetaDataQuery.php
//    public static function getMetaDataComponentTypes($projectType, $metaDataSubset, $persistence) {
//        $ret = self::getMetaDataDefinition($projectType, $metaDataSubset, $persistence, ProjectKeys::KEY_METADATA_COMPONENT_TYPES);
//        return ($ret) ? $ret[$metaDataSubset] : NULL;
//    }

    /**
     * Call PERSISTENCE component to obtain ns containing project metadata and his projectType (starting from a nsRoot given)
     * @param string $nsRoot
     * @return {ns:projectType,...,ns:projectType}
     */
    public static function getMetaDataElementsKey($nsRoot, $persistence) {
        $jSONArray = $persistence->createProjectMetaDataQuery()->getMetaDataElementsKey($nsRoot);
        $jSONArray = self::controlMalFormedJson($jSONArray);
        return $jSONArray;
    }

    public static function controlMalFormedJson($jsonVar, $typeReturn="object") {
        if ($jsonVar) {
            $t = ($typeReturn==="array") ? TRUE : FALSE;
            $obj = json_decode($jsonVar, $t);
            if (json_last_error() != JSON_ERROR_NONE) {
                throw new MalFormedJSON();
            }
        }
        return $obj;
    }

}
