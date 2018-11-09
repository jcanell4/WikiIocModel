<?php
/**
 * Description of ProjectMetaDataQuery
 * @author josep et al.
 */
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC."lib/plugins/");
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_PLUGIN."wikiiocmodel/");

require_once (DOKU_INC."inc/JSON.php");
require_once (DOKU_PLUGIN."ajaxcommand/defkeys/ProjectKeys.php");
require_once (WIKI_IOC_MODEL."persistence/DataQuery.php");

class ProjectMetaDataQuery extends DataQuery {

    const PATH_METADATA_CONFIG = "metadata/config/";
    const FILE_CONFIGMAIN      = "configMain.json";
    const FILE_DEFAULTVIEW     = "defaultView.json";
    const DEFAULT_PROJECT_TYPE_DIR = WIKI_IOC_MODEL . "projects/defaultProject/";

    const LOG_TYPE_CREATE     = 'C';
    const LOG_TYPE_EDIT       = 'E';
    const LOG_TYPE_MINOR_EDIT = 'e';
    const LOG_TYPE_DELETE     = 'D';
    const LOG_TYPE_REVERT     = 'R';

    private $projectFileName = NULL;    //Nom de l'arxiu de dades del projecte
    private $projectFilePath = NULL;    //Ruta completa al directori del projecte

    public function getListMetaDataComponentTypes($projectType, $metaDataPrincipal, $subset, $component, $projectTypeDir=NULL) {
        if (!$projectTypeDir) {
            $projectTypeDir = $this->getProjectTypeDir($projectType);
        }
        //lista de elementos permitidos para el componente dado
        $jsonList = $this->getMetaDataConfig($projectType, $metaDataPrincipal, $subset, $projectTypeDir);
        if (!empty($jsonList)) {
            $arrayList = json_decode($jsonList, true);
            return $arrayList[$subset][$component];
        }else {
            return NULL;
        }
    }

    /**
     * Devuelve la lista ordenada de tipos de proyecto obtenida a partir de la lectura
     * de la estructura de directorios de 'plugin'/projects/
     */
    public function getListProjectTypes($projectType=NULL, $subset=NULL, $projectTypeDir=NULL) {
        global $plugin_controller;
        if ($projectType) {
            if (!$subset) $subset = ProjectKeys::VAL_DEFAULTSUBSET;
            $listProjects = $this->getListMetaDataComponentTypes($projectType,
                                                                 ProjectKeys::KEY_METADATA_COMPONENT_TYPES,
                                                                 $subset,
                                                                 ProjectKeys::KEY_MD_CT_SUBPROJECTS,
                                                                 $projectTypeDir);
        }
        $plugin_list = $plugin_controller->getList('action');
        foreach ($plugin_list as $plugin) {
            $pluginProjectsDir = DOKU_PLUGIN."$plugin/projects/";
            if (($projectsDir = @opendir($pluginProjectsDir))) {
                while ($pType = readdir($projectsDir)) {
                    if (is_dir($pluginProjectsDir.$pType) && $pType !== '.' && $pType !== '..') {
                        if ($listProjects) {
                            if (in_array($pType, $listProjects)) {
                                $ret[] = $pType;
                            }
                        }else {
                            $ret[] = $pType;
                        }
                    }
                }
            }
        }
        if ($ret) sort($ret);
        return $ret;
    }

    /**
     * Devuelve una lista ordenada con los tipos de proyecto contenidos en el plugin
     */
    public function getPluginProjectTypes($plugin) {
        $pluginProjectsDir = DOKU_PLUGIN."$plugin/projects/";
        if (($projectsDir = @opendir($pluginProjectsDir))) {
            while ($pType = readdir($projectsDir)) {
                if (is_dir($pluginProjectsDir.$pType) && $pType !== '.' && $pType !== '..') {
                    $ret[] = $pType;
                }
            }
        }
        if ($ret) sort($ret);
        return $ret;
    }

    /**
     * AHORA MISMO NO LA USA NADIE
     * Devuelve un array de tipos de proyecto contenidos en el plugin
     */
    public function getArrayProjectTypes($plugin) {
        $pluginProjectsDir = DOKU_PLUGIN."$plugin/projects/";
        if (($projectsDir = @opendir($pluginProjectsDir))) {
            while ($pType = readdir($projectsDir)) {
                if (is_dir($pluginProjectsDir.$pType) && $pType !== '.' && $pType !== '..') {
                    $ret[] = ['project' => $pType,
                              'dir' => $pluginProjectsDir.$pType."/"];
                }
            }
        }
        return $ret;
    }

