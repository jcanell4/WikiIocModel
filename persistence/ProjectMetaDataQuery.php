<?php
if (!defined("DOKU_INC"))
    die();
if (!defined('DOKU_PLUGIN')) {
    define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
}
require_once( DOKU_INC . 'inc/JSON.php' );

/**
 * Description of ProjectMetaDataQuery
 *
 * @author josep
 */
class ProjectMetaDataQuery {

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
    private static $retornStructure = '{"user":{"tipus": "string","mandatory":true},"rol":{"mandatory":true},"xyz":{"mandatory":false}}';

    /*public function getMetaDataConfig($projectType, $metaDataSubset, $configSubSet) {
        if ($configSubSet == 'metaDataClassesNameSpaces') {
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
        } else {
            return self::$retornStructure;
        }
    }*/
    
    public function getMetaDataConfig($projectType, $metaDataSubset, $configSubSet) {
        $configMain = @file_get_contents(DOKU_PLUGIN."wikiiocmodel/projects/".$projectType."/metadata/config/configMain.json");
        if($configMain == false){
            $configMain = @file_get_contents(DOKU_PLUGIN."wikiiocmodel/projects/"."defaultProject"."/metadata/config/configMain.json");
        }       
        $configMainArray= json_decode($configMain,true);
        $toReturn = "";
        $encoder = new JSON();
        for($i=0;$i<sizeof($configMainArray[$configSubSet]);$i++){
            if (isset($configMainArray[$configSubSet][$i][$metaDataSubset])){              
                $toReturn = $encoder->encode($configMainArray[$configSubSet][$i]);
            }else{
                if (isset($configMainArray[$configSubSet][$i]["defaultSubSet"])){
                    if($toReturn == ""){
                        $toReturn = $encoder->encode($configMainArray[$configSubSet][$i]);
                    }
                }
            }            
        }
        print_r("\ntoReturn");
        print_r($toReturn);
        return $toReturn;
        
    }
    

    //Retorn → JSON {ns1:projectType1, …, nsm:projectTypem}
    public function getMetaDataElementsKey($nsRoot) {
        if ($nsRoot == "fp") {
            return self::$retornNsProject;
        } else {
            return self::$retornNsProjectM;
        }
    }

    public function getMeta($idResource, $projectType, $metaDataSubSet) {
        if ($idResource === "fp:dam:m03") {
            return self::$retornGetMetaDataMaterialsM03;
        }
        if ($idResource === "fp:daw:m07") {
            return self::$retornGetMetaDataMaterialsM07;
        }
        if ($idResource === "fp:daw:m09") {
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

    public function setMeta($idResource, $projectType, $metaDataSubSet, $metaDataValue) {
        if ($idResource === "fp" || $projectType == "adocs" || $projectType == "materials") {
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
