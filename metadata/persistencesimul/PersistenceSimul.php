<?php

/**
 * Component: Project / MetaData
 * Status: @@Development
 * Purposes:
 * - Simulació del component PERSISTENCE mentre no es pugui fer una crida real a aquest component
 * @author Miguel Àngel Lozano Márquez<mlozan54@ioc.cat>
 */
class PersistenceSimul {

    private static $retornNsConfig = '{"metaDataClassesNameSpaces":
            {
            "MetaDataRepository": "default", 
            "MetaDataDAO": "default",
            "MetaDataEntity": "default",
            "MetaDataRender": "default"
            }
        }';
    private static $retornNsConfigM = "{'Organización': 'Equipo de documentación PHP'}";
    private static $retornNsProject = '{"fp:dam:m03":"materials","fp:daw:m07":"materials"}';
    private static $retornNsProjectM = '{"fp:dam:m03":"materials","fp:daw:m07materials"]';
    private static $retornGetMetaData = '{"keymd1":"valormd1","keymdx":"valormdx"}';
    private static $retornGetMetaDataM = '{"keymd1":"valormd1","keymdxvalormdx"]';
    private static $retornSetMetaDataNs = '{"error":"5120"}';
    private static $retornSetMetaDataGen = '{"error":"5090"}';

    public static function getMetaDataConfig($projectType, $metaDataSubset, $configSubSet) {
        if ($projectType == "a") {
            return self::$retornNsConfig;
        } else {
            return self::$retornNsConfigM;
        }
    }

    //Retorn → JSON {ns1:projectType1, …, nsm:projectTypem}
    public static function getMetaDataElementsKey($nsRoot) {
        if ($nsRoot == "fp") {
            return self::$retornNsProject;
        } else {
            return self::$retornNsProjectM;
        }
    }

    public static function getMeta($ns, $projectType, $metaDataSubSet) {
        if ($ns === "fp") {
            return self::$retornGetMetaData;
        } else {
            if ($ns === "bl") {
                return "";
            } else {
                return self::$retornGetMetaDataM;
            }
        }
    }

    public static function setMeta($ns, $projectType, $metaDataSubSet, $metaDataValue) {
        if ($ns === "fp") {
            return true;
        } else {
            if ($ns === "nt") {
                print("ABC ABC ABC ABC");
                return "abc";
            } else {
                if ($ns === "ns") {
                    return self::$retornSetMetaDataNs;
                } else {
                    return self::$retornSetMetaDataGen;
                }
            }
        }
    }

}
