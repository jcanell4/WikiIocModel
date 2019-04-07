<?php
/**
 * AbstractProjectModel
 * @author professor
 */
if (!defined('DOKU_INC')) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . "lib/plugins/");
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_PLUGIN . "wikiiocmodel/");

require_once (DOKU_INC . "inc/common.php");
require_once (DOKU_PLUGIN . "ajaxcommand/defkeys/PageKeys.php");
require_once (DOKU_PLUGIN . "ajaxcommand/defkeys/ProjectKeys.php");
require_once (WIKI_IOC_MODEL . "datamodel/AbstractWikiDataModel.php");
require_once (WIKI_IOC_MODEL . "datamodel/DokuPageModel.php");
require_once (WIKI_IOC_MODEL . "metadata/MetaDataService.php");

abstract class AbstractProjectModel extends AbstractWikiDataModel{
    protected $id;
    protected $rev;
    protected $projectType;
    protected $metaDataSubSet;

    //protected $persistenceEngine; Ya está definida en AbstractWikiModel
    protected $metaDataService;
    protected $draftDataQuery;
    protected $lockDataQuery;
    protected $dokuPageModel;
    protected $viewConfigName;

    public function __construct($persistenceEngine)  {
        parent::__construct($persistenceEngine);
        $this->metaDataService= new MetaDataService();
        $this->draftDataQuery = $persistenceEngine->createDraftDataQuery();
        $this->lockDataQuery = $persistenceEngine->createLockDataQuery();
        $this->dokuPageModel = new DokuPageModel($persistenceEngine);
        $this->viewConfigName = "defaultView";
    }

    public function init($params, $projectType=NULL, $rev=NULL, $viewConfigName="defaultView", $metadataSubset=Projectkeys::VAL_DEFAULTSUBSET) {
        if(is_array($params)){
            $this->id          = $params[ProjectKeys::KEY_ID];
            $this->projectType = $params[ProjectKeys::KEY_PROJECT_TYPE];
            $this->rev         = $params[ProjectKeys::KEY_REV];
            $this->metaDataSubSet = ($params[ProjectKeys::KEY_METADATA_SUBSET]) ? $params[ProjectKeys::KEY_METADATA_SUBSET] : ProjectKeys::VAL_DEFAULTSUBSET;
            if ($params[ProjectKeys::VIEW_CONFIG_NAME]){
                $this->viewConfigName = $params[ProjectKeys::VIEW_CONFIG_NAME];
            }
        }else{
            $this->id = $params;
            $this->projectType = $projectType;
            $this->rev = $rev;
            $this->metaDataSubSet = $metadataSubset;
            $this->viewConfigName=$viewConfigName;
        }
        $this->projectMetaDataQuery->init($this->id);
        if($this->projectType){
            $this->projectMetaDataQuery->setProjectType($this->projectType);
        }
        if($this->metaDataSubSet){
            $this->projectMetaDataQuery->setProjectSubset($this->metaDataSubSet);
        }
        if($this->rev){
            $this->projectMetaDataQuery->setRevision($this->rev);
        }
    }

    public function getModelAttributes($key=NULL){
        $attr[ProjectKeys::KEY_ID] = $this->id;
        $attr[ProjectKeys::KEY_PROJECT_TYPE] = $this->getProjectType();
        $attr[ProjectKeys::KEY_REV] = $this->rev;
        $attr[ProjectKeys::KEY_METADATA_SUBSET] = $this->getMetaDataSubSet();
        return ($key) ? $attr[$key] : $attr;
    }
    
    public function getContentDocumentId($docId){
        if(is_array($docId)){
            return $this->getContentDocumentIdFormResponse($docId);
        }
        return $this->id.":".$docId;
    }
    
    protected function getContentDocumentIdFromResponse($responseData){        
//        Cal fer abstracta aquesta funció
    }


    public function setActualRevision($actual_revision){
        $this->projectMetaDataQuery->setActualRevision($actual_revision);
    }

