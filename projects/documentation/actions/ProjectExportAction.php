<?php
/**
 * ProcessRenderer: clases de procesos, establecidas en el fichero de configuración,
 *                  correspondientes a los tipos de datos del proyecto
 * @culpable Rafael Claver
 */
if (!defined('DOKU_INC')) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC."lib/plugins/");
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_PLUGIN."wikiiocmodel/");
if (!defined('EXPORT_TMP')) define('EXPORT_TMP',DOKU_PLUGIN.'tmp/latex/');

define('WIKI_IOC_PROJECT', WIKI_IOC_MODEL . "projects/documentation/");
require_once WIKI_IOC_MODEL."persistence/ProjectMetaDataQuery.php";

class ProjectExportAction{
    const PATH_RENDERER = WIKI_IOC_PROJECT."renderer/";
    const PATH_CONFIG_FILE = WIKI_IOC_PROJECT."metadata/config/";
    const CONFIG_TYPE_FILENAME = "configMain.json";
    const CONFIG_RENDER_FILENAME = "configRender.json";
    const PROJECT_TYPE = "documentation";
    protected $projectID = NULL;
    protected $projectNS = NULL;
    protected $mainTypeName = NULL;
    protected $dataArray = array();
    protected $renderArray = array();
    protected $typesDefinition = array();
    protected $mode;
    protected $factoryRender;
   
    public function __construct($factory=NULL){
        $this->factoryRender = $factory;
    }
    /**
     * Ejecuta los procesos_render de primer nivel definidos en el primer nivel
     * del archivo de configuración del proyecto
     */
    public function init($params) {
        $this->mode = $params['mode'];
        $this->projectID = $params['id'];
        $this->projectNS = $params['ns'];
        $this->renderArray = $this->getProjectConfigFile(self::CONFIG_RENDER_FILENAME, "typesDefinition");
        $cfgArray = $this->getProjectConfigFile(self::CONFIG_TYPE_FILENAME, ProjectKeys::KEY_METADATA_PROJECT_STRUCTURE)[0];
        $this->mainTypeName = $cfgArray['mainType']['typeDef'];
        $this->typesDefinition = $cfgArray['typesDefinition'];
        $projectfilename = $cfgArray[ProjectKeys::VAL_DEFAULTSUBSET];
        $idResoucePath = WikiGlobalConfig::getConf('mdprojects')."/".str_replace(":", "/", $this->projectNS);
        $projectfilepath = "$idResoucePath/".self::PROJECT_TYPE."/$projectfilename";
        $this->dataArray = $this->getProjectDataFile($projectfilepath, ProjectKeys::VAL_DEFAULTSUBSET);            
    }

    public function process() {
        $ret = array();
        //$fRenderer = new FactoryRenderer($this->typesDefinition, $this->renderArray);
        $fRenderer = $this->factoryRender;
        $fRenderer->init($this->mode, $this->typesDefinition, $this->renderArray);
        $render = $fRenderer->createRender($this->typesDefinition[$this->mainTypeName], $this->renderArray[$this->mainTypeName], array("id"=> $this->projectID));
        $result = $render->process($this->dataArray);
        $result['id'] = $this->projectID;
        $result['ns'] = $this->projectNS;
        $ret = self::get_html_metadata($result);
        
        return $ret;
    }
    /**
     * @return array : Devuelve el subconjunto $rama del fichero de configuración del proyecto
     */
    private function getProjectConfigFile($filename, $rama) {
        $config = @file_get_contents(self::PATH_CONFIG_FILE.$filename);
        if ($config != FALSE) {
            $array = json_decode($config, true);
            return $array[$rama];
        }
    }
    /**
     * Extrae, del contenido del fichero, los datos correspondientes a la clave
     * @param string $file : ruta completa al fichero de datos del proyecto
     * @param string $metaDataSubSet : clave del contenido
     * @return array conteniendo el array de la clave 'metadatasubset' con los datos del proyecto
     */
    private function getProjectDataFile($file, $metaDataSubSet) {
        $contentFile = @file_get_contents($file);
        if ($contentFile != false) {
            $contentArray = json_decode($contentFile, true);
            return $contentArray[$metaDataSubSet];
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
    
//    private function _writeRenderedData($text){
//            $error = '';
//
//            $filename = preg_replace('/:/', '/', $this->projectID);
//            $dest = dirname($filename);
//            if (!file_exists(WikiGlobalConfig::getConf('mediadir').'/'.$dest)){
//                mkdir(WikiGlobalConfig::getConf('mediadir').'/'.$dest, 0755, TRUE);
//            }
//            $fop = fopen(WikiGlobalConfig::getConf('mediadir').'/'.$filename, "w");
//            fwrite($fop, $text);
//            fclose($fop);
//            return $filename;
//    }
    
    public static function get_html_metadata($result){
        if($result['error']){

        }else{
            if($result["zipFile"]){
                self::copyZip($result);
            }
            if(@file_exists(WikiGlobalConfig::getConf('mediadir').'/'. preg_replace('/:/', '/', $result['ns']) .'/'.$result["zipName"])){
                $formId = "form_rend_".$result['id']; //str_replace(":", "_", $this->projectID); //Id del node que conté la pàgina
                $ext = ".zip";

                $filename = str_replace(':','_',basename($result['ns'])).$ext;

                $media_path = "lib/exe/fetch.php?media=".$result['ns'].":".$filename;

                $ret = '<span id="exportacio">';

                $ret .= '<a class="media mediafile  mf_zip" href="'.$media_path.'">'.$filename.'</a>';
                $ret .= '</span>';
            }else{
                $ret = '<span id="exportacio">';
                $ret .= '<p class="media mediafile  mf_zip">No hi ha cap exportació feta</p>';
                $ret .= '</span>';                            
            }
        }
        return $ret;
    }
    
     private static function copyZip($result){
        $dest = preg_replace('/:/', '/', $result['ns']);
        if (!file_exists(WikiGlobalConfig::getConf('mediadir').'/'.$dest)){
            mkdir(WikiGlobalConfig::getConf('mediadir').'/'.$dest, 0755, TRUE);
        }
        copy($result["zipFile"], WikiGlobalConfig::getConf('mediadir').'/'.$dest .'/'.$result["zipName"]);
    }
}
