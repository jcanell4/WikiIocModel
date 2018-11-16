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

    private $projectId = NULL;    //Nom de l'arxiu de dades del projecte
    private $projectSubset = FALSE;    //Nom de l'arxiu de dades del projecte
    private $projectType = FALSE;    //Nom de l'arxiu de dades del projecte
    private $projectFileName = FALSE;    //Nom de l'arxiu de dades del projecte
    private $projectTypeDir = FALSE;    //Ruta completa al directori del projecte
    private $revision = FALSE;    //Ruta completa al directori del projecte
    
    public function __construct($projectId=FALSE, $projectSubset=FALSE, $projectType=FALSE, $revision=FALSE) {
        if($projectId || !$projectSubset || $projectType){
            $this->init($projectId, $projectSubset, $projectType);
        }
        $this->revision = $revision;
    }
    
    public function init($projectId, $projectSubset=FALSE, $projectType=FALSE, $revision=FALSE){
        $this->projectId = $projectId;
        $this->projectSubset = $projectSubset;
        $this->projectType= $projectType;
        $this->revision=$revision;
        $this->projectFileName = FALSE;
        $this->projectFilePath = FALSE;
        $this->projectTypeDir = FALSE;
        return $this;
    }
    
    public function setRevision($rev){
        $this->revision = $rev;
    }

    public function setProjectType($projectType){
        $this->projectType = $projectType;
    }

    public function setProjectSubset($projectSubset){
        $this->projectSubset = $projectSubset;
    }
    
    public function getRevision(){
        return $this->revision;
    }

    public function getProjectId(){
        return $this->projectId;
    }
    
    public function getProjectSubset(){
        if($this->projectSubset){
            $this->projectSubset = AjaxKeys::VAL_DEFAULTSUBSET;
        }
        return $this->projectSubset;
    }
    
    public function getProjectType(){
        if(!$this->projectType){
            //obtenir el projectType del directori
            $dir = WikiGlobalConfig::getConf('mdprojects')."/".str_replace(":", "/", $this->projectId.'/');
            $dirList = scandir($dir) ;
            $found = false;
            for($i=0; !$found && $i<count($dirList); $i++){
                if(is_dir($dirList[$i])){
                    if(preg_grep("/.*\.mdpr/", scandir($dir.$dirList[$i]))){
                        $this->projectType = $dirList[$i];
                        $found = true;
                    }
                }
            }
        }
        return  $this->projectType;
    }

    public function getListMetaDataComponentTypes($metaDataPrincipal, $component) {
        //lista de elementos permitidos para el componente dado
        $jsonList = $this->getMetaDataConfig($metaDataPrincipal);
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
    public function getListProjectTypes($all=FALSE) {
        global $plugin_controller;
        if (!$all) {
            if (!$subset) $subset = ProjectKeys::VAL_DEFAULTSUBSET;
            $listProjects = $this->getListMetaDataComponentTypes(ProjectKeys::KEY_METADATA_COMPONENT_TYPES,
                                                                 ProjectKeys::KEY_MD_CT_SUBPROJECTS);
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
    public function existProject() {
        return $this->isAProject($this->getProjectId());
    }

    /**
     * Obtiene el array correspondiente a la clave $configAttribute del archivo FILE_CONFIGMAIN
     * @param string $projectType       NOOO!
     * @param string $configAttribute : conjunto principal requerido
     * @param string $metaDataSubset : subconjunto requerido   NOOO!
     * @param string $projectTypeDir : directorio del tipo de proyecto  NOOOO!
     * @return Json con el array correspondiente a la clave $configAttribute del archivo FILE_CONFIGMAIN
     */
    public function getMetaDataConfig($configAttribute, $projectType=FALSE) {
        if(!$projectType){
            $projectType = $this->getProjectType();
        }
        $projectTypeDir = $this->getProjectTypeDir($projectType);
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
                break;
            } else if (isset($configArray[$configAttribute][$i][ProjectKeys::VAL_DEFAULTSUBSET])) {
                $toReturn = $encoder->encode($configArray[$configAttribute][$i]);
            }
        }
        return $toReturn;
    }

    /**
     * Obtiene un array con un conjunto de subSets, extraidos de la clave 'metaDataClassesNameSpaces', del archivo FILE_CONFIGMAIN
     * @param string $projectType
     * @param string $projectTypeDir : directorio del tipo de proyecto
     * @return array con la lista de subSets del archivo FILE_CONFIGMAIN
     */
    public function getListMetaDataSubSets($projectType=FALSE) {
        $configSet = ProjectKeys::KEY_METADATA_CLASSES_NAMESPACES;
        if(!$projectType){
            $projectType = $this->getProjectType();
        }
        $projectTypeDir = $this->getProjectTypeDir($projectType);
        $path = $projectTypeDir . self::PATH_METADATA_CONFIG . self::FILE_CONFIGMAIN;
        $configMain = @file_get_contents($path);
        if ($configMain == false) {
            $configMain = @file_get_contents(self::DEFAULT_PROJECT_TYPE_DIR . self::PATH_METADATA_CONFIG . self::FILE_CONFIGMAIN);
        }

        $configArray = json_decode($configMain, true);
        $toReturn = "";

        for ($i = 0; $i < sizeof($configArray[$configSet]); $i++) {
            $toReturn[] = array_keys($configArray[$configSet][$i])[0];
        }
        return $toReturn;
    }

    /**
     * Extrae el conjunto de campos definidos en la configuración de datos del tipo de proyecto
     * Se usa cuando todavía no hay datos en el fichero de proyecto, entonces se recoge la lista de campos del tipo de proyecto
     * @param string $projectType : tipo de proyecto
     * @param string $metaDataSubset : subconjunto requerido
     * @param string $projectTypeDir : ruta del proyecto
     * @return JSON conteniendo el conjunto de campos del subset
     */
    public function getStructureMetaDataConfig() {
        $metaStructure = $this->getMetaDataConfig(ProjectKeys::KEY_METADATA_PROJECT_STRUCTURE);

        if ($metaStructure) {
            $content = json_decode($metaStructure, TRUE);
            $typeDef = $content['mainType']['typeDef'];
            $keys = $content['typesDefinition'][$typeDef]['keys'];

            foreach ($keys as $k => $v) {
                $metaData[$k] = ($v['default']) ? $v['default'] : "";
            }
            $metaDataReturn = json_encode($metaData);
        }
        return $metaDataReturn;
    }

    public function getMetaViewConfig($viewConfig, $projectType=FALSE) {
        if(!$projectType){
            $projectType = $this->getProjectType();
        }
        $projectTypeDir = $this->getProjectTypeDir($projectType);
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
    public function isProjectGenerated() {
        $filename = WikiGlobalConfig::getConf('projects','wikiiocmodel')['dataSystem'];
        $jsonArr = $this->_getMeta($this->getProjectId(), $this->getProjectType(), "state", $filename);
        $data = json_decode($jsonArr, true);
        return $data['generated'];
    }

    /**
     * Establece el estado 'generated'=true del proyecto
     * @return boolean : true si el estado del proyecto se ha establecido con éxito
     */
public function setProjectGenerated() {
        $projectSystemDataFile = WikiGlobalConfig::getConf('projects','wikiiocmodel')['dataSystem'];
        $metaDataSubSet = "state";
        $jSysArr = $this->_getMeta($this->getProjectId(), $this->getProjectType(), $this->getProjectSubset(), $projectSystemDataFile);
        $sysValue = json_decode($jSysArr, true);
        $sysValue['generated'] = true;
        $success = $this->_setMeta($this->getProjectId(), $this->getProjectType(), $this->getProjectSubset(), $projectSystemDataFile, json_encode($sysValue));
        return $success;
    }

    /**
     * Extrae, del contenido del fichero, los datos correspondientes a la clave
     * @param string $idProject : wikiRuta del proyecto
     * @param string $projectType : tipo de proyecto
     * @param string $metaDataSubSet : clave del contenido
     * @return JSON conteniendo el array de la clave 'metadatasubset' con los datos del proyecto
     */
    public function getMeta($metadataSubset=FALSE, $revision=FALSE) {
        if(!$metadataSubset){
            $metadataSubset = $this->getProjectSubset();
        }
        return $this->_getMeta($metadataSubset, 
                                $this->getFileName($this->getProjectId(), 
                                                    array(ProjectKeys::KEY_REV => $revision, 
                                                          ProjectKeys::KEY_PROJECT_TYPE => $this->getProjectType(),
                                                          ProjectKeys::KEY_METADATA_SUBSET => $metadataSubset)));
    }
    /**
     * Extrae, del contenido del fichero, los datos correspondientes a la clave
     * @param string $metaDataSubSet : clave del contenido
     * @param string $filename : fichero de datos del proyecto / ruta completa para las revisiones 
     * @return JSON conteniendo el array de la clave 'metadatasubset' con los datos del proyecto
     */
    private function _getMeta($metaDataSubSet, $filename) {
        $metaDataReturn = null;
        $contentFile = io_readFile($filename, false);

        if ($contentFile != false) {
            $contentMainArray = json_decode($contentFile, true);
            foreach ($contentMainArray as $clave => $valor) {
                if ($clave == $metaDataSubSet) {
                    if (is_array($valor)) {
                        $metaDataReturn = json_encode($valor);
                        break;
                    }
                }
            }
        }
        return $metaDataReturn;
    }

    /**
     * Guarda el nuevo archivo de datos del proyecto, guardando previamente la versión anterior como una revisión
     * @param JSON   $metaDataValue   Nou contingut de l'arxiu de dades del projecte
     * @return string
     */
    public function setMeta($metaDataValue, $metadataSubset=FALSE) {
        if(!$metadataSubset){
            $metadataSubset = $this->getProjectSubset();
        }
        return $this->_setMeta($metadataSubsetet, 
                                $this->getProjectFilePath($this->getProjectId()), 
                                $this->getProjectFileName($metadataSubset, $this->getProjectType()),
                                $metaDataValue);                
    }
    
    /**
     * Guarda el nuevo archivo de datos del proyecto, guardando previamente la versión anterior como una revisión
     * @param string $metaDataSubSet  Valor de metadatasubset (exemple: "main")
     * @param string $projectFileName Nom de l'arxiu de dades del projecte (exemple: "meta.mdpr")
     * @param JSON   $metaDataValue   Nou contingut de l'arxiu de dades del projecte
     * @return string
     */
    private function _setMeta($metaDataSubSet, $projectFilePath, $projectFileName, $metaDataValue) {
        $projectFilePathName = $projectFilePath . $projectFileName;

        if (is_file($projectFilePathName)) {
            $old_contentFile = file_get_contents($projectFilePathName);
            //Aquí, ya existe, como mínimo, una versión previa de los archivos del proyecto y un subSet
            if ($old_contentFile != false) {
                $contentFileArray = json_decode($old_contentFile, true);
                $prev_date = filemtime($projectFilePathName);
                $contentFileArray[$metaDataSubSet] = json_decode($metaDataValue, true);
                $resourceCreated = io_saveFile($projectFilePathName, str_replace("\\r\\n", "\\n", json_encode($contentFileArray)));
                $new_date = filemtime($projectFilePathName);
                //Guardamos el archivo existente (la versión previa) como revisión
                $this->_saveRevision($prev_date, $new_date, $this->getProjectId(), $projectFileName, $old_contentFile);
            }
        }else {
            $resourceCreated = $this->_createResource($projectFilePath, $projectFileName);
            if ($resourceCreated) {
                $resourceCreated = $this->_setSystemData($this->getProjectId(), $projectFilePath);
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
     * @param array $params : {projectType, metaDataSubSet}
     * @return string
     */
    public function getFileName($id, $params=array()) {
        if(isset($params[ProjectKeys::KEY_REV])){
            $revision = $params[ProjectKeys::KEY_REV];
        }else{
            $revision = $this->getRevision();
        }
        if(isset($params[ProjectKeys::KEY_PROJECT_TYPE])){
            $projectType = $params[ProjectKeys::KEY_PROJECT_TYPE];
        }else{
            $projectType = $this->getProjectType();
        }
        if(isset($params[ProjectKeys::KEY_METADATA_SUBSET])){
            $metadataSubset = $params[ProjectKeys::KEY_PROJECT_TYPE];
        }else{
            $metadataSubset = $this->getProjectSubset();
        }
        return $this->getProjectFilePath($id, $revision).$this->getProjectFileName($metadataSubset, $projectType, $revision);
//                
//                
//        if($this->getProjectId()!=$id){
//            $this->init($id);
//            if(isset($params[ProjectKeys::KEY_PROJECT_TYPE])){
//                $this->setProjectType($params[ProjectKeys::KEY_PROJECT_TYPE]);
//            }
//            if(isset($params[ProjectKeys::KEY_METADATA_SUBSET])){
//                $this->setProjectType($params[ProjectKeys::KEY_PROJECT_TYPE]);
//            }
//            if(isset($params[ProjectKeys::KEY_REV])){
//                $this->setRevision($params[ProjectKeys::KEY_REV]);
//            }
//        }
//        
//        if($this->getRevision()){
//            $ret = $this->_getProjectRevisionFile($this->getRevision());
//        }else{
//            $ret = $this->getProjectFilePath().$this->getProjectFileName();
//        }
//        
//        return $ret;
    }

    /**
     * Devuelve el nombre del archivo de datos para este tipo de proyecto
     * @params array [ProjectKeys::KEY_PROJECT_TYPE,
     *                ProjectKeys::KEY_METADATA_SUBSET,
     *                ProjectKeys::KEY_PROJECTTYPE_DIR]  NOOOO!
     * @return string el nombre del fichero de datos del tipo de proyecto solicitado
     */
    public function getProjectFileName($metadataSubset=FALSE, $projectType=FALSE, $revision=FALSE) {
        $ret;
        if(!$revision){
            $revision = $this->getRevision();
        }
        if(!$this->projectFileName){
            if(!$metadataSubset){
                $metadataSubset = $this->getProjectSubset();
            }
            if(!$projectType){
                $projectType = $this->getProjectType();
            }
            $jsonArray = $this->getMetaDataConfig(ProjectKeys::KEY_METADATA_PROJECT_STRUCTURE, $projectType);
            $data = json_decode($jsonArray, true);
            $this->projectFileName = $data[$metadataSubset];
        }
        if($revision){
            $ret = "{$this->projectFileName}.$revison.txt.gz";
        }else{
            $ret = $this->projectFileName;
        }
        return $ret;
//        $jsonArray = $this->getMetaDataConfig($parms[ProjectKeys::KEY_PROJECT_TYPE],
//                                              ProjectKeys::KEY_METADATA_PROJECT_STRUCTURE,
//                                              $parms[ProjectKeys::KEY_METADATA_SUBSET],
//                                              $parms[ProjectKeys::KEY_PROJECTTYPE_DIR]
//                                            );
//        $data = json_decode($jsonArray, true);
//        return $data[$parms[ProjectKeys::KEY_METADATA_SUBSET]];
    }

//    /**
//     * Devuelve el nombre del archivo de datos para este tipo de proyecto
//     * Si recibe parámetros, guarda el valor en la variable privada $this->projectFileName
//     * @params array [ProjectKeys::KEY_PROJECT_TYPE,
//     *                ProjectKeys::KEY_METADATA_SUBSET,
//     *                ProjectKeys::KEY_PROJECTTYPE_DIR]
//     */
//    private function _getProjectFileName($parms=[]) {
//        if (!empty($parms)) {
//            $this->projectFileName = $this->getProjectFileName($parms);
//        }
//        return $this->projectFileName;
//    }


    private function getProjectFilePath($id=FALSE, $revision=FALSE) {
        if(!$id){
            $id = $this->getProjectId();
        }
        if(!$revision){
            $revision = $this->getRevision();
        }
        if($revision){
            $path = WikiGlobalConfig::getConf('revisionprojectdir') . "/"
                                    . utf8_encodeFN(str_replace(":", "/", $id))."/";
        }else{
            $path = WikiGlobalConfig::getConf('mdprojects') . "/"
                                    . utf8_encodeFN(str_replace(":", "/", $id)) . "/"
                                    . $this->getProjectType() . "/";
        }
        return $path;
    }

    private function updateProjectTypeDir($projectType=FALSE) {
            global $plugin_controller;
            if(!$projectType){
                $projectType = $this->getProjectType();
            }            
            $this->projectTypeDir = $plugin_controller->getProjectTypeDir($projectType);            
    }

    public function getProjectTypeDir($projectType=FALSE){
        if(!$this->projectTypeDir){
            $this->updateProjectTypeDir($projectType);
        }
        return $this->projectTypeDir;
//        return (class_exists('DokuModelManager', FALSE)) ? DokuModelManager::getProjectTypeDir() : WIKI_IOC_MODEL . "projects/$projectType/";
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
    public function getDataProject($id=FALSE, $projectType=FALSE, $metaDataSubSet=FALSE) {
        if(!$id){
            $id = $this->getProjectId();
        }
        if(!$projectType){
            $projectType = $this->getProjectType();
        }
        if(!$metaDataSubSet){
            $metaDataSubSet = $this->getProjectSubset();
        }
        $filename = $this->getFileName($id, array(ProjectKeys::KEY_PROJECT_TYPE => $projectType, ProjectKeys::KEY_METADATA_SUBSET => $metaDataSubSet));
        $jsonData = $this->_getMeta($metaDataSubSet, $filename);
        $data = json_decode($jsonData, true);
        return $data;
    }

    private function _saveRevision($prev_date, $new_date, $projectId, $projectFileName, $old_content) {
        $resourceCreated = FALSE;
        $new_rev_file = $this->revisionProjectDir($projectId) . "$projectFileName.$new_date.txt";
        $resourceCreated = io_saveFile("$new_rev_file.gz", $old_content);

        $last_rev_date = $this->getProjectRevisionList($projectId, $projectFileName, 1)[0]['date'];
        if ($last_rev_date && $last_rev_date < $prev_date) {
            $summary = WikiIocLangManager::getLang('external_edit');
            $flags = array('ExternalEdit'=> true);
        }
        $resourceCreated &= $this->_addProjectLogEntry($new_date, $projectId, self::LOG_TYPE_EDIT, $summary, "", $flags);
        return ($resourceCreated) ? $new_date : "";
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

    public function revisionProjectDir($projectId=FALSE) {
        if(!$projectId){
            $projectId = $this->getProjectId();
        }
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
    public function getProjectRevisionList($num=1, $chunk_size=1024) {
        $revs = array();
        $file = $this->_metaProjectFN($this->getProjectId(), $this->getProjectFileName(), ".changes");

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

    public function getLastModFileDate() {
        $fn = $this->getFileName($this->getProjectId());
        if (@file_exists($fn)) {
            return filemtime($fn);
        }
    }
}