    public function getActualRevision(){
        return $this->projectMetaDataQuery->getActualRevision();
    }

    public function getMetaDataSubSet() {
        return ($this->metaDataSubSet) ? $this->metaDataSubSet : ProjectKeys::VAL_DEFAULTSUBSET;
    }

    public function isAlternateSubSet() {
        return ($this->metaDataSubSet && $this->metaDataSubSet !== ProjectKeys::VAL_DEFAULTSUBSET);
    }

    /**
     * Retorna el sufijo para el ID de la pestaña de un proyecto para un subset distinto de 'main' o una revisión
     * @params string $rev . Si existe, indica que es una revisión del proyecto
     * @return string
     */
    public function getIdSuffix($rev=FALSE) {
        $ret = "";
        if ($this->isAlternateSubSet()){
            $ret .= "-".$this->getMetaDataSubSet();
        }
        if ($rev) {
            $ret .= ProjectKeys::REVISION_SUFFIX;
        }
        return $ret;
    }

    //Obtiene el contenido de un archivo wiki, es decir, está en pages/$id:nombre y tienen extensión .txt
    public function getRawProjectDocument($filename) {
        $content = $this->getPageDataQuery()->getRaw("{$this->id}:$filename");
        return $content;
    }

    public function getRawTemplate($filename, $version) {
        $content = $this->getPageDataQuery()->getTemplateRaw($filename, $version);
        return $content;        
    }
    
    /**
     * Obtiene el contenido del archivo wiki indicado en $filename. Está en pages/$filename con extensión .txt
     * @param string $filename : ruta wiki (con :) del archivo (a partir de pages/)
     * @return string : contenido del archivo
     */
    public function getRawDocument($filename) {
        $content = $this->getPageDataQuery()->getRaw($filename);
        return $content;
    }

    public function setRawProjectDocument($filename, $text, $summary) {
        $toSet = [ProjectKeys::KEY_ID => "{$this->id}:$filename",
                  PageKeys::KEY_WIKITEXT => $text,
                  PageKeys::KEY_SUM => $summary];
        $this->dokuPageModel->setData($toSet);
    }

    /**
     * Obtiene los datos del archivo de datos (meta.mdpr) de un proyecto
     */
    public function getMetaDataProject($metaDataSubset=FALSE) {
        $ret = $this->projectMetaDataQuery->getMeta($metaDataSubset);
        return json_decode($ret, true);
    }

    //Obtiene un array [key, value] con los datos del proyecto solicitado
    public function getDataProject($id=FALSE, $projectType=FALSE, $metaDataSubSet=FALSE) {
        return $this->projectMetaDataQuery->getDataProject($id, $projectType, $metaDataSubSet);
    }

    /**
     * Obtiene y, después, retorna una estructura con los metadatos y valores del proyecto
     * @return array('projectMetaData'=>array('values','structure'), array('projectViewData'))
     */
    public function getData() {
        $ret = [];
        $subSet = $this->getMetaDataSubSet();
        $query = [
            ProjectKeys::KEY_PERSISTENCE => $this->persistenceEngine,
            ProjectKeys::KEY_PROJECT_TYPE => $this->getProjectType(),
            ProjectKeys::KEY_METADATA_SUBSET => $subSet,
            ProjectKeys::KEY_ID_RESOURCE => $this->id
        ];
        if ($this->rev) {
            $query[ProjectKeys::KEY_REV] = $this->rev;
        }
        $ret['projectMetaData'] = $this->metaDataService->getMeta($query, FALSE)[0];

        if ($this->viewConfigName === ProjectKeys::KEY_DEFAULTVIEW){  //CANVIAR $viewConfigName a VALOR NUMÊRIC
            $struct = $this->projectMetaDataQuery->getMetaDataStructure();
            if (!$ret['projectMetaData']) {
                //si todavía no hay datos en el fichero de proyecto se recoge la lista de campos del tipo de proyecto
                $typeDef = $struct['mainType']['typeDef'];
                $keys = $struct['typesDefinition'][$typeDef]['keys'];
                foreach ($keys as $k => $v) {
                    $metaData[$k] = ($v['default']) ? $v['default'] : "";
                }
                $ret['projectMetaData'] = $metaData;
            }
            if ($struct['viewfiles'][0]) {
                $this->viewConfigName = $struct['viewfiles'][0];
            }
        }
        $ret['projectViewData'] = $this->projectMetaDataQuery->getMetaViewConfig($this->viewConfigName);

        $this->mergeFieldConfig($ret['projectMetaData'], $ret['projectViewData']['fields']);
        $this->mergeFieldNameToLayout($ret['projectViewData']['fields']);

        return $ret;
    }

