<?php
/**
 * ProjectExportAction: clase de procesos, establecida en el fichero de configuración,
 *                      correspondientes a los tipos de datos del proyecto
 * @culpable Rafael Claver
 */
if (!defined('DOKU_INC')) die();
define('WIKI_IOC_PROJECT', realpath(__DIR__ . "/../") . "/");

class ProjectExportAction extends ProjectAction{
    const PATH_RENDERER = WIKI_IOC_PROJECT."exporter/";
    const PATH_CONFIG_FILE = WIKI_IOC_PROJECT."metadata/config/";
    const CONFIG_TYPE_FILENAME = "configMain.json";
    const CONFIG_RENDER_FILENAME = "configRender.json";

    protected $projectID = NULL;
    protected $mainTypeName = NULL;
    protected $dataArray = array();
    protected $typesRender = array();
    protected $typesDefinition = array();
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
        $this->mode           = $params[ProjectKeys::KEY_MODE];
        $this->filetype       = $params[ProjectKeys::KEY_FILE_TYPE];
        $this->projectType    = $params[ProjectKeys::KEY_PROJECT_TYPE];
        $this->projectID      = $params[ProjectKeys::KEY_ID];
        $this->metaDataSubSet = $params[ProjectKeys::KEY_METADATA_SUBSET];
        $this->projectNS      = $params[ProjectKeys::KEY_NS] ? $params[ProjectKeys::KEY_NS] : $this->projectID;

        $this->typesRender = $this->getProjectConfigFile(self::CONFIG_RENDER_FILENAME, "typesDefinition");
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
        //Guarda una revisió del pdf existent abans no es guardi la nova versió
        $ext = ($this->mode === "xhtml") ? ".zip" : ".pdf";
        $output_filename = $this->projectID . ":" . str_replace(':', '_', $this->projectID) . $ext;
        media_saveOldRevision($output_filename);
    }

    public function responseProcess() {
        $ret = array();
        if ($this->mode !== "xhtml") {
            throw new Exception("ProjectExportAction: mode incorrecte.");
        }
        $this->factoryRender->init(['mode'            => $this->mode,
                                    'filetype'        => $this->filetype,
                                    'typesDefinition' => $this->typesDefinition,
                                    'typesRender'     => $this->typesRender]);

        $render = $this->factoryRender->createRender($this->typesDefinition[$this->mainTypeName],
                                                     $this->typesRender[$this->mainTypeName],
                                                     array(ProjectKeys::KEY_ID => $this->projectID));

        $data = $this->dataArray;
        $data['pdf_filename'] = $this->getPdfDocumentName();
        $render->process($data);
        $result = $render->getResultFileList();
        $result['ns'] = $this->projectNS;
        $ret["id"] = $this->idToRequestId($this->projectID);
        $ret["ns"] = $this->projectNS;
        $ret["meta"] = ResultsWithFiles::get_html_metadata($result);

        if (!WikiGlobalConfig::getConf('plugin')['iocexportl']['saveWorkDir']){
            $this->removeDir($result["tmp_dir"]);
        }
        return $ret;
    }

    /**
     * @return string Nom del fitxer pdf (agrupa tots els fitxers del projecte) definit a configMain:metaDataFtpSender
     */
    private function getPdfDocumentName() {
        $metaDataFtpSender = $this->getModel()->getMetaDataFtpSender();
        foreach ($metaDataFtpSender['files'] as $file_type) {
            if ($file_type['type'] == "pdf") {
                $filename = $file_type['filename'];
            }
        }
        return $filename;
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
