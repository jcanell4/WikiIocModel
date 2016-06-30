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
    private static $retornNsConfigPt1 = '{"metaDataClassesNameSpaces":
            {
            "MetaDataRepository": "pt1", 
            "MetaDataDAO": "pt1",
            "MetaDataEntity": "pt1",
            "MetaDataRender": "pt1"
            }
        }';
    private static $retornNsConfigPt2 = '{"metaDataClassesNameSpaces":
            {
            "MetaDataRepository": "pt1", 
            "MetaDataDAO": null,
            "MetaDataEntity": null,
            "MetaDataRender": null
            }
        }';
    private static $retornNsConfigM = "{'Organización': 'Equipo de documentación PHP'}";
    private static $retornNsProject = '{"fp:dam:m03":"materials","fp:daw:m07":"materials","fp:daw:m09":"adocs"}';
    private static $retornNsProjectM = '{"fp:dam:m03":"materials","fp:daw:m07materials"]';
    private static $retornGetMetaData = '{"keymd1":"valormd1","keymdx":"valormdx"}';
    private static $retornGetMetaDataM = '{"keymd1":"valormd1","keymdxvalormdx"]';
    private static $retornSetMetaDataNs = '{"error":"5120"}';
    private static $retornSetMetaDataGen = '{"error":"5090"}';
    private static $retornGetMetaDataMaterialsM03 = '{"keymd1":"valorf","keymd3x":"valormd3x"}';
    private static $retornGetMetaDataMaterialsM07 = '{"keymd7":"valormd7","keymd7x":"valormd7x"}';
    private static $retornGetMetaDataAdocsM09 = '{"keymd1":"valorf","keymd9x":"valormd9x"}';

    public static function getMetaDataConfig($projectType, $metaDataSubset, $configSubSet) {
        if ($projectType == "a" || $projectType == "fp" || $projectType == "Materials" || $projectType == "materials") {
            return self::$retornNsConfig;
        } else {
            if ($projectType == "pt1" || $projectType == "adocs") {
                return self::$retornNsConfigPt1;
            } else {
                if ($projectType == "pt2") {
                    return self::$retornNsConfigPt2;
                } else {
                    return self::$retornNsConfigM;
                }
            }
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

    public static function getMeta($idResource, $projectType, $metaDataSubSet) {
        if($idResource==="fp:dam:m03"){
            return self::$retornGetMetaDataMaterialsM03;
        }
        if($idResource==="fp:daw:m07"){
            return self::$retornGetMetaDataMaterialsM07;
        }
        if($idResource==="fp:daw:m09"){
            return self::$retornGetMetaDataAdocsM09;
        }
        if ($idResource === "fp") {
            return self::$retornGetMetaData;
        } else {
            if ($idResource === "bl") {
                return "";
            } else {
                return self::$retornGetMetaDataM;
            }
        }
    }

    public static function setMeta($idResource, $projectType, $metaDataSubSet, $metaDataValue) {
        if ($idResource === "fp"|| $projectType == "adocs" || $projectType == "materials") {
            return true;
        } else {
            if ($idResource === "nt") {
                print("ABC ABC ABC ABC");
                return "abc";
            } else {
                if ($idResource === "idResource") {
                    return self::$retornSetMetaDataNs;
                } else {
                    return self::$retornSetMetaDataGen;
                }
            }
        }
    }

}
