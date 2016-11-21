<?php

if (!defined("DOKU_INC"))
    die();
if (!defined('DOKU_PLUGIN')) {
    define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
}
require_once( DOKU_INC . 'inc/JSON.php' );
require_once (DOKU_PLUGIN . 'ownInit/WikiGlobalConfig.php');
require_once (DOKU_PLUGIN . 'wikiiocmodel/persistence/DataQuery.php');

/**
 * Description of ProjectMetaDataQuery
 *
 * @author josep
 */
class ProjectMetaDataQuery extends DataQuery {

//    private static $retornNsConfig = '{"metaDataClassesNameSpaces":
//            {
//            "MetaDataRepository": "default",
//            "MetaDataDAO": "default",
//            "MetaDataEntity": "default",
//            "MetaDataRender": "default"
//            }
//        }';
//    private static $retornNsConfigPt1 = '{"metaDataClassesNameSpaces":
//            {
//            "MetaDataRepository": "pt1",
//            "MetaDataDAO": "pt1",
//            "MetaDataEntity": "pt1",
//            "MetaDataRender": "pt1"
//            }
//        }';
//    private static $retornNsConfigPt2 = '{"metaDataClassesNameSpaces":
//            {
//            "MetaDataRepository": "pt1",
//            "MetaDataDAO": null,
//            "MetaDataEntity": null,
//            "MetaDataRender": null
//            }
//        }';
//    private static $retornNsConfigM = "{'Organización': 'Equipo de documentación PHP'}";
//    private static $retornNsProject = '{"fp:dam:m03":"materials","fp:daw:m07":"materials","fp:daw:m09":"adocs"}';
//    private static $retornNsProjectM = '{"fp:dam:m03":"materials","fp:daw:m07materials"]';
//    private static $retornGetMetaData = '{"keymd1":"valormd1","keymdx":"valormdx"}';
//    private static $retornGetMetaDataM = '{"keymd1":"valormd1","keymdxvalormdx"]';
//    private static $retornSetMetaDataNs = '{"error":"5120"}';
//    private static $retornSetMetaDataGen = '{"error":"5090"}';
//    private static $retornGetMetaDataMaterialsM03 = '{"keymd1":"valorf","keymd3x":"valormd3x"}';
//    private static $retornGetMetaDataMaterialsM07 = '{"keymd7":"valormd7","keymd7x":"valormd7x"}';
//    private static $retornGetMetaDataAdocsM09 = '{"keymd1":"valorf","keymd9x":"valormd9x"}';
//    private static $retornStructure = '{"user":{"tipus": "string","mandatory":true},"rol":{"mandatory":true},"xyz":{"mandatory":false}}';
//    private static $retornNsProjectX = '{"fp:dam:m03:fptx":"ptx","fp:daw:m07:fitxerx":"ptx","fp:daw:m07:fptx":"ptx","fp:daw:m09:fitxerx":"defaultProject"}';
//    private static $retornGetMetaDataMX = '{"user":"mlozan54","rol":"autor"}';
//    private static $retornGetMetaDataMXX = '{"user":"mlozan54","rol":"editor"}';
//    private static $retornGetMetaDataMXY = '{"user":"mlozan54","rol":"autor"}';

    public function getMetaDataConfig($projectType, $metaDataSubset, $configSubSet) {
        $configMain = @file_get_contents(DOKU_PLUGIN . "wikiiocmodel/projects/" . $projectType . "/metadata/config/configMain.json");
        if ($configMain == false) {
            $configMain = @file_get_contents(DOKU_PLUGIN . "wikiiocmodel/projects/" . "defaultProject" . "/metadata/config/configMain.json");
        }
//        print_r("\n START getMetaDataConfig getMetaDataConfig \n");

//        print_r(DOKU_PLUGIN . "wikiiocmodel/projects/" . $projectType . "/metadata/config/configMain.json");
//        print_r("\n");
        $configMainArray = json_decode($configMain, true);
//        print_r($configMainArray);
//        print_r("\n");
        $toReturn = "";
        $encoder = new JSON();
        for ($i = 0; $i < sizeof($configMainArray[$configSubSet]); $i++) {
//            print_r("\n i \n");
//            print_r($i);
//            print_r("\n");
//            print_r("\n configSubSet \n");
//            print_r($configSubSet);
//            print_r("\n");
//            print_r("\n metaDataSubset \n");
//            print_r($metaDataSubset);
//            print_r("\n");
//            print_r("\n");
//            print_r($configMainArray[$configSubSet]);
//            print_r("\n");
            if (isset($configMainArray[$configSubSet][$i][$metaDataSubset])) {

                $toReturn = $encoder->encode($configMainArray[$configSubSet][$i]);
//                print_r("\n NO DEFAULT");
//                print_r($toReturn);
//                print_r("\n");
            } else {
                if (isset($configMainArray[$configSubSet][$i]["defaultSubSet"])) {
                    if ($toReturn == "") {
                        $toReturn = $encoder->encode($configMainArray[$configSubSet][$i]);
//                        print_r("\n SI DEFAULT");
//                        print_r($toReturn);
//                        print_r("\n");
                    }
                }
            }
        }
//        print_r("\n END getMetaDataConfig getMetaDataConfig \n");
        return $toReturn;
    }

