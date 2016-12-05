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

    const CONFIGUSUBSETSTRUCTURE = "metaDataProjectStructure";
    
    /**
     * Devuelve la lista ordenada de tipos de proyecto obtenida a partir de la lectura 
     * de la estructura de directorios de wikiiocmodel/projects/
     */
    public function getListProjectTypes() {
        $base = DOKU_PLUGIN . 'wikiiocmodel/projects/';
        $projectsDir = opendir($base);
        while ($projType = readdir($projectsDir)) {
            if (is_dir($base.$projType) && $projType !== '.' && $projType !== '..') {
                $ret[] = $projType;
            }
        }
        if ($ret) sort($ret);
        return $ret;
    }
    
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
        foreach ($elementsKeyArray['children'] as $arrayElement) {
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

    /**
     * Devuelve el estado de generación del proyecto
     * @return boolean : true si el proyecto ya ha sido generado
     */
    public function isProjectGenerated($idResource, $projectType) {
        $filename = WikiGlobalConfig::getConf('projects','wikiiocmodel')['dataSystem'];
        $jsonArr = $this->getMeta($idResource, $projectType, "system", $filename);
        $data = json_decode($jsonArr, true);
        return $data['generated'];
    }
    
    /**
     * Establece el estado 'generated'=true del proyecto
     * @return boolean : true si el estado del proyecto se ha establecido con éxito
     */
    public function setProjectGenerated($idResource, $projectType) {
        $filename = WikiGlobalConfig::getConf('projects','wikiiocmodel')['dataSystem'];
        $metaDataSubSet = "system";
        $jSysArr = $this->getMeta($idResource, $projectType, $metaDataSubSet, $filename);
        $sysValue = json_decode($jSysArr, true);
        $sysValue['generated'] = true;
        $success = $this->setMeta($idResource, $projectType, $metaDataSubSet, $filename, json_encode($sysValue));
        return $success;
    }

    // Retorn --> JSON
    public function getMeta($idResource, $projectType, $metaDataSubSet, $filename) {

        $metaDataPath = WikiGlobalConfig::getConf('mdprojects');
        $idResourceArray = explode(':', $idResource);
        $idResoucePath = implode("/", $idResourceArray);
        $metaDataReturn = null;
        // Get content file and return metaData included in $metaDataSubSet
        $contentFile = @file_get_contents("$metaDataPath/$idResoucePath/$projectType/$filename");
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
        $idResoucePath = str_replace(':', "/", $idResource);
        $dir = "$metaDataPath/$idResoucePath/$projectType";

        if (is_file("$dir/$filename")) {
            $contentFile = file_get_contents("$dir/$filename");
            if ($contentFile != false) {
                $contentFileArray = json_decode($contentFile, true);
                if ($contentFileArray[$metaDataSubSet]) {
                    $contentFileArray[$metaDataSubSet] = json_decode($metaDataValue, true);
                    $resourceCreated = io_saveFile("$dir/$filename", json_encode($contentFileArray));
                }else {
                    $resourceCreated = '{"error":"5090"}';  //no existe $metaDataSubSet en el contenido del fichero
                }
            }
        }else {
            $resourceCreated = $this->_createResource($dir, $filename);
            if ($resourceCreated) {
                $metaDataValue = $this->_setSystemData($metaDataSubSet, $metaDataValue, $dir);
                $resourceCreated = io_saveFile("$dir/$filename", $metaDataValue);
            }else {
                $resourceCreated = '{"error":"5090"}';
            }
        }
        return $resourceCreated;
    }
    
    private function _createResource($dir, $file) {
        $resourceCreated = is_dir($dir);
        if (!$resourceCreated) {
            //Crea, si no existe, la estructura de directorios en 'mdprojects'
            $resourceCreated = mkdir($dir, 0777, true);
        }
        if ($resourceCreated) {
            // Crea y verifica el fichero .mdpr que contendrá los datos del proyecto
            if (($fp = @fopen("$dir/$file", 'w')) !== false) {
                fclose($fp);
                $resourceCreated = true;
            }
        }
        return $resourceCreated;
    }
    
    private function _setSystemData($metaDataSubSet, $metaDataValue, $dir) {
        //Crea el fichero de sistema del proyecto
        $data = '{"system":{"generated":false}}';
        $file = WikiGlobalConfig::getConf('projects','wikiiocmodel')['dataSystem'];
        io_saveFile("$dir/$file", $data);
        //Retorna el array json contruido a partir del subset y su array de valores
        return "{\"$metaDataSubSet\":$metaDataValue}";
    }

    public function getFileName($id, $especParams=NULL) {
        $filename = $this->getProjectFileName($especParams);
        $rawid = wikiFN($id);
        return "$rawid/${especParams['projectType']}/$filename";
    }
    
    public function getProjectFileName($parms) {
        $jsonArray = $this->getMetaDataConfig($parms['projectType'], $parms['metaDataSubSet'], self::CONFIGUSUBSETSTRUCTURE);
        $data = json_decode($jsonArray, true);
        return $data[$parms['metaDataSubSet']];
    }

    public function getNsTree($currentNode, $sortBy, $onlyDirs = FALSE, $expandProjects = TRUE, $hiddenProjects=FALSE, $root=FALSE) {

        $base = WikiGlobalConfig::getConf('datadir');
        return $this->getNsTreeFromGenericSearch($base, $currentNode, $sortBy, $onlyDirs, 'search_universal', $expandProjects, $hiddenProjects, $root);
    }

    public function createDataDir($id) {
        $id = str_replace(':', '/', $id);
        $dir = WikiGlobalConfig::getConf('datadir') . '/' . utf8_encodeFN($id) . "/dummy";
        $this->makeFileDir($dir);
    }
    
    /**
     * Obtiene el array con los datos del proyecto
     * @return array 
     */
    public function getDataProject($idResource, $projectType) {
        $metaDataSubSet = "main";
        $filename = $this->getProjectFileName(array('projectType'=>$projectType, 'metaDataSubSet'=>$metaDataSubSet));
        $jArr = $this->getMeta($idResource, $projectType, $metaDataSubSet, $filename);
        $data = json_decode($jArr, true);
        return $data;
    }
    
}
