<?php
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_PLUGIN . 'wikiiocmodel/');

require_once (DOKU_INC . 'inc/common.php');
require_once (DOKU_PLUGIN . "ajaxcommand/defkeys/PageKeys.php");
require_once (DOKU_PLUGIN . "ajaxcommand/defkeys/ProjectKeys.php");
require_once (WIKI_IOC_MODEL . "datamodel/AbstractWikiDataModel.php");
require_once (WIKI_IOC_MODEL . "datamodel/DokuPageModel.php");
require_once (WIKI_IOC_MODEL . "metadata/MetaDataService.php");

/**
 * Description of BaseProjectModel
 *
 * @author professor
 */
abstract class AbstractProjectModel extends AbstractWikiDataModel{
    protected $id;
    protected $rev;
    protected $projectType;
    protected $projectFileName;
    protected $projectFilePath;
    protected $metaDataService;
    protected $persistenceEngine;
    protected $draftDataQuery;
    protected $lockDataQuery;
    protected $pageDataQuery;
    protected $dokuPageModel;

    public function __construct($persistenceEngine)  {
        parent::__construct($persistenceEngine);
        $this->metaDataService= new MetaDataService();
        $this->draftDataQuery = $persistenceEngine->createDraftDataQuery();
        $this->lockDataQuery = $persistenceEngine->createLockDataQuery();
        $this->dokuPageModel = new DokuPageModel($persistenceEngine);
    }

    public function init($id, $projectType=NULL, $rev=NULL, $projectFileName=NULL) {
        $this->id = $id;
        $this->projectType = $projectType;
        $this->rev = $rev;
        $this->setProjectFileName($projectFileName);
        $this->setProjectFilePath();
    }

    /**
     * Obtiene y, después, retorna una estructura con los metadatos y valores del proyecto
     * @return array('projectMetaData'=>array('values','structure'), array('projectViewData'))
     */
    public function getData() {
        $ret = [];
        if ($this->rev) {
            $revision_file = $this->getProjectRevisionFile($this->id, $this->rev);
            $query = [
                ProjectKeys::KEY_PERSISTENCE => $this->persistenceEngine,
                ProjectKeys::KEY_PROJECT_TYPE => $this->projectType,
                ProjectKeys::KEY_METADATA_SUBSET => ProjectKeys::VAL_DEFAULTSUBSET,
                ProjectKeys::KEY_ID_RESOURCE => $this->id . ProjectKeys::REVISION_SUFFIX,
                ProjectKeys::KEY_PROJECT_FILENAME => $revision_file
            ];
        }else {
            $query = [
                ProjectKeys::KEY_PERSISTENCE => $this->persistenceEngine,
                ProjectKeys::KEY_PROJECT_TYPE => $this->projectType,
                ProjectKeys::KEY_METADATA_SUBSET => ProjectKeys::VAL_DEFAULTSUBSET,
                ProjectKeys::KEY_ID_RESOURCE => $this->id,
                ProjectKeys::KEY_PROJECT_FILENAME => $this->getProjectFileName()
            ];
        }
        $ret['projectMetaData'] = $this->metaDataService->getMeta($query, FALSE)[0];
        $ret['projectViewData'] = $this->projectMetaDataQuery->getMetaViewConfig($this->projectType, "defaultView");
        return $ret;
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
    public function getMetaDataDefKeys($projectType) {
        $dao = $this->metaDataService->getMetaDataDaoConfig();
        $struct = $dao->getMetaDataStructure($projectType, ProjectKeys::VAL_DEFAULTSUBSET, $this->persistenceEngine);
        return json_decode($struct, TRUE);
    }

    private function setProjectFileName($projectFileName=NULL) {
        if ($projectFileName) {
            $this->projectFileName = $projectFileName;
        }else {
            $a = array('id' => $this->id,
                       ProjectKeys::KEY_PROJECT_TYPE    => $this->projectType,
                       ProjectKeys::KEY_METADATA_SUBSET => ProjectKeys::VAL_DEFAULTSUBSET
                      );
            $this->projectFileName = $this->projectMetaDataQuery->getProjectFileName($a);
        }
    }

    public function getProjectFileName() {
        if (!$this->projectFileName) {
            $this->setProjectFileName();
        }
        return $this->projectFileName;
    }

    private function setProjectFilePath() {
        $this->projectFilePath = $this->projectMetaDataQuery->getProjectFilePath();
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

    public function getProjectRevisionFile($id, $rev) {
        return $this->projectMetaDataQuery->revisionProjectDir($id) . "{$this->projectFileName}.$rev.txt.gz";
    }

    //Obtiene un array [key, value] con los datos del proyecto solicitado
    public function getDataProject($id, $projectType) {
        return $this->projectMetaDataQuery->getDataProject($id, $projectType);
    }

    //Obtiene un array [key, value] con los datos de una revisión específica del proyecto solicitado
    public function getDataRevisionProject($id, $rev) {
        $file_revision = $this->getProjectRevisionFile($id, $rev);
        $jrev = gzfile($file_revision);
        $a = json_decode($jrev[0], TRUE);
        return $a[ProjectKeys::VAL_DEFAULTSUBSET];
    }

    //Obtiene la fecha de una revisión específica del proyecto solicitado
    public function getDateRevisionProject($id, $rev) {
        $file_revision = $this->getProjectRevisionFile($id, $rev);
        $date = @filemtime($file_revision);
        return $date;
    }

    /**
     * Indica si el proyecto ya existe
     * @return boolean
     */
    public function existProject($id) {
        return $this->projectMetaDataQuery->existProject($id);
    }

    /**
     * Indica si el proyecto ya ha sido generado
     * @return boolean
     */
    public function isProjectGenerated($id, $projectType) {
        return $this->projectMetaDataQuery->isProjectGenerated($id, $projectType);
    }

    public abstract function generateProject($id, $projectType);

    /**
     * @param integer $num Número de revisiones solicitadas El valor 0 significa obtener todas las revisiones
     * @return array  Contiene $num elementos de la lista de revisiones del fichero de proyecto obtenidas del log .changes
     */
    public function getProjectRevisionList($id, $num=0) {
        $revs = $this->projectMetaDataQuery->getProjectRevisionList($id, $num);
        if ($revs) {
            $amount = WikiGlobalConfig::getConf('revision-lines-per-page', 'wikiiocmodel');
            if (count($revs) > $amount) {
                $revs['show_more_button'] = true;
            }
            $revs['current'] = @filemtime($this->projectMetaDataQuery->getFileName($id));
            $revs['docId'] = $id;
            $revs['position'] = -1;
            $revs['amount'] = $amount;
        }
        return $revs;
    }

    public function getLastModFileDate($id) {
        return $this->projectMetaDataQuery->getLastModFileDate($id);
    }

    public function getMetaDataComponent($projectType, $type){
        $dao = $this->metaDataService->getMetaDataDaoConfig();
        $set = $dao->getMetaDataComponentTypes($projectType, ProjectKeys::VAL_DEFAULTSUBSET, $this->persistenceEngine);
        $subset = $set[$type];
        $ret = is_array($subset) ? "array" : $subset;
        return $ret;
    }

}