    //Retorn → JSON {ns1:projectType1, …, nsm:projectTypem}
    public function getMetaDataElementsKey($nsRoot) {
        //getNsTree("fp:dam", 0, false,true)
        $elementsKeyArray = $this->getNsTree($nsRoot, 0, true, false);
//        print_r("\n INIT elementsKeyArray elementsKeyArray elementsKeyArray \n");
//        print_r($elementsKeyArray);
//        print_r("\n END elementsKeyArray elementsKeyArray elementsKeyArray \n");
        $returnArray = array();
        foreach ($elementsKeyArray['children'] as $index => $arrayElement) {
            if ($arrayElement['type'] == 'p') {
                $returnArray[$arrayElement['id']] = $arrayElement['projectType'];
            }
        }
        /*
         * Add the $nsRoot itself, if it's a project (only a type of project)
         */
        $metaDataPath = WikiGlobalConfig::getConf('mdprojects');
        $metaDataExtension = WikiGlobalConfig::getConf('mdextension');
        $pathProject = str_replace(':', '/', $nsRoot);
        $pathProject = $metaDataPath . '/'. $pathProject;
        $dirProject = opendir($pathProject);
        while ($current = readdir($dirProject)) {
            $pathProjectOne = $pathProject . '/' . $current;
            if (is_dir($pathProjectOne)) {
                $dirProjectOne = opendir($pathProjectOne);
                                    while ($currentOne = readdir($dirProjectOne)) {
                                        //print_r("\n current2 current2 current2 current2 current2 current2 current2 current2 current2 \n");
                                        //print_r("\n" . $currentOne . "\n");
                                        if (!is_dir($pathProjectOne . '/' . $currentOne)) {
                                            $fileTokens = explode(".", $currentOne);
                                            //print_r("\n" . $fileTokens[sizeof($fileTokens) - 1] . "\n");
                                            //print_r("\naaa" . $metaDataExtension . "\n");
                                            if ($fileTokens[sizeof($fileTokens) - 1] == $metaDataExtension) {
                                                //print_r("\n PROJECTE PROJECTE PROJECTE PROJECTE PROJECTE PROJECTE PROJECTE PROJECTE PROJECTE PROJECTE PROJECTE PROJECTE \n");
                                                //print_r("\n" . $currentOne . "\n");
                                                //És projecte i escriure   p  
                                                $returnArray[$nsRoot] = $current;
                                            }
                                        }
                                    }
                
                
                
            }
        }
        
        /*
         * END Add the $nsRoot, if it's a project
         */
//        print_r("\n INIT returnArray \n");
//        print_r($returnArray);
//        print_r("\n END returnArray \n");

        if (sizeof($returnArray) > 0) {
            $encoder = new JSON();
            $toReturn = $encoder->encode($returnArray);
        } else {
            return null;
        }

//        print_r("\n INIT getMetaDataElementsKey getMetaDataElementsKey getMetaDataElementsKey \n");
//        print_r($toReturn);
//        print_r("\n END getMetaDataElementsKey getMetaDataElementsKey getMetaDataElementsKey \n");
        return $toReturn;


        //$retornNsProject = '{"fp:dam:m03":"materials","fp:daw:m07":"materials","fp:daw:m09":"adocs"}'
        /* if ($nsRoot == "fp") {
          return self::$retornNsProject;
          } else {
          if ($nsRoot == "chg2") {
          return self::$retornNsProjectX;
          } else {
          if ($nsRoot == "fp:daw:m07") {
          return null;
          } else {
          return self::$retornNsProjectM;
          }
          }
          } */
    }

