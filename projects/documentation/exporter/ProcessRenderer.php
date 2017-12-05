<?php
/**
 * ProcessRenderer: clases de procesos, establecidas en el fichero de configuración,
 *                  correspondientes a los tipos de datos del proyecto
 * @culpable Rafael Claver
 */
if (!defined('DOKU_INC')) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC."lib/plugins/");
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_PLUGIN."wikiiocmodel/");
define('WIKI_IOC_PROJECT', WIKI_IOC_MODEL . "projects/documentation/");
require_once WIKI_IOC_MODEL."persistence/ProjectMetaDataQuery.php";
require_once WIKI_IOC_PROJECT."exporter/FactoryRenderer.php";
require_once WIKI_IOC_PROJECT."exporter/exporterClasses.php";

class ProcessRenderer extends AbstractRenderer {

    const PATH_RENDERER = WIKI_IOC_PROJECT."exporter/";
    const PATH_CONFIG_FILE = WIKI_IOC_PROJECT."metadata/config/";
    const CONFIG_TYPE_FILENAME = "configMain.json";
    const CONFIG_RENDER_FILENAME = "configRender.json";
    const PROJECT_TYPE = "documentation";

    protected $projectID = NULL;
    protected $mainTypeName = NULL;
    protected $dataArray = array();
    protected $typesRender = array();
    protected $typesDefinition = array();

    public function __construct($projectID=NULL) {
        $this->projectID = $projectID;
        $this->typesRender = $this->getProjectConfigFile(self::CONFIG_RENDER_FILENAME, "typesDefinition");
        $cfgArray = $this->getProjectConfigFile(self::CONFIG_TYPE_FILENAME, ProjectKeys::KEY_METADATA_PROJECT_STRUCTURE)[0];
        $this->mainTypeName = $cfgArray['mainType']['typeDef'];
        $this->typesDefinition = $cfgArray['typesDefinition'];
            $projectfilename = $cfgArray[ProjectKeys::VAL_DEFAULTSUBSET];
            if (defined('TEST_RENDERER_PROJECT'))
                $idResoucePath = DOKU_INC."data/".WikiGlobalConfig::getConf('mdprojects')."/".str_replace(":", "/", $this->projectID);
            else
                $idResoucePath = WikiGlobalConfig::getConf('mdprojects')."/".str_replace(":", "/", $this->projectID);
            $projectfilepath = "$idResoucePath/".self::PROJECT_TYPE."/$projectfilename";
        $this->dataArray = $this->getProjectDataFile($projectfilepath, ProjectKeys::VAL_DEFAULTSUBSET);
    }

    /**
     * Ejecuta los procesos_render de primer nivel definidos en el primer nivel
     * del archivo de configuración del proyecto
     */
    public function init() {
        $fRenderer = new FactoryRenderer($this->typesDefinition, $this->typesRender);
        $render = $fRenderer->createRender($this->typesDefinition[$this->mainTypeName], $this->typesRender[$this->mainTypeName]);
        $ret = $render->process($this->dataArray);
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
}