    /**
     * AHORA MISMO NO LA USA NADIE
     * Devuelve un array de tipos de proyecto obtenido a partir de la lectura
     * de la estructura de directorios de 'plugin'/projects/
     */
    public function getAllArrayProjectTypes() {
        global $plugin_controller;
        $plugin_list = $plugin_controller->getList('action');
        foreach ($plugin_list as $plugin) {
            $pluginProjectsDir = DOKU_PLUGIN."$plugin/projects/";
            if (($projectsDir = @opendir($pluginProjectsDir))) {
                while ($pType = readdir($projectsDir)) {
                    if (is_dir($pluginProjectsDir.$pType) && $pType !== '.' && $pType !== '..') {
                        $ret[] = ['plugin' => $plugin,
                                  'project' => $pType,
                                  'dir' => $pluginProjectsDir.$pType."/"];
                    }
                }
            }
        }
        return $ret;
    }

    /**
     * Busca si la ruta (ns) es un proyecto
     * @param string $ns
     * @return boolean
     */
    public function existProject($ns) {
        return $this->isAProject($ns);
    }

    /**
     * Obtiene el array correspondiente a la clave $configAttribute del archivo FILE_CONFIGMAIN
     * @param string $projectType
     * @param string $configAttribute : attributo 'metaData' principal
     * @param string $metaDataSubset : nombre del subconjunto requerido
     * @param string $projectTypeDir : directorio del tipo de proyecto
     * @return Json con el array correspondiente a la clave $configAttribute del archivo FILE_CONFIGMAIN
     */
    public function getMetaDataConfig($projectType, $configAttribute, $metaDataSubset, $projectTypeDir=NULL) {
        if (!$projectTypeDir) $projectTypeDir = $this->getProjectTypeDir($projectType);
        $path = $projectTypeDir . self::PATH_METADATA_CONFIG . self::FILE_CONFIGMAIN;
        $configMain = @file_get_contents($path);
        if ($configMain == false) {
            $configMain = @file_get_contents(self::DEFAULT_PROJECT_TYPE_DIR . self::PATH_METADATA_CONFIG . self::FILE_CONFIGMAIN);
        }

        $configArray = json_decode($configMain, true);
        $toReturn = "";
        $encoder = new JSON();

        for ($i = 0; $i < sizeof($configArray[$configAttribute]); $i++) {
            if (isset($configArray[$configAttribute][$i][$metaDataSubset])) {
                $toReturn = $encoder->encode($configArray[$configAttribute][$i]);
            } else if (isset($configArray[$configAttribute][$i][ProjectKeys::KEY_DEFAULTSUBSET])) {
                if ($toReturn == "") {
                    $toReturn = $encoder->encode($configArray[$configAttribute][$i]);
                }
            }
        }
        return $toReturn;
    }

    public function getMetaViewConfig($projectType, $viewConfig, $projectTypeDir=NULL) {
        if (!$projectTypeDir) $projectTypeDir = $this->getProjectTypeDir($projectType);
        $view = @file_get_contents($projectTypeDir . self::PATH_METADATA_CONFIG . "$viewConfig.json");
        if ($view == false) {
            $view = @file_get_contents($projectTypeDir . self::PATH_METADATA_CONFIG . self::FILE_DEFAULTVIEW);
            if ($view == false) {
                $view = @file_get_contents(self::DEFAULT_PROJECT_TYPE_DIR . self::PATH_METADATA_CONFIG . self::FILE_DEFAULTVIEW);
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
                $returnArray[$arrayElement['id']] = $arrayElement[ProjectKeys::KEY_PROJECT_TYPE];
            }
        }

        // Add the $nsRoot itself, if it's a project (only a type of project)
        $metaDataPath = WikiGlobalConfig::getConf('mdprojects');
        $metaDataExtension = WikiGlobalConfig::getConf('mdextension');
        $pathProject = $metaDataPath . '/'. str_replace(':', '/', $nsRoot);
        $dirProject = @opendir($pathProject);

        if ($dirProject) {  //En el proceso de creación de un proyecto, no existe, todavía el directorio del proyecto
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
        }

        if (sizeof($returnArray) > 0) {
            $encoder = new JSON();
            $toReturn = $encoder->encode($returnArray);
        } else {
            return NULL;
        }

        return $toReturn;
    }

