<?php
/**
 * Description of ProjectMetaDataQuery
 * @author josep et al.
 */
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC.'lib/plugins/');
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_PLUGIN.'wikiiocmodel/');
if (!defined('WIKI_IOC_PROJECTS')) define('WIKI_IOC_PROJECTS', WIKI_IOC_MODEL.'projects/');

require_once (DOKU_INC.'inc/JSON.php');
require_once (DOKU_PLUGIN.'ownInit/WikiGlobalConfig.php');
require_once (DOKU_PLUGIN.'ajaxcommand/defkeys/ProjectKeys.php');
require_once (WIKI_IOC_MODEL.'persistence/DataQuery.php');

class ProjectMetaDataQuery extends DataQuery {

    const K_CONFIGUSUBSETSTRUCTURE = ProjectKeys::KEY_METADATA_PROJECT_STRUCTURE;
    const K_METADATASUBSET         = ProjectKeys::KEY_METADATA_SUBSET;
    const K_DEFAULTSUBSET          = ProjectKeys::KEY_DEFAULTSUBSET;
    const K_PROJECT_FILENAME       = ProjectKeys::KEY_PROJECT_FILENAME;
    const K_PROJECT_FILEPATH       = ProjectKeys::KEY_PROJECT_FILEPATH;

    const PATH_METADATA_CONFIG     = "/metadata/config/";
    const FILE_CONFIGMAIN          = "configMain.json";
    const FILE_DEFAULTVIEW         = "defaultView.json";

    /**
     * Devuelve la lista ordenada de tipos de proyecto obtenida a partir de la lectura
     * de la estructura de directorios de wikiiocmodel/projects/
     */
    public function getListProjectTypes() {
        $projectsDir = opendir(WIKI_IOC_PROJECTS);
        while ($projType = readdir($projectsDir)) {
            if (is_dir(WIKI_IOC_PROJECTS.$projType) && $projType !== '.' && $projType !== '..') {
                $ret[] = $projType;
            }
        }
        if ($ret) sort($ret);
        return $ret;
    }

    /**
     * Obtiene el array correspondiente a la clave $configSubSet del archivo FILE_CONFIGMAIN
     * @param string $projectType
     * @param string $metaDataSubset
     * @param string $configSubSet
     * @return Json con el array correspondiente a la clave $configSubSet del archivo FILE_CONFIGMAIN
     */
    public function getMetaDataConfig($projectType, $metaDataSubset, $configSubSet) {

        $configMain = @file_get_contents(WIKI_IOC_PROJECTS . $projectType . self::PATH_METADATA_CONFIG.self::FILE_CONFIGMAIN);
        if ($configMain == false) {
            $configMain = @file_get_contents(WIKI_IOC_PROJECTS . "defaultProject" . self::PATH_METADATA_CONFIG.self::FILE_CONFIGMAIN);
        }

        $configMainArray = json_decode($configMain, true);
        $toReturn = "";
        $encoder = new JSON();

        for ($i = 0; $i < sizeof($configMainArray[$configSubSet]); $i++) {
            if (isset($configMainArray[$configSubSet][$i][$metaDataSubset])) {
                $toReturn = $encoder->encode($configMainArray[$configSubSet][$i]);
            } else if (isset($configMainArray[$configSubSet][$i][self::K_DEFAULTSUBSET])) {
                if ($toReturn == "") {
                    $toReturn = $encoder->encode($configMainArray[$configSubSet][$i]);
                }
            }
        }
        return $toReturn;
    }

    public function getMetaViewConfig($projectType, $viewConfig) {

        $view = @file_get_contents(WIKI_IOC_PROJECTS.$projectType.self::PATH_METADATA_CONFIG."$viewConfig.json");
        if ($view == false) {
            $view = @file_get_contents(WIKI_IOC_PROJECTS.$projectType.self::PATH_METADATA_CONFIG.self::FILE_DEFAULTVIEW);
            if ($view == false) {
                $view = @file_get_contents(WIKI_IOC_PROJECTS."defaultProject".self::PATH_METADATA_CONFIG.self::FILE_DEFAULTVIEW);
            }
        }
        $viewArray = json_decode($view, true);
        return $viewArray;
    }

