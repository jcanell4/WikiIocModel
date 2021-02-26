<?php
/**
 * ProjectExportAction: clases de procesos, establecidas en el fichero de configuración,
 *                  correspondientes a los tipos de datos del proyecto
 * @culpable Rafael Claver
 */
if (!defined('DOKU_INC')) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC."lib/plugins/");
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_PLUGIN."wikiiocmodel/");
if (!defined('EXPORT_TMP')) define('EXPORT_TMP',DOKU_PLUGIN.'tmp/latex/');
define('WIKI_IOC_PROJECT', WIKI_IOC_MODEL . "projects/ptfplogse/");

class ProjectExportAction  extends ProjectAction{
    const PATH_RENDERER = WIKI_IOC_PROJECT."exporter/";
    const PATH_CONFIG_FILE = WIKI_IOC_PROJECT."metadata/config/";
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
        parent::__construct();
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
        $this->projectModel->init([ProjectKeys::KEY_ID              => $this->projectID,
                                   ProjectKeys::KEY_PROJECT_TYPE    => $this->projectType,
                                   ProjectKeys::KEY_METADATA_SUBSET => $this->metadataSubset]);
        $this->dataArray = $this->projectModel->getCurrentDataProject();
    }

    protected function preResponseProcess() {
        parent::preResponseProcess();
        //Guarda una revisió del zip existent abans no es guardi la nova versió
        $output_filename = $this->projectID . ":". str_replace(':', '_', $this->projectID).".zip";
        media_saveOldRevision($output_filename);
    }

    public function responseProcess() {
        $ret = array();
        $fRenderer = $this->factoryRender;
        $fRenderer->init(['mode'            => $this->mode,
                          'filetype'        => $this->filetype,
                          'typesDefinition' => $this->typesDefinition,
                          'typesRender'     => $this->typesRender,
                          'defaultValueForObjectFields' => $this->defaultValueForObjectFields ]);
        $render = $fRenderer->createRender($this->typesDefinition[$this->mainTypeName],
                                           $this->typesRender[$this->mainTypeName],
                                           array(ProjectKeys::KEY_ID => $this->projectID));
        $result = $render->process($this->dataArray);
        $result['ns'] = $this->projectNS;
        $ret = ["id" => $this->idToRequestId($this->projectID),
                "ns" => $this->projectNS];

        switch ($this->mode) {
            case 'xhtml':
                $ret["meta"] = ResultsWithFiles::get_html_metadata($result);
                break;
            default:
                throw new Exception("ProjectExportAction: mode incorrecte.");
        }
        if (!WikiGlobalConfig::getConf('plugin')['iocexportl']['saveWorkDir']){
                $this->removeDir($result["tmp_dir"]);
        }

        $ret[AjaxKeys::KEY_ACTIVA_FTP_PROJECT_BTN] = $this->getModel()->haveFilesToExportList();
        $ret[AjaxKeys::KEY_FTPSEND_HTML] = $this->getModel()->get_ftpsend_metadata();

        return $ret;
    }

    /**
     * @return array : Devuelve el subconjunto $rama del fichero de configuración del proyecto
     *                  Si se indica el metaDataSubSet, es que espera encontrar una estructura
     */
    private function getProjectConfigFile($filename, $rama, $metaDataSubSet=NULL) {
        $config = @file_get_contents(self::PATH_CONFIG_FILE.$filename);
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
        return ResultsWithFiles::get_html_metadata($result);
    }

    private function removeDir($directory) {
        return IocCommon::removeDir($directory);
    }
}
