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
require_once (DOKU_PLUGIN.'ajaxcommand/defkeys/ProjectKeys.php');
require_once (WIKI_IOC_MODEL.'persistence/DataQuery.php');

class ProjectMetaDataQuery extends DataQuery {

    const K_CONFIGUSUBSETSTRUCTURE = ProjectKeys::KEY_METADATA_PROJECT_STRUCTURE;
    const K_METADATASUBSET         = ProjectKeys::KEY_METADATA_SUBSET;
    const K_DEFAULTSUBSET          = ProjectKeys::KEY_DEFAULTSUBSET;
    const K_PROJECT_FILENAME       = ProjectKeys::KEY_PROJECT_FILENAME;
    const K_PROJECT_FILEPATH       = ProjectKeys::KEY_PROJECT_FILEPATH;

    const PATH_METADATA_CONFIG = "/metadata/config/";
    const FILE_CONFIGMAIN      = "configMain.json";
    const FILE_DEFAULTVIEW     = "defaultView.json";

    const PROJECT_LOG_TYPE_CREATE     = 'C';
    const PROJECT_LOG_TYPE_EDIT       = 'E';
    const PROJECT_LOG_TYPE_MINOR_EDIT = 'e';
    const PROJECT_LOG_TYPE_DELETE     = 'D';
    const PROJECT_LOG_TYPE_REVERT     = 'R';

    private $projectFileName = NULL;    //Nom de l'arxiu de dades del projecte
    private $projectFilePath = NULL;    //Ruta completa al directori del projecte

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