    protected function mergeFieldNameToLayout(&$projectViewDataFields) {
        // S'afegeix la informació dels fields al layout si no existeix
        // Per ara només cal afegir la informació 'name'
        foreach ($projectViewDataFields as $tableKey => $table) {
            // $value es el camp o taula

            if (!isset($table['config']) || !isset($table['config']['layout'])) {
                continue;
            }

            // Recorrem tots els layouts

            for ($i = 0; $i<count ($table['config']['layout']); $i++) {

                // Recorrem totes les cel·les
                for ($j = 0; $j<count ($table['config']['layout'][$i]['cells']); $j++)

                    // Si no s'ha assignat el name al layout es cerca el name al field
                    if (!isset($table['config']['layout'][$i]['cells'][$j]['name'])) {
                        $fieldName = $table['config']['layout'][$i]['cells'][$j]['field'];

                        // TODO[Xavi] Valorar si es preferible assignar el valor del field quan no existeixi 'name' al camp
                        $layoutName = $table['config']['fields'][$fieldName]['name'];
                        $projectViewDataFields[$tableKey]['config']['layout'][$i]['cells'][$j]['name'] = $layoutName;
                    }

                }
            }

    }

    protected function mergeFieldConfig($projectMetaData, &$projectViewDataFields) {
        foreach ($projectMetaData as $key=>$value) {
            if (!$value['keys']) {
                continue;
            }
            if (!isset($projectViewDataFields[$key]['config']) || !isset($projectViewDataFields[$key]['config']['fields'])) {
                $projectViewDataFields[$key]['config']['fields'] = [];
            }

            foreach ($value['keys'] as $field=>$fieldConfig) {
                // Si el camp no es troba al view, s'afegeix completament
                if (!isset($projectViewDataFields[$key]['config']['fields'][$field])) {
                    $projectViewDataFields[$key]['config']['fields'][$field] = $fieldConfig;
                } else {
                    // Si es troba al view, es comprova que el valor no estigui configurat, i en aquest cas s'afegeix la configuració del config
                    foreach ($fieldConfig as $fieldConfigKey=>$fieldConfigValue) {
                        if (!isset($projectViewDataFields[$key]['config']['fields'][$field][$fieldConfigKey])) {
                            $projectViewDataFields[$key]['config']['fields'][$field][$fieldConfigKey] = $fieldConfigValue;
                        } // si ja es troba establert a la view no fem res, perquè aquest te prioritat

                    }
                }
            }

        }

    }

    public function getProjectType() {
        return $this->projectType;
    }

    public function getViewConfigName() {
        return $this->viewConfigName;
    }

    public function setViewConfigName($viewConfigName) {
        $this->viewConfigName = $viewConfigName;
    }

    public function setData($toSet) {
        // $toSet es genera a l'Action corresponent
        // JOSEPPPPP MIRA ESTOO
        //$this->projectMetaDataQuery->setMeta($toSet, $this->getMetaDataSubSet()); //acortando caminos, jejejeje
        $this->metaDataService->setMeta($toSet);
    }

    public function setDataProject($dataProject, $summary="") {
        $this->projectMetaDataQuery->setMeta($dataProject, $this->getMetaDataSubSet(), $summary);
    }