    /**
     * Devuelve el estado de generación del proyecto
     * @return boolean : true si el proyecto ya ha sido generado
     */
    public function isProjectGenerated($idProject, $projectType) {
        $filename = WikiGlobalConfig::getConf('projects','wikiiocmodel')['dataSystem'];
        $jsonArr = $this->getMeta($idProject, $projectType, "state", $filename);
        $data = json_decode($jsonArr, true);
        return $data['generated'];
    }

    /**
     * Establece el estado 'generated'=true del proyecto
     * @return boolean : true si el estado del proyecto se ha establecido con éxito
     */
    public function setProjectGenerated($idProject, $projectType) {
        $projectSystemDataFile = WikiGlobalConfig::getConf('projects','wikiiocmodel')['dataSystem'];
        $metaDataSubSet = "state";
        $jSysArr = $this->getMeta($idProject, $projectType, $metaDataSubSet, $projectSystemDataFile);
        $sysValue = json_decode($jSysArr, true);
        $sysValue['generated'] = true;
        $success = $this->setMeta($idProject, $projectType, $metaDataSubSet, $projectSystemDataFile, json_encode($sysValue));
        return $success;
    }

    /**
     * Extrae, del contenido del fichero, los datos correspondientes a la clave
     * @param string $idProject : wikiRuta del proyecto
     * @param string $projectType : tipo de proyecto
     * @param string $metaDataSubSet : clave del contenido
     * @param string $filename : fichero de datos del proyecto / ruta completa para las revisiones
     * @return JSON conteniendo el array de la clave 'metadatasubset' con los datos del proyecto
     */
    public function getMeta($idProject, $projectType, $metaDataSubSet, $filename) {
        $metaDataReturn = null;

        if (substr($idProject, -5) === ProjectKeys::REVISION_SUFFIX) {
            $contentFile = io_readFile($filename, false);
        }else {
            $idResoucePath = WikiGlobalConfig::getConf('mdprojects')."/".str_replace(":", "/", $idProject);
            $contentFile = @file_get_contents("$idResoucePath/$projectType/$filename");
        }

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

    /**
     * Guarda el nuevo archivo de datos del proyecto, guardando previamente la versión anterior como una revisión
     * @param string $id              ns del proyecto (ruta relativa del projecte, a partir de 'data/mdproject/')
     * @param string $projectType     tipus de projete (exemples: "defaultProject", "documentation")
     * @param string $metaDataSubSet  Valor de metadatasubset (exemple: "main")
     * @param string $projectFileName Nom de l'arxiu de dades del projecte (exemple: "meta.mdpr")
     * @param JSON   $metaDataValue   Nou contingut de l'arxiu de dades del projecte
     * @return string
     */
    public function setMeta($id, $projectType, $metaDataSubSet, $projectFileName, $metaDataValue) {
        $projectId = str_replace(':', "/", $id);
        $this->projectFileName = $projectFileName;
        $this->projectFilePath = WikiGlobalConfig::getConf('mdprojects')."/$projectId/$projectType/";
        $projectFilePathName = $this->projectFilePath . $this->projectFileName;

        if (is_file($projectFilePathName)) {
            $old_contentFile = file_get_contents($projectFilePathName);
            //Aquí, ya existe, como mínimo, una versión previa de los archivos del proyecto
            if ($old_contentFile != false) {
                $contentFileArray = json_decode($old_contentFile, true);
                if ($contentFileArray[$metaDataSubSet]) {
                    $prev_date = filemtime($projectFilePathName);
                    $contentFileArray[$metaDataSubSet] = json_decode($metaDataValue, true);
                    $resourceCreated = io_saveFile($projectFilePathName, str_replace("\\r\\n", "\\n", json_encode($contentFileArray)));
                    //Guardamos el archivo existente (la versión previa) como revisión
                    $dateRevision = $this->_saveRevision($prev_date, $projectId, $old_contentFile);
                }else {
                    $resourceCreated = '{"error":"5090"}';  //no existe $metaDataSubSet en el fichero
                }
            }
        }else {
            $resourceCreated = $this->_createResource($this->projectFilePath, $this->projectFileName);
            if ($resourceCreated) {
                $resourceCreated = $this->_setSystemData($id, $this->projectFilePath);
                if ($resourceCreated) {
                    $resourceCreated = io_saveFile($projectFilePathName, "{\"$metaDataSubSet\":$metaDataValue}");
                }
            }
            if (!$resourceCreated) {
                $resourceCreated = '{"error":"5090"}';
            }
        }
        return $resourceCreated;
    }

    private function _createResource($dirProject, $file) {
        $resourceCreated = is_dir($dirProject);
        if (!$resourceCreated) {
            //Crea, si no existe, la estructura de directorios en 'mdprojects'
            $resourceCreated = mkdir($dirProject, 0777, true);
        }
        if ($resourceCreated) {
            // Crea y verifica el fichero .mdpr que contendrá los datos del proyecto
            $fp = @fopen("$dirProject/$file", 'w');
            if ($resourceCreated = ($fp !== false)) {
                fclose($fp);
            }
        }
        return $resourceCreated;
    }

    /**
     * Crea el archivo de sistema del proyecto y guarda datos de estado
     * @param string $id (ruta ns del proyecto)
     * @param string $dirProject
     * @return boolean : indica si la creación del fichero ha tenido éxito
     */
    private function _setSystemData($id, $dirProject) {
        //Crea el fichero de sistema del proyecto
        $parentProject = $this->getThisProject($id)['nsproject'];
        $state = ['generated' => false];
        if ($parentProject && $parentProject !== $id) {
            $state['parentNs'] = $parentProject;
        }
        $data['state'] = $state;
        $file = WikiGlobalConfig::getConf('projects','wikiiocmodel')['dataSystem'];
        $succes = io_saveFile("$dirProject/$file", json_encode($data));
        return $succes;
    }

    /**
     * Devuelve la ruta completa al fichero del proyecto (en mdprojects)
     * @param string $id : wikiRuta de la página del proyecto
     * @param array $params : {projectType, metaDataSubSet, projectfilename}
     * @return string
     */
    public function getFileName($id, $params=array()) {
        if ($id && !empty($params)) {
            $filename = ($params[ProjectKeys::KEY_PROJECT_FILENAME]) ? $params[ProjectKeys::KEY_PROJECT_FILENAME] : $this->_getProjectFileName($params);
            $params['id'] = $id;
            $ret = $this->getProjectFilePath($params) . $filename;
        }else {
            $ret = $this->getProjectFilePath() . $this->_getProjectFileName();
        }
        return $ret;
    }

    /**
     * Devuelve el nombre del archivo de datos para este tipo de proyecto
     * @params array [ProjectKeys::KEY_PROJECT_TYPE,
     *                ProjectKeys::KEY_METADATA_SUBSET,
     *                ProjectKeys::KEY_PROJECTTYPE_DIR]
     * @return string el nombre del fichero de datos del tipo de proyecto solicitado
     */
    public function getProjectFileName($parms) {
        $jsonArray = $this->getMetaDataConfig($parms[ProjectKeys::KEY_PROJECT_TYPE],
                                              ProjectKeys::KEY_METADATA_PROJECT_STRUCTURE,
                                              $parms[ProjectKeys::KEY_METADATA_SUBSET],
                                              $parms[ProjectKeys::KEY_PROJECTTYPE_DIR]
                                            );
        $data = json_decode($jsonArray, true);
        return $data[$parms[ProjectKeys::KEY_METADATA_SUBSET]];
    }

    /**
     * Devuelve el nombre del archivo de datos para este tipo de proyecto
     * Si recibe parámetros, guarda el valor en la variable privada $this->projectFileName
     * @params array [ProjectKeys::KEY_PROJECT_TYPE,
     *                ProjectKeys::KEY_METADATA_SUBSET,
     *                ProjectKeys::KEY_PROJECTTYPE_DIR]
     */
    private function _getProjectFileName($parms=[]) {
        if (!empty($parms)) {
            $this->projectFileName = $this->getProjectFileName($parms);
        }
        return $this->projectFileName;
    }

    private function setProjectFilePath(array $parms) {
        if (empty($parms) || $parms===NULL) {
            throw new Exception("Manquen paràmetres a la funció setProjectFilePath(array) de ProjectMetaDataQuery");
        }
        $this->projectFilePath = WikiGlobalConfig::getConf('mdprojects') . "/"
                                    . str_replace(":", "/", $parms[ProjectKeys::KEY_ID]) . "/"
                                    . $parms[ProjectKeys::KEY_PROJECT_TYPE] . "/";
    }

    public function getProjectFilePath($parms=[]) {
        if (!$this->projectFilePath && $parms[ProjectKeys::KEY_ID]) {
            $this->setProjectFilePath($parms);
        }
        return $this->projectFilePath;
    }

    public function getProjectTypeDir($projectType){
//        return (class_exists('DokuModelManager', FALSE)) ? DokuModelManager::getProjectTypeDir() : WIKI_IOC_MODEL . "projects/$projectType/";
        global $plugin_controller;
        return $plugin_controller->getProjectTypeDir($projectType);
    }

    public function getNsTree($currentNode, $sortBy, $onlyDirs=FALSE, $expandProjects=TRUE, $hiddenProjects=FALSE, $root=FALSE) {
        $base = WikiGlobalConfig::getConf('datadir');
        return $this->getNsTreeFromGenericSearch($base, $currentNode, $sortBy, $onlyDirs, 'search_universal', $expandProjects, $hiddenProjects, $root);
    }

    public function createDataDir($id) {
        $id = str_replace(":", "/", $id);
        $dir = WikiGlobalConfig::getConf('datadir') . "/" . utf8_encodeFN($id) . "/dummy";
        $this->makeFileDir($dir);
    }

    /**
     * @param boolean parameters['extra'] : TRUE | FALSE
     * @return array Contiene los datos del proyecto correspondientes a la clave '$metaDataSubSet'
     */
    public function getDataProject($parameters) {
        $metaDataSubSet = ($parameters['metaDataSubSet']) ? $parameters['metaDataSubSet'] : ProjectKeys::VAL_DEFAULTSUBSET;
        $parms = [ProjectKeys::KEY_ID => $parameters[ProjectKeys::KEY_ID],
                  ProjectKeys::KEY_PROJECT_TYPE => $parameters[ProjectKeys::KEY_PROJECT_TYPE],
                  ProjectKeys::KEY_PROJECTTYPE_DIR => $parameters[ProjectKeys::KEY_PROJECTTYPE_DIR],
                  ProjectKeys::KEY_METADATA_SUBSET => $metaDataSubSet
                 ];
        $filename = $this->_getProjectFileName($parms);
        $jsonData = $this->getMeta($parameters[ProjectKeys::KEY_ID], $parameters[ProjectKeys::KEY_PROJECT_TYPE], $metaDataSubSet, $filename);
        $data = json_decode($jsonData, true);
        $data[ProjectKeys::KEY_PROJECTTYPE_DIR] = $parameters[ProjectKeys::KEY_PROJECTTYPE_DIR];
        if ($parameters['extra']) {
            $data[ProjectKeys::KEY_PROJECT_FILENAME] = $parms[ProjectKeys::KEY_PROJECT_FILENAME] = $filename;
            $data[ProjectKeys::KEY_PROJECT_FILEPATH] = $this->getFileName($parameters[ProjectKeys::KEY_ID], $parms);
        }
        return $data;
    }

    private function _saveRevision($prev_date, $projectId, $old_content) {
        $resourceCreated = FALSE;
        $projectFilePathName = $this->projectFilePath . $this->projectFileName;

        if (@file_exists($projectFilePathName)) {
            $mdate = filemtime($projectFilePathName);
            $new_rev_file = $this->_revisionProjectFN($projectId, "{$this->projectFileName}.$mdate", ".txt");
            $resourceCreated = io_saveFile("$new_rev_file.gz", $old_content);

            $last_rev_date = $this->getProjectRevisionList($projectId, $this->projectFileName, 1)[0]['date'];
            if ($last_rev_date && $last_rev_date < $prev_date) {
                $summary = WikiIocLangManager::getLang('external_edit');
                $flags = array('ExternalEdit'=> true);
            }
            $resourceCreated &= $this->_addProjectLogEntry($mdate, $projectId, self::LOG_TYPE_EDIT, $summary, "", $flags);
        }
        return ($resourceCreated) ? $mdate : "";
    }

    /**
     * Logs del proceso de guardar una modificación del archivo de datos del proyecto.
     * @param string $mdate     fecha de última modificación del archivo de datos del proyecto
     * @param string $projectId ruta relativa del proyecto
     * @param string $type      tipo de modificación
     * @param string $summary
     * @param string $extra
     * @param array $flags
     * @return boolean
     */
    private function _addProjectLogEntry($mdate, $projectId, $type=self::LOG_TYPE_EDIT, $summary="", $extra="", $flags=NULL) {
        $strip  = array("\t", "\n");
        if (is_array($flags))
            $flagExternalEdit = isset($flags['ExternalEdit']);
        $record = array(
            'date'  => $mdate,
            'ip'    => (!$flagExternalEdit) ? clientIP(true) : "127.0.0.1",
            'type'  => str_replace($strip, "", $type),
            'id'    => str_replace("/", ":", $projectId),
            'user'  => (!$flagExternalEdit) ? $_SERVER['REMOTE_USER'] : "",
            'sum'   => utf8_substr(str_replace($strip, "", $summary), 0, 255),
            'extra' => str_replace($strip, "", $extra)
            );

        //meta log
        $ret = $this->_addLogMetaFile($projectId, $record );

        //changes log
        $ret &= $this->_addLogChangesFile($projectId, $record);

        return $ret;
    }

    /**
     * En este log se guarda una línea por cada modificación sufrida por el archivo de datos del proyecto
     * @param string $projectId ruta relativa del proyecto
     * @param array  $record    datos del registro de log
     * @return boolean
     */
    private function _addLogChangesFile($projectId, $record) {
        $ret = TRUE;
        $record_line = implode("\t", $record)."\n";
        $ch_filename = $this->_metaProjectFN($projectId, "", ".changes");

        $fh = @fopen($ch_filename, "r");
        if ($fh) {
            $fh2 = @fopen("$ch_filename.tmp", "w");
            $bytes = fwrite($fh2, $record_line);
            while (!feof($fh)) {
                fwrite($fh2, fgets($fh));
            }
            fclose($fh2);
            fclose($fh);
            $ret &= rename("$ch_filename.tmp", $ch_filename);
        }else {
            $fh = @fopen($ch_filename, "w");
            $bytes = fwrite($fh, $record_line);
            fclose($fh);
        }
        $ret &= ($bytes === strlen($record_line));
        return $ret;
    }

    /**
     * Log del proceso de guardar una modificación del archivo de datos del proyecto. Es el log que se guarda en
     * el archivo projectId/projectFilename.meta. Este archivo log contiene un JSON de metadatos del proyecto
     * @param string $projectId ruta relativa del proyecto
     * @param array  $record    datos del registro de log
     * @return boolean
     */
    private function _addLogMetaFile($projectId, $record) {
        $projectFilePathName = $this->projectFilePath . $this->projectFileName;
        $minor = ($record['type'] === self::LOG_TYPE_MINOR_EDIT);
        $user   = $record['user'];
        $created = @filectime($projectFilePathName);

        $old_meta = $this->p_read_projectmetadata($projectId);
        $new_meta = array();
        if (!WikiIocInfoManager::getInfo('exists')) {
            if (empty($old_meta['persistent']['date']['created'])) { //newly created
                $new_meta['date']['created'] = $created;
                if ($user){
                    $new_meta['creator'] = WikiIocInfoManager::getInfo('userinfo')['name'];
                    $new_meta['user']    = $user;
                }
            } elseif (!empty($old_meta['persistent']['date']['created'])) { //re-created / restored
                $new_meta['date']['created']  = $old_meta['persistent']['date']['created'];
                $new_meta['date']['modified'] = $created; // use the files ctime here
                $new_meta['creator'] = $old_meta['persistent']['creator'];
                if ($user) $new_meta['contributor'][$user] = WikiIocInfoManager::getInfo('userinfo')['name'];
            }
        } elseif (!$minor) {
            $new_meta['date']['modified'] = $record['date'];
            if ($user) $new_meta['contributor'][$user] = WikiIocInfoManager::getInfo('userinfo')['name'];
        }
        $new_meta['last_change'] = $record;
        $ret = $this->p_set_projectmetadata($projectId, $new_meta);
        return $ret;
    }

    private function _revisionProjectFN($projectId, $filename="", $ext="") {
        if ($filename==="" && $this->projectFileName) {
            $filename = $this->projectFileName;
        }
        $dir = $this->revisionProjectDir($projectId) . "$filename$ext";
        return $dir;
    }

    public function revisionProjectDir($projectId) {
        $projectId = utf8_encodeFN(str_replace(":", "/", $projectId));
        $dir = WikiGlobalConfig::getConf('revisionprojectdir') . "/$projectId/";
        return $dir;
    }

    private function _metaProjectFN($projectId, $filename="", $ext="") {
        $projectId = utf8_encodeFN(str_replace(":", "/", $projectId));
        if ($filename==="") {
            $filename = $this->_getProjectFileName();
        }
        $file = WikiGlobalConfig::getConf('metaprojectdir') . "/$projectId/$filename$ext";
        return $file;
    }

    private function p_set_projectmetadata($projectId, $data){
        if (!is_array($data))
            return false;

        $meta = $orig = $this->p_read_projectmetadata($projectId);
        $protected = array('description', 'date', 'contributor');

        foreach ($data as $key => $value){
            if ($key == 'relation'){
                foreach ($value as $subkey => $subvalue){
                    if (isset($meta['current'][$key][$subkey]) && is_array($meta['current'][$key][$subkey])) {
                        $meta['current'][$key][$subkey] = array_merge($meta['current'][$key][$subkey], (array)$subvalue);
                    }else {
                        $meta['current'][$key][$subkey] = $subvalue;
                    }

                    if (isset($meta['persistent'][$key][$subkey]) && is_array($meta['persistent'][$key][$subkey])) {
                        $meta['persistent'][$key][$subkey] = array_merge($meta['persistent'][$key][$subkey], (array)$subvalue);
                    }else {
                        $meta['persistent'][$key][$subkey] = $subvalue;
                    }
                }
            }elseif (in_array($key, $protected)){
                // these keys, must have subkeys - a legitimate value must be an array
                if (is_array($value)) {
                    $meta['current'][$key] = !empty($meta['current'][$key]) ? array_merge((array)$meta['current'][$key],$value) : $value;
                    $meta['persistent'][$key] = !empty($meta['persistent'][$key]) ? array_merge((array)$meta['persistent'][$key],$value) : $value;
                }
            }else {
                $meta['current'][$key] = $value;
                $meta['persistent'][$key] = $value;
            }
        }

        // save only if metadata changed
        if ($meta == $orig)
            return true;
        else
            return $this->p_save_projectmetadata($projectId, $meta);
    }

    private function p_read_projectmetadata($idProject, $filename="") {
        $meta_file = $this->_metaProjectFN($idProject, $filename, ".meta");
        if (@file_exists($meta_file))
            $meta = unserialize(io_readFile($meta_file, false));
        else
            $meta = array('current' => array(), 'persistent' => array());
        return $meta;
    }

    private function p_save_projectmetadata($idProject, $meta) {
        return io_saveFile($this->_metaProjectFN($idProject, "", ".meta"), serialize($meta));
    }

    /**
     * Retorna un array con las líneas del archivo de log .changes
     * @param string $projectId
     * @param int    $num        Número de registros solicitados
     * @param int    $chunk_size Máximo número de bytes que van a leerse del fichero de log
     * @return array
     */
    public function getProjectRevisionList($projectId, $projectFileName, $num=1, $chunk_size=1024) {
        $revs = array();
        $file = $this->_metaProjectFN($projectId, $projectFileName, ".changes");

        if (@file_exists($file)) {
            if (filesize($file) < $chunk_size || $num==0 || $chunk_size==0) {
                $lines = file($file);
                if ($num==0 || $chunk_size==0) $num = count($lines);
            }else {
                $fh = fopen($file, 'r');
                if ($fh) {
                    $lines[] = fgets($fh, $chunk_size);
                    $count = floor($chunk_size / strlen($lines[0]));
                    $i = 1;
                    while (!feof($fh) && $i < $count) {
                        $lines[] = fgets($fh);
                        $i++;
                    }
                    fclose($fh);
                }
            }
            for ($i=0; $i<$num; $i++) {
                if (!empty(trim($lines[$i]))) {
                    $registre = explode("\t", $lines[$i]);
                    $revs[$registre[0]]['date'] = date("d-m-Y h:i:s", $registre[0]);
                    $revs[$registre[0]]['ip']   = $registre[1];
                    $revs[$registre[0]]['type'] = $registre[2];
                    $revs[$registre[0]]['id']   = $registre[3];
                    $revs[$registre[0]]['user'] = $registre[4];
                    $revs[$registre[0]]['sum']  = trim($registre[5]);
                    $revs[$registre[0]]['extra']= trim($registre[6]);
                }
            }
        }
        return $revs;
    }

    public function getLastModFileDate($id) {
        $fn = $this->getFileName($id);
        if (@file_exists($fn)) {
            return filemtime($fn);
        }
    }
}