        $configMain = @file_get_contents(WIKI_IOC_PROJECTS . $projectType . self::PATH_METADATA_CONFIG . self::FILE_CONFIGMAIN);
        if ($configMain == false) {
            $configMain = @file_get_contents(WIKI_IOC_PROJECTS . "defaultProject" . self::PATH_METADATA_CONFIG . self::FILE_CONFIGMAIN);
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
    public function isProjectGenerated($idProject, $projectType) {
        $filename = WikiGlobalConfig::getConf('projects','wikiiocmodel')['dataSystem'];
        $jsonArr = $this->getMeta($idProject, $projectType, "system", $filename);
        $data = json_decode($jsonArr, true);
        return $data['generated'];
    }

    /**
     * Establece el estado 'generated'=true del proyecto
     * @return boolean : true si el estado del proyecto se ha establecido con éxito
     */
    public function setProjectGenerated($idProject, $projectType) {
        $projectSystemDataFile = WikiGlobalConfig::getConf('projects','wikiiocmodel')['dataSystem'];
        $metaDataSubSet = "system";
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
        $dirProject = $this->projectFilePath;
        $projectFilePathName = $this->projectFilePath . $this->projectFileName;

        if (is_file($projectFilePathName)) {
            $old_contentFile = file_get_contents($projectFilePathName);
            //Aquí, ya existe, como mínimo, una versión previa de los archivos del proyecto
            if ($old_contentFile != false) {
                $contentFileArray = json_decode($old_contentFile, true);
                if ($contentFileArray[$metaDataSubSet]) {
                    $prev_date = filemtime($projectFilePathName);
                    $contentFileArray[$metaDataSubSet] = json_decode($metaDataValue, true);
                    $resourceCreated = io_saveFile($projectFilePathName, json_encode($contentFileArray));
                    //Guardamos el archivo existente (la versión previa) como revisión
                    $dateRevision = $this->_saveRevision($prev_date, $projectId, $projectFilePathName, $old_contentFile);
                }else {
                    $resourceCreated = '{"error":"5090"}';  //no existe $metaDataSubSet en el fichero
                }
            }
        }else {
            $resourceCreated = $this->_createResource($dirProject, $projectFileName);
            if ($resourceCreated) {
                $metaDataValue = $this->_setSystemData($metaDataSubSet, $metaDataValue, $dirProject);
                $resourceCreated = io_saveFile($projectFilePathName, $metaDataValue);
            }else {
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
            if (($fp = @fopen("$dirProject/$file", 'w')) !== false) {
                fclose($fp);
                $resourceCreated = true;
            }
        }
        return $resourceCreated;
    }

    private function _setSystemData($metaDataSubSet, $metaDataValue, $dirProject) {
        //Crea el fichero de sistema del proyecto
        $data = '{"system":{"generated":false}}';
        $file = WikiGlobalConfig::getConf('projects','wikiiocmodel')['dataSystem'];
        io_saveFile("$dirProject/$file", $data);
        //Retorna el array json construido a partir del subset y su array de valores
        return "{\"$metaDataSubSet\":$metaDataValue}";
    }

    /**
     * Devuelve la ruta completa al fichero del proyecto (en mdprojects)
     * @param string $id : wikiRuta de la página del proyecto
     * @param array $params : {projectType, metaDataSubSet, projectfilename}
     * @return string
     */
    public function getFileName($id, $params=array()) {
        if ($id && $params) {
            $filename = ($params[self::K_PROJECT_FILENAME]) ? $params[self::K_PROJECT_FILENAME] : $this->getProjectFileName($params);
            $dir = WikiGlobalConfig::getConf('mdprojects')."/".str_replace(':', "/", $id)."/${params[self::K_PROJECTTYPE]}";
            $ret = "$dir/$filename";
        }else {
            $ret = $this->getProjectFilePath() . $this->getProjectFileName();
        }
        return $ret;
    }

     /**
     * @params array(projectType, metadatasubset)
     * @return string el nombre del fichero de datos del proyecto del tipo solicitado
     */
    public function getProjectFileName($parms) {
        if ($parms) {
            $jsonArray = $this->getMetaDataConfig($parms[self::K_PROJECTTYPE], $parms[self::K_METADATASUBSET], self::K_CONFIGUSUBSETSTRUCTURE);
            $data = json_decode($jsonArray, true);
            $this->projectFileName = $data[$parms[self::K_METADATASUBSET]];

            if ($parms[AjaxKeys::KEY_ID]) {
                $this->projectFilePath = WikiGlobalConfig::getConf('mdprojects') . "/"
                                         . str_replace(":", "/", $parms[AjaxKeys::KEY_ID]) . "/"
                                         . $parms[self::K_PROJECTTYPE] . "/";
            }
        }
        return $this->projectFileName;
    }

    public function getProjectFilePath() {
        return $this->projectFilePath;
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
     * @return array Contiene los datos del proyecto correspondientes a la clave '$metaDataSubSet'
     */
    public function getDataProject($idProject, $projectType) {
        $metaDataSubSet = ProjectKeys::VAL_DEFAULTSUBSET;   //clave del array que contiene los datos del proyecto
        $filename = $this->getProjectFileName(array('id'=>$idProject, self::K_PROJECTTYPE=>$projectType, self::K_METADATASUBSET=>$metaDataSubSet));
        $jsonData = $this->getMeta($idProject, $projectType, $metaDataSubSet, $filename);
        $data = json_decode($jsonData, true);
        $data[self::K_PROJECT_FILENAME] = $filename;
        $data[self::K_PROJECT_FILEPATH] = $this->getFileName($idProject, array(self::K_PROJECTTYPE => $projectType, self::K_METADATASUBSET => $metaDataSubSet));
        return $data;
    }

    private function _saveRevision($prev_date, $projectId, $projectFilePathName, $old_content) {
        $resourceCreated = FALSE;

        if (@file_exists($projectFilePathName)) {
            $mdate = filemtime($projectFilePathName);
            $new_rev_file = $this->_revisionProjectFN($projectId, "{$this->projectFileName}.$mdate", ".txt");
            $resourceCreated = io_saveFile("$new_rev_file.gz", $old_content);

            $last_rev_date = $this->getProjectRevisionList($projectId, 1)[0]['date'];
            if ($last_rev_date && $last_rev_date < $prev_date) {
                $summary = WikiIocLangManager::getLang('external_edit');
                $flags = array('ExternalEdit'=> true);
            }
            $resourceCreated &= $this->addProjectLogEntry($mdate, $projectId, $projectFilePathName, self::PROJECT_LOG_TYPE_EDIT, $summary, "", $flags);
        }
        return ($resourceCreated) ? $mdate : "";
    }

    /**
     * Logs del proceso de guardar una modificación del archivo de datos del proyecto.
     * @param string $mdate               fecha de última modificación del archivo de datos del proyecto
     * @param string $projectId           ruta relativa del proyecto
     * @param string $projectFilePathName ruta absoluta del fichero del proyecto (incluye el nombre del fichero)
     * @param string $type                tipo de modificación
     * @param string $summary
     * @param string $extra
     * @param array $flags
     * @return boolean
     */
    private function addProjectLogEntry($mdate, $projectId, $projectFilePathName, $type=self::PROJECT_LOG_TYPE_EDIT, $summary="", $extra="", $flags=NULL) {
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
        $ret = $this->addLogMetaFile($projectId, $projectFilePathName, $record );

        //changes log
        $ret &= $this->addLogChangesFile($projectId, $record);

        return $ret;
    }

    /**
     * En este log se guarda una línea por cada modificación sufrida por el archivo de datos del proyecto
     * @param string $projectId ruta relativa del proyecto
     * @param array  $record    datos del registro de log
     * @return boolean
     */
    private function addLogChangesFile($projectId, $record) {
        $ret = TRUE;
        $record_line = implode("\t", $record)."\n";
        $ch_filename = $this->_metaProjectFN($projectId, "", ".changes");

        $fh = fopen($ch_filename, "r");
        if ($fh) {
            $fh2 = fopen("$ch_filename.tmp", "w");
            $bytes = fwrite($fh2, $record_line);
            while (!feof($fh)) {
                fwrite($fh2, fgets($fh));
            }
            fclose($fh2);
            fclose($fh);
            $ret &= rename("$ch_filename.tmp", $ch_filename);
        }else {
            $fh = fopen($ch_filename, "w");
            $bytes = fwrite($fh, $record_line);
            fclose($fh);
        }
        $ret &= ($bytes === strlen($record_line));
        return $ret;
    }

    /**
     * Log del proceso de guardar una modificación del archivo de datos del proyecto. Es el log que se guarda en
     * el archivo projectId/projectFilename.meta. Este archivo log contiene un JSON de metadatos del proyecto
     * @param string $projectId            ruta relativa del proyecto
     * @param string $projectFilePathName  ruta absoluta del fichero del proyecto (incluye el nombre del fichero)
     * @param array  $record               datos del registro de log
     * @return boolean
     */
    private function addLogMetaFile($projectId, $projectFilePathName, $record) {
        $minor = ($record['type'] === self::PROJECT_LOG_TYPE_MINOR_EDIT);
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
        if ($filename==="" && $this->projectFileName) {
            $filename = $this->projectFileName;
        }
        $dir = WikiGlobalConfig::getConf('metaprojectdir') . "/$projectId/$filename$ext";
        return $dir;
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
    public function getProjectRevisionList($projectId, $num=1, $chunk_size=1024) {
        $revs = array();
        $file = $this->_metaProjectFN($projectId, "", ".changes");

        if (@file_exists($file)) {
            if (filesize($file) < $chunk_size || $num==0 || $chunk_size==0) {
                $lines = file($file);
                if ($num==0 || $chunk_size==0) $num = count($lines);
            }else {
                $fh = fopen($file, 'r');
                if ($fh) {
                    $lines[] = fgets($fh, $chunk_size);
                    $count = intdiv($chunk_size, strlen($revs[0]));
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

}
