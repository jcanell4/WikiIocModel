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
    protected $projectType;
    protected $projectFileName;
    protected $projectFilePath;
    protected $metaDataService;
    protected $persistenceEngine;
    protected $projectMetaDataQuery;
    protected $pageDataQuery;
    protected $dokuPageModel;

    public function __construct($persistenceEngine)  {
        parent::__construct($persistenceEngine);
        $this->metaDataService= new MetaDataService();
        $this->projectMetaDataQuery = $persistenceEngine->createProjectMetaDataQuery();
        $this->pageDataQuery = $persistenceEngine->createPageDataQuery();
        $this->dokuPageModel = new DokuPageModel($persistenceEngine);
    }

    public function init($id, $projectType=NULL, $projectFileName=NULL) {
        $this->id = $id;
        $this->projectType = $projectType;
        $this->setProjectFileName($projectFileName);
    }

    public function setData($toSet) {
        $ret = [];
        // En aquest cas el $toSet equival al $query, que es genera al Action corresponent
        $meta = $this->metaDataService->setMeta($toSet);
        /* El retorn es un array, agrupat:
            // primer nivell: project-type
            // segon nivell: idResource
           Per tant, aquí sempre voldrem el [0][0] perquè només demanem un id i un projecttype */
        $metaJSON = json_decode($meta[0][0], true);
        $ret['projectMetaData']['values'] = json_decode($metaJSON['metaDataValue'], true);
        $ret['projectMetaData']['values']['idResource'] = $metaJSON['idResource'];
        $ret['projectMetaData']['structure'] = json_decode($metaJSON['metaDataStructure'], true);

        return $ret;
    }

    /**
     * Obtiene y, después, retorna una estructura con los metadatos y valores del proyecto
     * @return array('projectMetaData'=>array('values','structure'), array('projectViewData'))
     */
    public function getData() {
        $ret = [];
        $query = [
            ProjectKeys::KEY_PERSISTENCE => $this->persistenceEngine,
            ProjectKeys::KEY_PROJECT_TYPE => $this->projectType,
            ProjectKeys::KEY_METADATA_SUBSET => ProjectKeys::VAL_DEFAULTSUBSET,
            ProjectKeys::KEY_ID_RESOURCE => $this->id,
            ProjectKeys::KEY_PROJECT_FILENAME => $this->getProjectFileName()
        ];
        $meta = $this->metaDataService->getMeta($query, FALSE)[0];
        $ret['projectMetaData']['values'] = $meta['values'];
        $ret['projectMetaData']['structure'] = $meta['structure']; //inclou els valors
        $ret['projectViewData'] = $this->projectMetaDataQuery->getMetaViewConfig($this->projectType, "defaultView");
        return $ret;
    }

    /* Valida que exista el nombre de usuario que se desea utilizar */
    public function validaNomAutor($nom) {
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

    public function setProjectFileName($projectFileName=NULL) {
        if ($projectFileName) {
            $this->projectFileName = $projectFileName;
        }else {
            $a = array(ProjectKeys::KEY_PROJECT_TYPE    => $this->projectType,
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

    public function createDataDir($id) {
        $this->projectMetaDataQuery->createDataDir($id);
    }

    /**
     * Indica si el proyecto ya existe
     * @return boolean
     */
    public function existProject($id) {
        return $this->projectMetaDataQuery->haveADirProject($id);
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
        $plantilla = $ret['projectMetaData']['values']["plantilla"];
        $ret['projectMetaData']['values']["fitxercontinguts"] = $destino = "$id:".end(explode(":", $plantilla));

        //1. Crea el archivo 'continguts', en la carpeta del proyecto, a partir de la plantilla especificada
        $this->createPageFromTemplate($destino, $plantilla, NULL, "generate project");

        //2. Establece la marca de 'proyecto generado'
        $this->projectMetaDataQuery->setProjectGenerated($id, $projectType);

        //3a. Otorga, al Autor, permisos sobre el directorio de proyecto
        PagePermissionManager::updatePagePermission($id.":*", $ret['projectMetaData']['values']["autor"], AUTH_UPLOAD);

        //3b. Otorga, al Responsable, permisos sobre el directorio de proyecto
        if ($ret['projectMetaData']['values']["autor"] !== $ret['projectMetaData']['values']["responsable"])
            PagePermissionManager::updatePagePermission($id.":*", $ret['projectMetaData']['values']["responsable"], AUTH_UPLOAD);

        //4a. Otorga permisos al autor sobre su propio directorio (en el caso de que no los tenga)
        $ns = WikiGlobalConfig::getConf('userpage_ns','wikiiocmodel').$ret['projectMetaData']['values']["autor"].":";
        PagePermissionManager::updatePagePermission($ns."*", $ret['projectMetaData']['values']["autor"], AUTH_DELETE, TRUE);
        //4b. Incluye la página del proyecto en el archivo de atajos del Autor
        $params = [
             'id' => $id
            ,'autor' => $ret['projectMetaData']['values']["autor"]
            ,'link_page' => $ret['projectMetaData']['values']["destino"]
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
    public function includePageProjectToUserShortcut($parArr) {
        $summary = "include Page Project To User Shortcut";
        $shortcutText = "\n[[${parArr['link_page']}|accés al projecte ${parArr['id']}]]";
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
    public function removeProjectPageFromUserShortcut($usershortcut, $link_page) {
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
        /* antiguo modelo
        $action = new CreatePageAction($this->persistenceEngine);
        $contentData = $action->get([PageKeys::KEY_ID => $destino,
                                     PageKeys::KEY_WIKITEXT => $text,
                                     PageKeys::KEY_SUM => $summary]);
        return $contentData;*/
    }

}