    // Retorn --> JSON
    public function getMeta($idResource, $projectType, $metaDataSubSet, $filename) {
        /*
         * Obtain metadata files general path
         */
//        $metaDataPath = DOKU_INC . WikiGlobalConfig::getConf('mdprojects');
        $metaDataPath = WikiGlobalConfig::getConf('mdprojects');

        //$metaDataPath = '/home/professor/DesenvolupamentIOC/DesenvolupamentIOC/dokuwiki_30/' . WikiGlobalConfig::getConf('mdprojects');
        /*
         * Convert idResource delimiter ':' to persistence delimiter '/'
         */
        $idResourceArray = explode(':', $idResource);
        $idResoucePath = implode("/", $idResourceArray);
//        print_r("\n");
//        print_r(DOKU_INC);
//        print_r("\n");
//        print_r($metaDataPath);
//        print_r($idResoucePath);
        $metaDataReturn = null;
        /*
         * Get content file and return metaData included in $metaDataSubSet
         */
        $contentFile = @file_get_contents($metaDataPath . '/' . $idResoucePath . '/' . $projectType . '/' . $filename);
        if ($contentFile != false) {
            $contentMainArray = json_decode($contentFile, true);
            foreach ($contentMainArray as $clave => $valor) {
                if ($clave == $metaDataSubSet) {
                    if (is_array($valor)) {
                        $encoder = new JSON();
                        $metaDataReturn = $encoder->encode($valor);
                        break;
                    }
                }
            }
        }
//        print_r($metaDataReturn);
        return $metaDataReturn;
    }

    public function setMeta($idResource, $projectType, $metaDataSubSet, $filename, $metaDataValue) {
        $metaDataPath = WikiGlobalConfig::getConf('mdprojects');
//        $metaDataPath = DOKU_INC . WikiGlobalConfig::getConf('mdprojects');

        /*
         * Convert idResource delimiter ':' to persistence delimiter '/'
         */
        $idResourceArray = explode(':', $idResource);
        //$theFile = array_pop($idResourceArray);

        $idResoucePath = implode("/", $idResourceArray);
        /*
         * CHECK AND CREATES DIRS
         */
//        print_r("\n START pmdqsetmeta \n");
//        print_r($idResource . "\n");
//        print_r($filename . "\n");
//        print_r($idResourceArray . "\n");
//        print_r($theFile . "\n");
//        print_r($metaDataPath . "\n");
//        print_r($idResoucePath . "\n");
//        print_r($projectType . "\n");
//        print_r($metaDataPath . '/' . $idResoucePath . '/' . $projectType);
//        print_r("\n END pmdqsetmeta \n");
        $resourceCreated = false;
        if (!is_dir($metaDataPath . '/' . $idResoucePath . '/' . $projectType)) {
            $resourceCreated = mkdir($metaDataPath . '/'. $idResoucePath . '/' . $projectType, 0777, true);
        }
        //$fp = @fopen($metaDataPath . $idResoucePath .'/'.$projectType. '/' . $theFile, 'a');
        $fp = @fopen($metaDataPath . '/' . $idResoucePath . '/' . $projectType . '/' . $filename, 'a');
        if ($fp != false) {
            fclose($fp);
            $resourceCreated = true;
        }

        if ($resourceCreated) {
            $contentFile = file_get_contents($metaDataPath . '/'. $idResoucePath . '/' . $projectType . '/' . $filename);
            $newMetaDataSubSet = true;
            if ($contentFile != false) {
                $contentMainArray = json_decode($contentFile, true);
                foreach ($contentMainArray as $clave => $valor) {
                    if ($clave == $metaDataSubSet) {
                        $contentMainArray[$metaDataSubSet] = json_decode($metaDataValue, true);
                        $newMetaDataSubSet = false;
                    }
                }
                $encoder = new JSON();
                $resultPutContents = file_put_contents($metaDataPath . '/' . $idResoucePath . '/' . $projectType . '/' . $filename, $encoder->encode($contentMainArray));
            }
            if ($newMetaDataSubSet) {
                $contentMainArray[$metaDataSubSet] = json_decode($metaDataValue, true);
                $encoder = new JSON();
                $resultPutContents = file_put_contents($metaDataPath . '/' . $idResoucePath . '/' . $projectType . '/' . $filename, $encoder->encode($contentMainArray));
            }
        } else {
            $resourceCreated = '{"error":"5090"}';
        }
        return $resourceCreated;
    }

    public function getFileName($id, $especParams = NULL) {
        
    }

    public function getNsTree($currentNode, $sortBy, $onlyDirs = FALSE, $expandProjects = TRUE, $root=FALSE) {

        $base = WikiGlobalConfig::getConf('datadir');

        return $this->getNsTreeFromGenericSearch($base, $currentNode, $sortBy, $onlyDirs, 'search_universal', $expandProjects);
    }

}
