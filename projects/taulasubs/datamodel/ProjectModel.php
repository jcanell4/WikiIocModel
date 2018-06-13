<?php
/**
 * Description of ProjectModel
 * @culpable Rafael Claver
 */
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
if (!defined('WIKI_IOC_MODEL')) define('WIKI_IOC_MODEL', DOKU_PLUGIN . 'wikiiocmodel/');

require_once (DOKU_INC . 'inc/common.php');
require_once (DOKU_PLUGIN . "ajaxcommand/defkeys/PageKeys.php");
require_once (DOKU_PLUGIN . "ajaxcommand/defkeys/ProjectKeys.php");
require_once (WIKI_IOC_MODEL . "datamodel/AbstractWikiDataModel.php");
require_once (WIKI_IOC_MODEL . "datamodel/DokuPageModel.php");
require_once (WIKI_IOC_MODEL . "metadata/MetaDataService.php");
require_once (WIKI_IOC_MODEL . "authorization/PagePermissionManager.php");

class ProjectModel extends AbstractWikiDataModel {

    protected $id;
    protected $rev;
    protected $projectType;
    protected $projectFileName;
    protected $projectFilePath;
    protected $metaDataService;
    protected $persistenceEngine;
    protected $projectMetaDataQuery;
    protected $draftDataQuery;
    protected $lockDataQuery;
    //protected $pageDataQuery;
    protected $dokuPageModel;

    public function __construct($persistenceEngine)  {
        parent::__construct($persistenceEngine);
        $this->metaDataService= new MetaDataService();
        $this->projectMetaDataQuery = $persistenceEngine->createProjectMetaDataQuery();
        $this->draftDataQuery = $persistenceEngine->createDraftDataQuery();
        $this->lockDataQuery = $persistenceEngine->createLockDataQuery();
        $this->pageDataQuery = $persistenceEngine->createPageDataQuery();
        $this->dokuPageModel = new DokuPageModel($persistenceEngine);
    }

    public function init($id, $projectType=NULL, $rev=NULL, $projectFileName=NULL) {
        $this->id = $id;
        $this->projectType = $projectType;
        $this->rev = $rev;
        if ($id && $projectType) {
            $this->projectMetaDataQuery->init([ProjectKeys::KEY_ID => $id,
                                               ProjectKeys::KEY_PROJECT_TYPE => $projectType]);
        }
        $this->setProjectFileName($projectFileName);
        $this->setProjectFilePath();
    }

    public function setData($toSet) {
        // $toSet es genera a l'Action corresponent
        $this->metaDataService->setMeta($toSet);
    }

    /**
     * Obtiene y, después, retorna una estructura con los metadatos y valores del proyecto
     * @return array(array('projectMetaData'), array('projectViewData'))
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
     * Valida que exista el nombre de usuario que se desea utilizar
     */
    public function validaNom($nom) {
        global $auth;
        return ($auth->getUserCount(['user' => $nom]) > 0);
    }

    /**
     * Devuelve un array con la estructura definida en el archivo configMain.json
     */
    public function getMetaDataDefKeys($projectType) {
        $dao = $this->metaDataService->getMetaDataDaoConfig();
        $struct = $dao->getMetaDataStructure($projectType, ProjectKeys::VAL_DEFAULTSUBSET, $this->persistenceEngine);
        return json_decode($struct, TRUE);
    }

    public function getMetaDataComponent($projectType, $type){
        $dao = $this->metaDataService->getMetaDataDaoConfig();
        $set = $dao->getMetaDataComponentTypes($projectType, ProjectKeys::VAL_DEFAULTSUBSET, $this->persistenceEngine);
        $subset = $set[$type];
        $ret = is_array($subset) ? "array" : $subset;
        return $ret;
    }

    private function setProjectFileName($projectFileName=NULL) {
        if ($projectFileName) {
            $this->projectFileName = $projectFileName;
        }else {
            $a = array(ProjectKeys::KEY_ID => $this->id,
                       ProjectKeys::KEY_PROJECT_TYPE    => $this->projectType,
                       ProjectKeys::KEY_METADATA_SUBSET => ProjectKeys::VAL_DEFAULTSUBSET
                      );
            $this->projectFileName = $this->projectMetaDataQuery->getProjectFileName($a);
        }
    }

