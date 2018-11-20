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

    public function init($params, $projectType=NULL, $rev=NULL, $viewConfigName="defaultView", $metadataSubset=Projectkeys::KEY_DEFAULTSUBSET) {
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

    public function getMetaDataSubSet() {
        return ($this->metaDataSubSet) ? $this->metaDataSubSet : ProjectKeys::VAL_DEFAULTSUBSET;
    }

    public function isAlternateSubSet() {
        return ($this->metaDataSubSet && $this->metaDataSubSet !== ProjectKeys::VAL_DEFAULTSUBSET);
    }

    /**
     * Obtiene los datos del archivo de datos (meta.mdpr) de un proyecto
     */
    public function getMetaDataProject($metaDataSubset=FALSE) {
        $ret = $this->projectMetaDataQuery->getMeta($metaDataSubset);
        return json_decode($ret, true);
    }

    /**
     * Obtiene y, después, retorna una estructura con los metadatos y valores del proyecto
     * @return array('projectMetaData'=>array('values','structure'), array('projectViewData'))
     */
    public function getData() {
        $ret = [];
        if ($this->rev) {
            $query = [
                ProjectKeys::KEY_PERSISTENCE => $this->persistenceEngine,
                ProjectKeys::KEY_PROJECT_TYPE => $this->getProjectType(),
                ProjectKeys::KEY_METADATA_SUBSET => $this->getMetaDataSubSet(),
                ProjectKeys::KEY_ID_RESOURCE => $this->id,
                ProjectKeys::KEY_REV => $this->rev
            ];
        }else {
            $query = [
                ProjectKeys::KEY_PERSISTENCE => $this->persistenceEngine,
                ProjectKeys::KEY_PROJECT_TYPE => $this->getProjectType(),
                ProjectKeys::KEY_METADATA_SUBSET => $this->getMetaDataSubSet(),
                ProjectKeys::KEY_ID_RESOURCE => $this->id
            ];
        }
        $ret['projectMetaData'] = $this->metaDataService->getMeta($query, FALSE)[0];
        if ($this->isAlternateSubSet()){
            $ret[ProjectKeys::KEY_ID] .= $this->getMetaDataSubSet();
        }
        if ($this->rev){
            $ret[ProjectKeys::KEY_ID] .= ProjectKeys::REVISION_SUFFIX;
        }
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
        return $ret;
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
        $this->metaDataService->setMeta($toSet);
    }

    public function getDraft($peticio=NULL) {
        $draft = $this->draftDataQuery->getFull($this->id);
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
        return $this->draftDataQuery->hasFull($this->id);
    }

    public function saveDraft($draft) {
        $this->draftDataQuery->saveProjectDraft($draft);
    }

    public function removeDraft() {
        $this->draftDataQuery->removeProjectDraft($this->id);
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

    //Obtiene un array [key, value] con los datos del proyecto solicitado
    public function getDataProject() {
        return $this->projectMetaDataQuery->getDataProject();
    }

    // Verifica que el $subSet estigui definit a l'arxiu de configuració (configMain.json)
    public function validaSubSet($subSet) {
        $subSetList = $this->projectMetaDataQuery->getListMetaDataSubSets();
        return in_array($subSet, $subSetList);
    }

    //TODO PEL RAFA: AIXÒ HA DE PASSAR AL ProjectDataQuery
    //Obtiene un array [key, value] con los datos de una revisión específica del proyecto solicitado
    public function getDataRevisionProject($rev) {
        $file_revision = $this->_getProjectRevisionFile($rev);
        $jrev = gzfile($file_revision);
        $a = json_decode($jrev[0], TRUE);
        return $a[$this->getMetaDataSubSet()];
    }

    //TODO PEL RAFA: AIXÒ HA DE PASSAR AL ProjectDataQuery
    //Obtiene la fecha de una revisión específica del proyecto solicitado
    public function getDateRevisionProject($rev) {
        $file_revision = $this->_getProjectRevisionFile($rev);
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

    public abstract function generateProject();

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
            $revs['current'] = @filemtime($this->projectMetaDataQuery->getFileName($this->id));
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

}