    public function getDraft($peticio=NULL) {
        //un draft distinto por cada subset de un proyecto (mismo id para todo el proyecto)
        $draft = $this->draftDataQuery->getFull($this->id.$this->getMetaDataSubSet());
        if ($peticio)
            return $draft[$peticio]; // $peticio = 'content' | 'date'
        else
            return $draft;
    }

    public function getAllDrafts() {
        $drafts = [];
        if ($this->hasDraft()) {
            $drafts['project'] = $this->getDraft();
        }
        return $drafts;
    }

    private function hasDraft(){
        return $this->draftDataQuery->hasFull($this->id.$this->getMetaDataSubSet());
    }

    public function saveDraft($draft) {
        //un draft distinto para cada subset de un proyecto (mismo id para todo el proyecto)
        $this->draftDataQuery->saveProjectDraft($draft, $this->getMetaDataSubSet());
    }

    public function removeDraft() {
        $this->draftDataQuery->removeProjectDraft($this->id.$this->getMetaDataSubSet());
    }

    /**
     * Devuelve un array con la estructura definida en el archivo configMain.json
     */
    public function getMetaDataDefKeys() {
        //Cambiado por traspaso desde Dao a ProjectMetaDataQuery
//        $dao = $this->metaDataService->getMetaDataDaoConfig();
//        $struct = $dao->getMetaDataStructure($this->getProjectType(),
//                                             $this->getMetaDataSubSet(),
//                                             $this->persistenceEngine);
        $defKeys = $this->projectMetaDataQuery->getMetaDataDefKeys();
        return json_decode($defKeys, TRUE);
    }

    // Verifica que el $subSet estigui definit a l'arxiu de configuració (configMain.json)
    public function validaSubSet($subSet) {
        $subSetList = $this->projectMetaDataQuery->getListMetaDataSubSets();
        return in_array($subSet, $subSetList);
    }

    //TODO PEL RAFA: AIXÒ HA DE PASSAR AL ProjectDataQuery
    //Obtiene un array [key, value] con los datos de una revisión específica del proyecto solicitado
    public function getDataRevisionProject($rev) {
        $file_revision = $this->projectMetaDataQuery->getFileName($this->id, [ProjectKeys::KEY_REV => $rev]);
        $jrev = gzfile($file_revision);
        $a = json_decode($jrev[0], TRUE);
        return $a[$this->getMetaDataSubSet()];
    }

    //TODO PEL RAFA: AIXÒ HA DE PASSAR AL ProjectDataQuery
    //Obtiene la fecha de una revisión específica del proyecto solicitado
    public function getDateRevisionProject($rev) {
        $file_revision = $this->projectMetaDataQuery->getFileName($this->id, [ProjectKeys::KEY_REV => $rev]);
        $date = @filemtime($file_revision);
        return $date;
    }

    /**
     * Indica si el proyecto ya existe
     * @return boolean
     */
    public function existProject() {
        return $this->projectMetaDataQuery->existProject();
    }

    /**
     * Indica si el proyecto ya ha sido generado
     * @return boolean
     */
    public function isProjectGenerated() {
        return $this->projectMetaDataQuery->isProjectGenerated();
    }

    public function getProjectSubSetAttr($att) {
        return $this->projectMetaDataQuery->getProjectSystemSubSetAttr($att);
    }

    public function setProjectSubSetAttr($att, $value) {
        return $this->projectMetaDataQuery->setProjectSystemSubSetAttr($att, $value);
    }

    public abstract function generateProject();

    //Del fichero _wikiIocSystem_.mdpr, del proyecto en curso, el elemento subSet solicitado
    public function getSystemData($subSet=FALSE) {
        return $this->projectMetaDataQuery->getSystemData($subSet);
    }

    public function setSystemData($data, $subSet=FALSE) {
        $this->projectMetaDataQuery->setSystemData($data, $subSet);
    }