    private function getProjectFileName() {
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

    public function createDataDir($id) {
        $this->projectMetaDataQuery->createDataDir($id);
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

    public function generateProject($id, $projectType) {
        //0. Obtiene los datos del proyecto
        $ret = $this->getData();   //obtiene la estructura y el contenido del proyecto
        $plantilla = $ret['projectMetaData']["plantilla"]['value'];
        $ret['projectMetaData']["fitxercontinguts"]['value'] = $destino = "$id:".end(explode(":", $plantilla));

        //1. Crea el archivo 'continguts', en la carpeta del proyecto, a partir de la plantilla especificada
        $this->createPageFromTemplate($destino, $plantilla, NULL, "generate project");

        //2. Establece la marca de 'proyecto generado'
        $this->projectMetaDataQuery->setProjectGenerated($id, $projectType);

        //3a. Otorga, al Autor, permisos sobre el directorio de proyecto
        PagePermissionManager::updatePagePermission($id.":*", $ret['projectMetaData']["autor"]['value'], AUTH_UPLOAD);

        //3b. Otorga, al Responsable, permisos sobre el directorio de proyecto
        if ($ret['projectMetaData']["autor"]['value'] !== $ret['projectMetaData']["responsable"]['value'])
            PagePermissionManager::updatePagePermission($id.":*", $ret['projectMetaData']["responsable"]['value'], AUTH_UPLOAD);

        //4a. Otorga permisos al autor sobre su propio directorio (en el caso de que no los tenga)
        $ns = WikiGlobalConfig::getConf('userpage_ns','wikiiocmodel').$ret['projectMetaData']["autor"]['value'].":";
        PagePermissionManager::updatePagePermission($ns."*", $ret['projectMetaData']["autor"]['value'], AUTH_DELETE, TRUE);
        //4b. Incluye la página del proyecto en el archivo de atajos del Autor
        $params = [
             'id' => $id
            ,'autor' => $ret['projectMetaData']["autor"]['value']
            ,'link_page' => $ret['projectMetaData']["fitxercontinguts"]['value']
            ,'user_shortcut' => $ns.WikiGlobalConfig::getConf('shortcut_page_name','wikiiocmodel')
        ];
        $this->includePageProjectToUserShortcut($params);

        return $ret;
    }

    /**
     * Modifica los permisos en el fichero de ACL y la página de atajos del autor
     * cuando se modifica el autor o el responsable del proyecto
     * @param array $parArr ['id','link_page','old_autor','old_responsable','new_autor','new_responsable','userpage_ns','shortcut_name']
     */
    public function modifyACLPageToUser($parArr) {
        $project_ns = $parArr['id'].":*";

        //Se ha modificado el Autor del proyecto
        if ($parArr['old_autor'] !== $parArr['new_autor']) {
            if ($parArr['old_autor'] !== $parArr['old_responsable']) {
                //Elimina ACL de old_autor sobre la página del proyecto
                $ret = PagePermissionManager::deletePermissionPageForUser($project_ns, $parArr['old_autor']);
                if (!$ret) $retError[] = "Error en eliminar permissos a '${parArr['old_autor']}' sobre '$project_ns'";
            }
            //Elimina el acceso a la página del proyecto en el archivo dreceres de de old_autor
            $old_usershortcut = $parArr['userpage_ns'].$parArr['old_autor'].":".$parArr['shortcut_name'];
            $this->removeProjectPageFromUserShortcut($old_usershortcut, $parArr['link_page']);

            //Crea ACL para new_autor sobre la página del proyecto
            $ret = PagePermissionManager::updatePagePermission($project_ns, $parArr['new_autor'], AUTH_UPLOAD, TRUE);
            if (!$ret) $retError[] = "Error en assignar permissos a '${parArr['new_autor']}' sobre '$project_ns'";

            //Otorga permisos al autor sobre su propio directorio (en el caso de que no los tenga)
            $ns = $parArr['userpage_ns'].$parArr['new_autor'].":";
            PagePermissionManager::updatePagePermission($ns."*", $parArr['new_autor'], AUTH_DELETE, TRUE);
            //Escribe un acceso a la página del proyecto en el archivo de atajos de de new_autor
            $params = [
                 'id' => $parArr['id']
                ,'autor' => $parArr['new_autor']
                ,'link_page' => $parArr['link_page']
                ,'user_shortcut' => $ns.$parArr['shortcut_name']
            ];
            $this->includePageProjectToUserShortcut($params);
        }

        //Se ha modificado el Responsable del proyecto
        if ($parArr['old_responsable'] !== $parArr['new_responsable']) {
            if ($parArr['old_autor'] !== $parArr['old_responsable']) {
                //Elimina ACL de old_responsable sobre la página del proyecto
                $ret = PagePermissionManager::deletePermissionPageForUser($project_ns, $parArr['old_responsable']);
                if (!$ret) $retError[] = "Error en eliminar permissos a '${parArr['old_responsable']}' sobre '$project_ns'";
            }
            //Crea ACL para new_responsable sobre la página del proyecto
            $ret = PagePermissionManager::updatePagePermission($project_ns, $parArr['new_responsable'], AUTH_UPLOAD, TRUE);
            if (!$ret) $retError[] = "Error en assignar permissos a '${parArr['new_responsable']}' sobre '$project_ns'";
        }

        if ($retError) {
            foreach ($retError as $e) {
                throw new UnknownProjectException($project_ns, $e);
            }
        }
    }

    /**
     * Inserta en la página de dreceres del usuario un texto con enlace al proyecto
     * Si la página dreceres.txt del usuario no existe, se crea a partir de la plantilla 'userpage_shortcuts_ns'
     * @param array $parArr ['id', 'autor', 'link_page', 'user_shortcut']
     */
    private function includePageProjectToUserShortcut($parArr) {
        $summary = "include Page Project To User Shortcut";
        $shortcutText = "\n[[${parArr['link_page']}|accés als continguts del projecte ${parArr['id']}]]";
        $text = $this->pageDataQuery->getRaw($parArr['user_shortcut']);
        if ($text == "") {
            //La página dreceres.txt del usuario no existe
            $this->createPageFromTemplate($parArr['user_shortcut'], WikiGlobalConfig::getConf('template_shortcuts_ns', 'wikiiocmodel'), $shortcutText, $summary);
        }else {
            if (preg_match("/${parArr['link_page']}/", $text) === 1) {
                $eliminar = "/\[\[${parArr['link_page']}\|.*]]/";
                $text = preg_replace($eliminar, "", $text); //texto hallado -> eliminamos antiguo
            }
            $this->createPageFromTemplate($parArr['user_shortcut'], NULL, $text.$shortcutText, $summary);
        }
    }

    /**
     * Elimina el link al proyecto contenido en el archivo dreceres del usuario
     */
    private function removeProjectPageFromUserShortcut($usershortcut, $link_page) {
        $text = $this->pageDataQuery->getRaw($usershortcut);
        if ($text !== "" ) {
            if (preg_match("/$link_page/", $text) === 1) {  //subtexto hallado
                $eliminar = "/\[\[$link_page\|.*]]/";
                $text = preg_replace($eliminar, "", $text);
                $this->createPageFromTemplate($usershortcut, NULL, $text, "removeProjectPageFromUserShortcut");
            }
        }
    }

    /**
     * Crea el archivo $destino a partir de una plantilla
     */
    private function createPageFromTemplate($destino, $plantilla=NULL, $extra=NULL, $summary="generate project") {
        $text = ($plantilla) ? $this->pageDataQuery->getRaw($plantilla) : "";
        $this->dokuPageModel->setData([PageKeys::KEY_ID => $destino,
                                       PageKeys::KEY_WIKITEXT => $text . $extra,
                                       PageKeys::KEY_SUM => $summary]);
    }

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

    //[JOSEP] Alerta caldria pujar aquest mètode a abstractwikdatamodel
    public function createFolder($new_folder){
        return $this->projectMetaDataQuery->createFolder(str_replace(":", "/", $new_folder));
    }

    //[JOSEP] Alerta caldria pujar aquest mètode a abstractwikdatamodel
    public function folderExists($ns) {
        $id = str_replace(":", "/", $ns);
        return file_exists($id) && is_dir($id);
    }

}
