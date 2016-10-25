<?php
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');

require_once( DOKU_INC . 'inc/JSON.php' );
require_once (DOKU_PLUGIN . 'ownInit/WikiGlobalConfig.php');
require_once (DOKU_PLUGIN . 'wikiiocmodel/persistence/DataQuery.php');

/**
 * Description of ProjectMetaDataQuery
 *
 * @author josep
 */
class ProjectMetaDataQuery extends DataQuery {

    public function getMetaDataConfig($projectType, $metaDataSubset, $configSubSet) {
        
        $configMain = @file_get_contents(DOKU_PLUGIN . "wikiiocmodel/projects/" . $projectType . "/metadata/config/configMain.json");
        if ($configMain == false) {
            $configMain = @file_get_contents(DOKU_PLUGIN . "wikiiocmodel/projects/" . "defaultProject" . "/metadata/config/configMain.json");
        }

        $configMainArray = json_decode($configMain, true);
        $toReturn = "";
        $encoder = new JSON();

        for ($i = 0; $i < sizeof($configMainArray[$configSubSet]); $i++) {
            if (isset($configMainArray[$configSubSet][$i][$metaDataSubset])) {
                $toReturn = $encoder->encode($configMainArray[$configSubSet][$i]);
            } else if (isset($configMainArray[$configSubSet][$i]["defaultSubSet"])) {
                if ($toReturn == "") {
                    $toReturn = $encoder->encode($configMainArray[$configSubSet][$i]);
                }
            }
        }
        return $toReturn;
    }

    //Retorn JSON {ns1:projectType1, …, nsm:projectTypem}
    public function getMetaDataElementsKey($nsRoot) {

        $elementsKeyArray = $this->getNsTree($nsRoot, 0, true, false, false);

        $returnArray = array();
        foreach ($elementsKeyArray['children'] as $index => $arrayElement) {
            if ($arrayElement['type'] == 'p') {
                $returnArray[$arrayElement['id']] = $arrayElement['projectType'];
            }
        }

        // Add the $nsRoot itself, if it's a project (only a type of project)
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
                    if (!is_dir($pathProjectOne . '/' . $currentOne)) {
                        $fileTokens = explode(".", $currentOne);
                        if ($fileTokens[sizeof($fileTokens) - 1] == $metaDataExtension) {
                            //És projecte i escriure   p  
                            $returnArray[$nsRoot] = $current;
                        }
                    }
                }
            }
        }
        
        if (sizeof($returnArray) > 0) {
            $encoder = new JSON();
            $toReturn = $encoder->encode($returnArray);
        } else {
            return null;
        }

        return $toReturn;
    }

    // Retorn --> JSON
    public function getMeta($idResource, $projectType, $metaDataSubSet, $filename) {

        $metaDataPath = WikiGlobalConfig::getConf('mdprojects');
        $idResourceArray = explode(':', $idResource);
        $idResoucePath = implode("/", $idResourceArray);
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
        return $metaDataReturn;
    }

    public function setMeta($idResource, $projectType, $metaDataSubSet, $filename, $metaDataValue) {
        
        $metaDataPath = WikiGlobalConfig::getConf('mdprojects');
        $idResourceArray = explode(':', $idResource);
        $idResoucePath = implode("/", $idResourceArray);
        /*
         * CHECK AND CREATES DIRS
         */
        $resourceCreated = false;
        if (!is_dir($metaDataPath . '/' . $idResoucePath . '/' . $projectType)) {
            $resourceCreated = mkdir($metaDataPath . '/'. $idResoucePath . '/' . $projectType, 0777, true);
        }
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

    public function getNsTree($currentNode, $sortBy, $onlyDirs=FALSE, $expandProjects=TRUE, $hiddenProjects=FALSE) {
        $base = WikiGlobalConfig::getConf('datadir');
        return $this->getNsTreeFromGenericSearch($base, $currentNode, $sortBy, $onlyDirs, 'search_universal', $expandProjects, $hiddenProjects);
    }

}