    //Del fichero _wikiIocSystem_.mdpr del proyecto en curso, obtiene un atributo del subSet solicitado
    public function getProjectSystemSubSetAttr($attr, $subSet=NULL) {
        return $this->projectMetaDataQuery->getProjectSystemSubSetAttr($attr, $subSet);
    }

    public function setProjectSystemSubSetAttr($attr, $value, $subSet=NULL) {
        return $this->projectMetaDataQuery->setProjectSystemSubSetAttr($attr, $value, $subSet);
    }

    /*
     * Del archivo configMain.json, obtiene el atributo solicitado de la clave principal solicidada
     */
    public function getMetaDataAnyAttr($attr=NULL, $configMainKey=NULL) {
        return $this->projectMetaDataQuery->getMetaDataAnyAttr($attr, $configMainKey);
    }
    

    /**
     * @param integer $num Número de revisiones solicitadas El valor 0 significa obtener todas las revisiones
     * @return array  Contiene $num elementos de la lista de revisiones del fichero de proyecto obtenidas del log .changes
     */
    public function getProjectRevisionList($num=0) {
        $revs = $this->projectMetaDataQuery->getProjectRevisionList($num);
        if ($revs) {
            $amount = WikiGlobalConfig::getConf('revision-lines-per-page', 'wikiiocmodel');
            if (count($revs) > $amount) {
                $revs['show_more_button'] = true;
            }
            $r = $this->getActualRevision();
            $this->setActualRevision(TRUE);
            $revs['current'] = @filemtime($this->projectMetaDataQuery->getFileName($this->id));
            $this->setActualRevision($r);
            $revs['docId'] = $this->id;
            $revs['position'] = -1;
            $revs['amount'] = $amount;
        }
        return $revs;
    }

    public function getLastModFileDate() {
        return $this->projectMetaDataQuery->getLastModFileDate();
    }

    public function getProjectTypeConfigFile() {
        return $this->projectMetaDataQuery->getListMetaDataComponentTypes(ProjectKeys::KEY_METADATA_PROJECT_CONFIG,
                                                                          ProjectKeys::KEY_MD_PROJECTTYPECONFIGFILE);
    }

    public function getMetaDataComponent($projectType, $type){
        //$dao = $this->metaDataService->getMetaDataDaoConfig(); Anulado por TRASPASO a projectMetaDataQuery
        $set = $this->projectMetaDataQuery->getMetaDataComponentTypes($this->getMetaDataSubSet(), $projectType);
        if ($set) {
            $subset = $set[$type];
            $ret = is_array($subset) ? "array" : $subset;
        }
        return $ret;
    }

    public function preUpgradeProject($subSet) {
        if(class_exists("systemUpgrader")){
            $ret = systemUpgrader::preUpgrade($this, $subSet);
        }else{
            $ret = true;
        }
        return $ret;
    }
    
    public function createTemplateDocument($data){
        //NO HI HA TEMPLATES A CREAR
    }

    /**
     * Retorna el nom e la plantilla corresponent al document.
     *
     * @param array|string $responseData ruta de la plantilla, nom de la plantilla o objecte de configuració
     * @return string nom de la plantilla
     */
    public function getTemplateContentDocumentId($responseData){

        // Pot tractar-se del nom de la plantilla o una ruta, extraiem el nom i el retornem
        if (is_string($responseData)) {
            $plantilla = $responseData;

        } else {
            $plantilla = $responseData["plantilla"];

            if ($plantilla === NULL) {
                $plantilla = $responseData['projectMetaData']["plantilla"]['value'];
            }
        }

        $lastPos = strrpos($plantilla, ':');

        if ($lastPos) {
            $plantilla = substr($plantilla, $lastPos+1);
        }

        return $plantilla;

    }

    public function getTemplatePath($templateName, $version = null){
        $path = $this->getProjectMetaDataQuery()->getProjectTypeDir()."metadata/plantilles/" . $templateName . ".txt";

        if ($version) {
            $path .= "." . $version;
        } ;

        return $path;
    }

}