    //Retorn JSON {ns1:projectType1, …, nsm:projectTypem} Obtiene un array con las propiedades del nodo y sus hijos de 1er nivel.
    public function getMetaDataElementsKey($nsRoot) {

        $elementsKeyArray = $this->getNsTree($nsRoot, 0, true, false, false, false);

        $returnArray = array();
        foreach ($elementsKeyArray['children'] as $arrayElement) {
            if ($arrayElement['type'] == 'p') {
                $returnArray[$arrayElement['id']] = $arrayElement[self::K_PROJECTTYPE];
            }
        }

        // Add the $nsRoot itself, if it's a project (only a type of project)
        $metaDataPath = WikiGlobalConfig::getConf('mdprojects');
        $metaDataExtension = WikiGlobalConfig::getConf('mdextension');
        $pathProject = $metaDataPath . '/'. str_replace(':', '/', $nsRoot);
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

    /**
     * Extrae, del contenido del fichero, los datos correspondientes a la clave
     * @param string $idResource : wikiRuta del proyecto
     * @param string $projectType : tipo de proyecto
     * @param string $metaDataSubSet : clave del contenido
     * @param string $filename : fichero de datos del proyecto
     * @return JSON conteniendo el array de la clave 'metadatasubset' con los datos del proyecto
     */
    public function getMeta($idResource, $projectType, $metaDataSubSet, $filename) {
        $metaDataReturn = null;
        $idResoucePath = WikiGlobalConfig::getConf('mdprojects')."/".str_replace(":", "/", $idResource);
        $contentFile = @file_get_contents("$idResoucePath/$projectType/$filename");
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

        $dir = WikiGlobalConfig::getConf('mdprojects')."/".str_replace(':', "/", $idResource)."/$projectType";

        if (is_file("$dir/$filename")) {
            $contentFile = file_get_contents("$dir/$filename");
            if ($contentFile != false) {
                $contentFileArray = json_decode($contentFile, true);
                if ($contentFileArray[$metaDataSubSet]) {
                    $contentFileArray[$metaDataSubSet] = json_decode($metaDataValue, true);
                    $resourceCreated = io_saveFile("$dir/$filename", json_encode($contentFileArray));
                }else {
                    $resourceCreated = '{"error":"5090"}';  //no existe $metaDataSubSet en el fichero
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
        //Retorna el array json construido a partir del subset y su array de valores
        return "{\"$metaDataSubSet\":$metaDataValue}";
    }

    /** NO SE USA
     * Devuelve la ruta completa al fichero del proyecto (en mdprojects)
     * @param string $id : wikiRuta de la página del proyecto
     * @param array $params : {$projectType, $metaDataSubSet}
     * @return string
     */
    public function getFileName($id, $params=NULL) {
        $filename = $this->getProjectFileName($params);
        $dir = WikiGlobalConfig::getConf('mdprojects')."/".str_replace(':', "/", $id)."/${params[self::K_PROJECTTYPE]}";
        return "$dir/$filename";
    }

     /**
     * @params array(projectType, metadatasubset)
     * @return string el nombre del fichero de datos del proyecto del tipo solicitado
     */
    public function getProjectFileName($parms) {
        $jsonArray = $this->getMetaDataConfig($parms[self::K_PROJECTTYPE], $parms[self::K_METADATASUBSET], self::K_CONFIGUSUBSETSTRUCTURE);
        $data = json_decode($jsonArray, true);
        return $data[$parms[self::K_METADATASUBSET]];
    }

    public function getNsTree($currentNode, $sortBy, $onlyDirs=FALSE, $expandProjects=TRUE, $hiddenProjects=FALSE, $root=FALSE) {
        $base = WikiGlobalConfig::getConf('datadir');
        return $this->getNsTreeFromGenericSearch($base, $currentNode, $sortBy, $onlyDirs, 'search_universal', $expandProjects, $hiddenProjects, $root);
    }

    public function createDataDir($id) {
        $id = str_replace(':', '/', $id);
        $dir = WikiGlobalConfig::getConf('datadir') . '/' . utf8_encodeFN($id) . "/dummy";
        $this->makeFileDir($dir);
    }

    /**
     * @return array Contiene los datos del proyecto correspondientes a la clave '$metaDataSubSet'
     */
    public function getDataProject($idResource, $projectType) {
        $metaDataSubSet = ProjectKeys::VAL_DEFAULTSUBSET;   //clave del array que contiene los datos del proyecto
        $filename = $this->getProjectFileName(array(self::K_PROJECTTYPE=>$projectType, self::K_METADATASUBSET=>$metaDataSubSet));
        $jsonData = $this->getMeta($idResource, $projectType, $metaDataSubSet, $filename);
        $data = json_decode($jsonData, true);
        $data[self::K_PROJECT_FILENAME] = $filename;
        $data[self::K_PROJECT_FILEPATH] = $this->getFileName($idResource, array(self::K_PROJECTTYPE=>$projectType, self::K_METADATASUBSET=>$metaDataSubSet));
        return $data;
    }

}
