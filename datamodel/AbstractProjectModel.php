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
    protected $projectFileName;
    protected $projectFilePath;
    protected $metaDataSubSet;
    protected $projectTypeDir;  //ruta, a lib/plugins/.../, del tipus de projecte

    //protected $persistenceEngine; Ya está definida en AbstractWikiModel
    protected $metaDataService;
    protected $draftDataQuery;
    protected $lockDataQuery;
    protected $dokuPageModel;

    public function __construct($persistenceEngine, $projectTypeDir=NULL)  {
        parent::__construct($persistenceEngine);
        $this->metaDataService= new MetaDataService($projectTypeDir);
        $this->draftDataQuery = $persistenceEngine->createDraftDataQuery();
        $this->lockDataQuery = $persistenceEngine->createLockDataQuery();
        $this->dokuPageModel = new DokuPageModel($persistenceEngine);
        $this->projectTypeDir = $projectTypeDir;
    }

    public function init($params) {
        $this->id          = $params[ProjectKeys::KEY_ID];
        $this->projectType = $params[ProjectKeys::KEY_PROJECT_TYPE];
        $this->rev         = $params[ProjectKeys::KEY_REV];
        $this->metaDataSubSet = ($params[ProjectKeys::KEY_METADATA_SUBSET]) ? $params[ProjectKeys::KEY_METADATA_SUBSET] : ProjectKeys::VAL_DEFAULTSUBSET;
        if ($params[ProjectKeys::KEY_PROJECTTYPE_DIR])
            $this->projectTypeDir = $params[ProjectKeys::KEY_PROJECTTYPE_DIR];
        $this->setProjectFileName($params[ProjectKeys::KEY_PROJECT_FILENAME]);
        $this->setProjectFilePath();
    }

    public function getModelAttributes($key=NULL){
        $attr[ProjectKeys::KEY_ID] = $this->id;
        $attr[ProjectKeys::KEY_PROJECT_TYPE] = $this->getProjectType();
        $attr[ProjectKeys::KEY_REV] = $this->rev;
        $attr[ProjectKeys::KEY_METADATA_SUBSET] = $this->getMetaDataSubSet();
        $attr[ProjectKeys::KEY_PROJECTTYPE_DIR] = $this->getProjectTypeDir();
        $attr[ProjectKeys::KEY_PROJECT_FILENAME] = $this->projectFileName;
        $attr[ProjectKeys::KEY_PROJECT_FILEPATH] = $this->projectFilePath;
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
    public function getMetaDataProject($projectFileName, $metaDataSubset) {
        $ret = $this->projectMetaDataQuery->getMeta($this->id, $this->getProjectType(), $metaDataSubset, $projectFileName);
        return json_decode($ret, true);
    }

    /**
     * Obtiene y, después, retorna una estructura con los metadatos y valores del proyecto
     * @return array('projectMetaData'=>array('values','structure'), array('projectViewData'))
     */
    public function getData() {
        $ret = [];
        $projectTypeDir = $this->getProjectTypeDir();
        if ($this->rev) {
            $revision_file = $this->_getProjectRevisionFile($this->rev);
            $query = [
                ProjectKeys::KEY_PERSISTENCE => $this->persistenceEngine,
                ProjectKeys::KEY_PROJECT_TYPE => $this->getProjectType(),
                ProjectKeys::KEY_METADATA_SUBSET => $this->getMetaDataSubSet(),
                ProjectKeys::KEY_ID_RESOURCE => $this->id . ProjectKeys::REVISION_SUFFIX,
                ProjectKeys::KEY_PROJECT_FILENAME => $revision_file,
                ProjectKeys::KEY_PROJECTTYPE_DIR => $projectTypeDir
            ];
        }else {
            $s = $this->isAlternateSubSet() ? $this->getMetaDataSubSet() : "";
            $query = [
                ProjectKeys::KEY_PERSISTENCE => $this->persistenceEngine,
                ProjectKeys::KEY_PROJECT_TYPE => $this->getProjectType(),
                ProjectKeys::KEY_METADATA_SUBSET => $this->getMetaDataSubSet(),
                ProjectKeys::KEY_ID_RESOURCE => $this->id . $s,
                ProjectKeys::KEY_PROJECT_FILENAME => $this->getProjectFileName(),
                ProjectKeys::KEY_PROJECTTYPE_DIR => $projectTypeDir
            ];
        }
        $subSet = json_decode($this->projectMetaDataQuery
                                   ->getMetaDataConfig($this->getProjectType(),
                                                       ProjectKeys::KEY_METADATA_PROJECT_STRUCTURE,
                                                       $this->getMetaDataSubSet(),
                                                       $projectTypeDir),
                              true);
        $ret['projectMetaData'] = $this->metaDataService->getMeta($query, FALSE)[0];
        if (!$ret['projectMetaData']) {
            //si todavía no hay datos en el fichero de proyecto se recoge la lista de campos del tipo de proyecto
            $typeDef = $subSet['mainType']['typeDef'];
            $keys = $subSet['typesDefinition'][$typeDef]['keys'];
            foreach ($keys as $k => $v) {
                $metaData[$k] = ($v['default']) ? $v['default'] : "";
            }
            $ret['projectMetaData'] = $metaData;
        }
        $viewfile = ($subSet['viewfiles'][0]) ? $subSet['viewfiles'][0] : ProjectKeys::KEY_DEFAULTVIEW;
        $ret['projectViewData'] = $this->projectMetaDataQuery->getMetaViewConfig($this->getProjectType(), $viewfile, $projectTypeDir);
        return $ret;
    }

    public function getProjectType() {
        return $this->projectType;
    }

    public function getProjectTypeDir(){
        return ($this->projectTypeDir) ? $this->projectTypeDir : DokuModelManager::getProjectTypeDir();
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
        $dao = $this->metaDataService->getMetaDataDaoConfig();
        $struct = $dao->getMetaDataStructure($this->getProjectType(),
                                             $this->getMetaDataSubSet(),
                                             $this->persistenceEngine,
                                             $this->getProjectTypeDir());
        return json_decode($struct, TRUE);
    }

    private function setProjectFileName($projectFileName=NULL) {
        if ($projectFileName) {
            $this->projectFileName = $projectFileName;
        }else {
            $parm = [ProjectKeys::KEY_PROJECT_TYPE    => $this->getProjectType(),
                     ProjectKeys::KEY_METADATA_SUBSET => $this->getMetaDataSubSet(),
                     ProjectKeys::KEY_PROJECTTYPE_DIR => $this->getProjectTypeDir()
                    ];
            $this->projectFileName = $this->projectMetaDataQuery->getProjectFileName($parm);
        }
    }

    public function getProjectFileName() {
        if (!$this->projectFileName) {
            $this->setProjectFileName();
        }
        return $this->projectFileName;
    }

    private function setProjectFilePath() {
        $parm = [ProjectKeys::KEY_ID => $this->id,
                 ProjectKeys::KEY_PROJECT_TYPE => $this->getProjectType()
                ];
        $this->projectFilePath = $this->projectMetaDataQuery->getProjectFilePath($parm);
    }

    public function getProjectFilePath() {
        if (!$this->projectFilePath) {
            $this->setProjectFilePath();
        }
        return $this->projectFilePath;
    }

    public function getProjectAbsFilePath() {
        return $this->getProjectFilePath() . $this->getProjectFileName();
    }

    private function _getProjectRevisionFile($rev) {
        return $this->projectMetaDataQuery->revisionProjectDir($this->id) . "{$this->projectFileName}.$rev.txt.gz";
    }

    //Obtiene un array [key, value] con los datos del proyecto solicitado
    public function getDataProject() {
        $parm = [ProjectKeys::KEY_ID => $this->id,
                 ProjectKeys::KEY_PROJECT_TYPE    => $this->getProjectType(),
                 ProjectKeys::KEY_PROJECTTYPE_DIR => $this->getProjectTypeDir(),
                 ProjectKeys::KEY_METADATA_SUBSET => $this->getMetaDataSubSet()
                ];
        return $this->projectMetaDataQuery->getDataProject($parm);
    }

    // Verifica que el $subSet estigui definit a l'arxiu de configuració (configMain.json)
    public function validaSubSet($subSet) {
        $subSetList = $this->projectMetaDataQuery->getListMetaDataSubSets($this->getProjectType(), $this->getProjectTypeDir());
        return in_array($subSet, $subSetList);
    }

    //Obtiene un array [key, value] con los datos de una revisión específica del proyecto solicitado
    public function getDataRevisionProject($rev) {
        $file_revision = $this->_getProjectRevisionFile($rev);
        $jrev = gzfile($file_revision);
        $a = json_decode($jrev[0], TRUE);
        return $a[$this->getMetaDataSubSet()];
    }

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
        return $this->projectMetaDataQuery->existProject($this->id);
    }

    /**
     * Indica si el proyecto ya ha sido generado
     * @return boolean
     */
    public function isProjectGenerated() {
        return $this->projectMetaDataQuery->isProjectGenerated($this->id, $this->getProjectType());
    }

    public abstract function generateProject();

    /**
     * @param integer $num Número de revisiones solicitadas El valor 0 significa obtener todas las revisiones
     * @return array  Contiene $num elementos de la lista de revisiones del fichero de proyecto obtenidas del log .changes
     */
    public function getProjectRevisionList($num=0) {
        $revs = $this->projectMetaDataQuery->getProjectRevisionList($this->id, $this->projectFileName, $num);
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
        return $this->projectMetaDataQuery->getLastModFileDate($this->id);
    }

    public function getProjectTypeConfigFile($projectType, $metaDataSubSet=NULL) {
        if (!$metaDataSubSet) $metaDataSubSet = $this->getMetaDataSubSet();
        return $this->projectMetaDataQuery->getListMetaDataComponentTypes($projectType,
                                                                          ProjectKeys::KEY_METADATA_PROJECT_CONFIG,
                                                                          $metaDataSubSet,
                                                                          ProjectKeys::KEY_MD_PROJECTTYPECONFIGFILE,
                                                                          $this->getProjectTypeDir());
    }

    public function getMetaDataComponent($projectType, $type){
        $dao = $this->metaDataService->getMetaDataDaoConfig();
        $set = $dao->getMetaDataComponentTypes($projectType,
                                               $this->getMetaDataSubSet(),
                                               $this->persistenceEngine,
                                               $this->getProjectTypeDir());
        $subset = $set[$type];
        $ret = is_array($subset) ? "array" : $subset;
        return $ret;
    }

}
