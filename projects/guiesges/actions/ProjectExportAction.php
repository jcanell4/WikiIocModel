<?php
/**
 * ProjectExportAction: clases de procesos, establecidas en el fichero de configuración,
 *                  correspondientes a los tipos de datos del proyecto
 * @culpable Rafael Claver
 */
if (!defined('DOKU_INC')) die();

class ProjectExportAction  extends ProjectMetadataAction{
    const CONFIG_TYPE_FILENAME = "configMain.json";
    const CONFIG_RENDER_FILENAME = "configRender.json";

    protected $projectID = NULL;
    protected $mainTypeName = NULL;
    protected $dataArray = array();
    protected $typesRender = array();
    protected $typesDefinition = array();
    protected $defaultValueForObjectFields = "";    
    protected $mode;
    protected $filetype;
    protected $projectType;
    protected $factoryRender;
    protected $metaDataSubSet;

    public function __construct($factory=NULL){
        $this->factoryRender = $factory;
    }
    /**
     * Ejecuta los procesos_render de primer nivel definidos en el primer nivel
     * del archivo de configuración del proyecto
     */
    protected function setParams($params) {
        $this->mode        = $params[ProjectKeys::KEY_MODE];
        $this->filetype    = $params[ProjectKeys::KEY_FILE_TYPE];
        $this->projectType = $params[ProjectKeys::KEY_PROJECT_TYPE];
        $this->projectID   = $params[ProjectKeys::KEY_ID];
        $this->metaDataSubSet = $params[ProjectKeys::KEY_METADATA_SUBSET];
        $this->projectNS   = $params[ProjectKeys::KEY_NS]?$params[ProjectKeys::KEY_NS]:$this->projectID;
        $this->typesRender = $this->getProjectConfigFile(self::CONFIG_RENDER_FILENAME, "typesDefinition");
        $this->defaultValueForObjectFields = $this->getProjectConfigFile(self::CONFIG_RENDER_FILENAME, "defaultValueForObjectFields");

        $cfgArray = $this->getProjectConfigFile(self::CONFIG_TYPE_FILENAME, ProjectKeys::KEY_METADATA_PROJECT_STRUCTURE, $this->metaDataSubSet);
        $this->mainTypeName = $cfgArray['mainType']['typeDef'];
        $this->typesDefinition = $cfgArray['typesDefinition'];

        $toInitModel = array(ProjectKeys::KEY_ID =>$this->projectID, ProjectKeys::KEY_PROJECT_TYPE=>$this->projectType, ProjectKeys::KEY_METADATA_SUBSET =>$this->metaDataSubSet);
        $this->projectModel->init($toInitModel);
        $this->dataArray = $this->projectModel->getDataProject(); //JOSEP: AIXÍ ESTÀ BË PERQUÈ DELEGUEM EN EL MODEL
    }

    public function responseProcess() {
        $ret = array();
        $fRenderer = $this->factoryRender;
        $fRenderer->init(['mode'            => $this->mode,
                          'filetype'        => $this->filetype,
                          'typesDefinition' => $this->typesDefinition,
                          'typesRender'     => $this->typesRender,
                          'defaultValueForObjectFields'     => $this->defaultValueForObjectFields,
                          'projectModel' => $this->getModel()]);

        $render = $fRenderer->createRender($this->typesDefinition[$this->mainTypeName],
                                           $this->typesRender[$this->mainTypeName],
                                           array(ProjectKeys::KEY_ID => $this->projectID));

        $result = $render->process($this->dataArray);
        $result['ns'] = $this->projectNS;
        $ret['id'] = str_replace(":", "_", $this->projectID);
        $ret['ns'] = $this->projectNS;

        if ($this->mode === 'xhtml') {
            $ret["meta"] = self::get_html_metadata($result);
        }else {
            throw new Exception("ProjectExportAction: mode incorrecte.");
        }

        if (!WikiGlobalConfig::getConf('plugin')['iocexportl']['saveWorkDir']){
            $this->removeDir($result["tmp_dir"]);
        }

        return $ret;
    }

    /**
     * @return array : Devuelve el subconjunto $rama del fichero de configuración del proyecto
     *                  Si se indica el metaDataSubSet, es que espera encontrar una estructura
     */
    private function getProjectConfigFile($filename, $rama, $metaDataSubSet=NULL) {
        $config = @file_get_contents(WikiIocPluginController::getProjectTypeDir($this->projectType) . "metadata/config/" . $filename);
        if ($config != FALSE) {
            $array = json_decode($config, true);
            if ($metaDataSubSet) {
                $elem = $array[$rama];
                for ($i=0; $i<count($elem); $i++) {
                    if (array_key_exists($metaDataSubSet, $elem[$i])) {
                        $ret = $elem[$i];
                        break;
                    }
                }
            }else{
                $ret = $array[$rama];
            }
            return $ret;
        }
    }

    protected function getTypesCollection($key = NULL) {
        return ($key === NULL) ? $this->typesDefinition : $this->typesDefinition[$key];
    }
    protected function getProjectDataArray($key = NULL) {
        return ($key === NULL) ? $this->dataArray : $this->dataArray[$key];
    }
    public function processTitle($param = NULL) {
        return ($param===NULL) ? $param : getProjectDataArray('title');
    }
    public function getProjectID() {
        return $this->projectID;
    }

    public static function get_html_metadata($result){
        if ($result['error']) {
            throw new Exception ("Error");
        }else{
            if (!$result["dest"]) {
                if (!self::copyFiles($result)) {
                    throw new Exception("Error en la còpia dels arxius d 'esportació des de la ubicació temporal");
                }
            }
//            $file = WikiGlobalConfig::getConf('mediadir').'/'. preg_replace('/:/', '/', $result['ns']) .'/'.preg_replace('/:/', '_', $result['ns']);
            $ret = self::_getHtmlMetadata($result);
        }
        return $ret;
    }

    private static function _getHtmlMetadata($result) {
        $P = "";
        $nP = "";

        $ret = '';
        $ret.= $P.'<span id="exportacio" style="word-wrap: break-word;">';
        for($i=0; $i<count($result["fileNames"]); $i++){
            if (isset($result["dest"][$i]) && @file_exists($result["dest"][$i])) {
                $filename = $result["fileNames"][$i];
                $media_path = "lib/exe/fetch.php?media={$result['ns']}:$filename";
                $data = date("d/m/Y H:i:s", filemtime($result["dest"][$i]));
                $class = "mf_".substr($filename, -3);

                $ret.= '<p><a class="media mediafile '.$class.'" href="'.$media_path.'" target="_blank">'.$filename.'</a> ';
                $ret.= '<span style="white-space: nowrap;">'.$data.'</span></p>';
            }else{
                $ret.= '<p class="media mediafile '.$class.'">No hi ha cap exportació feta del fitxer'.$filename.'</p>';
            }
        }
        $ret.= '</span>'.$nP;
        return $ret;
    }

    private static function copyFiles(&$result){
        $result["dest"]=array();
        $ok=false;
        $dest = preg_replace('/:/', '/', $result['ns']);
        $path_dest = WikiGlobalConfig::getConf('mediadir').'/'.$dest;
        if (!file_exists($path_dest)){
            mkdir($path_dest, 0755, TRUE);
        }
        if(is_array($result["files"])){
            $ok=true;
            for($i=0; $i<count($result["files"]); $i++) {
                $ok = $ok && copy($result["files"][$i], $path_dest.'/'.$result["fileNames"][$i]);
                $result["dest"][$i]=$path_dest.'/'.$result["fileNames"][$i];
            }
        }
        return $ok;
    }

    /**
     * Remove specified dir
     * @param string $directory
     */
    private function removeDir($directory) {
        if (file_exists($directory) && is_dir($directory) && is_readable($directory)) {
            $dh = opendir($directory);
            while ($contents = readdir($dh)) {
                if ($contents != '.' && $contents != '..') {
                    $path = "$directory/$contents";
                    if (is_dir($path)) {
                        $this->removeDir($path);
                    }else {
                        unlink($path);
                    }
                }
            }
            closedir($dh);

            if (file_exists($directory)) {
                rmdir($directory);
            }
        }
    }
}
