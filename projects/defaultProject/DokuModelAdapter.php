<?php
//namespace ioc_dokuwiki; //[TO DO Josep] Adaptar la classe a  l'espai de noms

/**
 * Description of DokuModelAdapter
 *
 * @author Josep Cañellas Bornas<jcanell4@ioc.cat>
 * @author Rafael Claver Oñate<rclaver@xtec.cat>
 * @author Xavier Gracía Rodríguez <xaviergarodev@gmail.com>
 * @author Miguel Àngel Lozano Márquez<mlozan54@ioc.cat>
 */
if (!defined('DOKU_INC')) die();

//require common
require_once(DOKU_INC . 'inc/actions.php');
require_once(DOKU_INC . 'inc/pageutils.php');
require_once(DOKU_INC . 'inc/parserutils.php');//Eliminar quan es canvïi p_cached_instructions a PageDataQuery
require_once(DOKU_INC . 'inc/common.php');
require_once(DOKU_INC . 'inc/media.php');
require_once(DOKU_INC . 'inc/auth.php');
require_once(DOKU_INC . 'inc/confutils.php');
require_once(DOKU_INC . 'inc/io.php');
//require_once (DOKU_INC . 'inc/template.php');
require_once(DOKU_INC . 'inc/JSON.php');
require_once(DOKU_INC . 'inc/JpegMeta.php');

if (!defined('DOKU_PLUGIN')) {
    define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
}
require_once(DOKU_PLUGIN . 'wikiiocmodel/AbstractModelAdapter.php');
require_once(DOKU_PLUGIN . 'wikiiocmodel/WikiIocInfoManager.php');
require_once(DOKU_PLUGIN . 'ownInit/WikiGlobalConfig.php');
require_once(DOKU_PLUGIN . 'wikiiocmodel/projects/defaultProject/PermissionPageForUserManager.php');
require_once(DOKU_PLUGIN . 'wikiiocmodel/projects/defaultProject/DokuModelExceptions.php');

require_once(DOKU_PLUGIN . 'acl/admin.php');

// TODO[Xavi] Afegit per mi per extreure la funcionalitat dels locks a una altra classe
require_once(DOKU_PLUGIN . 'wikiiocmodel/LockManager.php');
require_once(DOKU_PLUGIN . 'wikiiocmodel/DraftManager.php');

require_once(DOKU_PLUGIN . 'wikiiocmodel/projects/defaultProject/actions/AdminTaskAction.php');
require_once(DOKU_PLUGIN . 'wikiiocmodel/projects/defaultProject/actions/AdminTaskListAction.php');
require_once(DOKU_PLUGIN . 'wikiiocmodel/projects/defaultProject/actions/RefreshEditionAction.php');
require_once(DOKU_PLUGIN . 'wikiiocmodel/projects/defaultProject/actions/RawPageAction.php');
require_once(DOKU_PLUGIN . 'wikiiocmodel/projects/defaultProject/actions/RawPartialPageAction.php');
require_once(DOKU_PLUGIN . 'wikiiocmodel/projects/defaultProject/actions/HtmlPageAction.php');
require_once(DOKU_PLUGIN . 'wikiiocmodel/projects/defaultProject/actions/HtmlRevisionPageAction.php');
require_once(DOKU_PLUGIN . 'wikiiocmodel/projects/defaultProject/actions/SavePageAction.php');
require_once(DOKU_PLUGIN . 'wikiiocmodel/projects/defaultProject/actions/SavePartialPageAction.php');
require_once(DOKU_PLUGIN . 'wikiiocmodel/projects/defaultProject/actions/CreatePageAction.php');
require_once(DOKU_PLUGIN . 'wikiiocmodel/projects/defaultProject/actions/CancelEditPageAction.php');
require_once(DOKU_PLUGIN . 'wikiiocmodel/projects/defaultProject/actions/CancelPartialEditPageAction.php');
require_once(DOKU_PLUGIN . 'wikiiocmodel/projects/defaultProject/actions/UploadMediaAction.php');
require_once(DOKU_PLUGIN . 'wikiiocmodel/projects/defaultProject/actions/DraftPageAction.php');
require_once(DOKU_PLUGIN . 'wikiiocmodel/actions/NotifyAction.php');


require_once(DOKU_PLUGIN . 'wikiiocmodel/persistence/BasicPersistenceEngine.php');
require_once(DOKU_PLUGIN . 'wikiiocmodel/persistence/WikiPageSystemManager.php');
require_once(DOKU_PLUGIN . 'ajaxcommand/requestparams/PageKeys.php');

if (!defined('DW_DEFAULT_PAGE')) {
    define('DW_DEFAULT_PAGE', "start");
}
if (!defined('DW_ACT_SHOW')) {
    define('DW_ACT_SHOW', "show");
}
if (!defined('DW_ACT_DRAFTDEL')) {
    define('DW_ACT_DRAFTDEL', "draftdel");
}
if (!defined('DW_ACT_SAVE')) {
    define('DW_ACT_SAVE', "save");
}
if (!defined('DW_ACT_EDIT')) {
    define('DW_ACT_EDIT', "edit");
}
if (!defined('DW_ACT_PREVIEW')) {
    define('DW_ACT_PREVIEW', "preview");
}
if (!defined('DW_ACT_RECOVER')) {
    define('DW_ACT_RECOVER', "recover");
}
if (!defined('DW_ACT_DENIED')) {
    define('DW_ACT_DENIED', "denied");
}
if (!defined('DW_ACT_MEDIA_DETAIL')) {
    define('DW_ACT_MEDIA_DETAIL', "media_detail");
}
if (!defined('DW_ACT_MEDIA_MANAGER')) {
    define('DW_ACT_MEDIA_MANAGER', "media");
}
if (!defined('DW_ACT_EXPORT_ADMIN')) {
    define('DW_ACT_EXPORT_ADMIN', "admin");
}
if (!defined('DW_ACT_MEDIA_DETAILS')) {
    define('DW_ACT_MEDIA_DETAILS', "mediadetails");
}

//    const DW_ACT_BACKLINK="backlink";
//    const DW_ACT_REVISIONS="revisions";
//    const DW_ACT_DIFF="diff";
//    const DW_ACT_SUBSCRIBE="subscribe";
//    const DW_ACT_UNSUBSCRIBE="unsubscribe";
//    const DW_ACT_SUBSCRIBENS="subscribens";
//    const DW_ACT_UNSUBSCRIBENS="unsubscribens";
//    const DW_ACT_INDEX="index";
//    const DW_ACT_RECENT="recent";
//    const DW_ACT_SEARCH="search";
//    const DW_ACT_EXPORT_RAW="export_raw";
//    const DW_ACT_EXPORT_XHTML="export_xhtml";
//    const DW_ACT_EXPORT_XHTMLBODY="export_xhtmlbody";
//    const DW_ACT_CHECK="check";
//    const DW_ACT_INDEX="register";
//    const DW_ACT_LOGIN="login";
//    const DW_ACT_LOGOUT="logout";
//    const DW_ACT_EXPORT_PROFILE="profile";
//    const DW_ACT_EXPORT_RESENDPWD="resendpwd";
//    const DW_ACT_DRAFT="draft";
//    const DW_ACT_WORDBLOCK="wordblock";
//    const DW_ACT_CONFLICT="conflict";
//    const DW_ACT_CANCEL="cancel";

/**
 * Class DokuModelAdapter
 * Adaptador per passar les nostres comandes a la Dokuwiki.
 */
class DokuModelAdapter extends AbstractModelAdapter
{
    const ADMIN_PERMISSION = "admin";

    /**
     * @var BasicPersistenceEngine
     */
    private $persistenceEngine;

    protected $params;
    protected $dataTmp;
    protected $ppEvt;

    public function init($persistenceEngine)
    {
        $this->persistenceEngine = $persistenceEngine;
        return $this;
    }

    /**
     * Crida principal de la comanda admin_task.
     * @param type $params de la comanda
     * @return Array
     */
    public function getAdminTask($params)
    {
        $action = new AdminTaskAction();
        return $action->get($params);
    }

    /**
     * Crida principal de la comanda admin_tab i crida del LoginResponseHandler
     * @return type
     */
    public function getAdminTaskList()
    {
        $action = new AdminTaskListAction();
        return $action->get();
    }

    public function setParams($element, $value)
    {
        $this->params[$element] = $value;
    }

    // ës la crida principal de la comanda new_page
    public function createPage($pars)
    {
        $action = new CreatePageAction($this->persistenceEngine);
        return $action->get($pars);
    }


    /**
     * Crida principal de la comanda page
     *
     * @param string $pid - id del document
     * @param string $prev - revisió del document
     * @return array - array amb la informació de la resposta
     * @throws InsufficientPermissionToViewPageException
     * @throws PageNotFoundException
     */
    public function getHtmlPage($pars)
    {


        if (!$pars[PageKeys::KEY_REV]) {
//            return $this->getPartialPage($pid, $prev, null, null, null);
            $action = new HtmlPageAction($this->persistenceEngine);
            $response = $action->get($pars);
        }else{
            $action = new HtmlRevisionPageAction($this->persistenceEngine);
            $response = $action->get($pars);
        }
        
        return $response;

//        $this->startPageProcess(DW_ACT_SHOW, $pid, $prev, null, null);
//
//        if (!WikiIocInfoManager::getInfo("exists")) {
//            throw new PageNotFoundException($id, $lang['pageNotFound']);
//        }
//        if (!WikiIocInfoManager::getInfo("perm")) {
//            throw new InsufficientPermissionToViewPageException($id); //TODO [Josep] Internacionalització missatge per defecte!
//        }
//
//        $this->doFormatedPartialPagePreProcess();    //[ALERTA Josep] Pot venir amb un fragment de HTML i caldria veure què es fa amb ell.
//
//        $response['structure'] = $this->getStructuredDocument(null, $pid, $prev);
//
//        $revisionInfo = p_locale_xhtml('showrev'); //[ALERTA Josep] Cal crear una classe WikiMessageManager (similar a WikiInfoManager) que es pugui fer servir en comptes de la variable global $lang
//
//        $response['structure']['html'] = str_replace($revisionInfo, '', $response['structure']['html']);
//
//
//        // Si no s'ha especificat cap altre missatge mostrem el de carrega
//        if (!$response['info']) {
//            $response['info'] = $this->generateInfo("warning", strip_tags($revisionInfo));
//        } else {
//            $this->addInfoToInfo($response['warning'], $this->generateInfo("info", strip_tags($revisionInfo)));
//        }
//
//
//        // TODO: afegir el 'meta' que correspongui
//        $response['meta'] = $this->getMetaResponse($pid);
//
//        // TODO: afegir les revisions
//        $response['revs'] = $this->getRevisions($pid);
//
//
//        return $response;
    }
    
    

    /**
     * Crida principal de la comanda edit i de la comanda raw_code
     * @global type $lang
     * @param type $pid
     * @param type $prev
     * @param type $prange
     * @param type $psum
     * @param type $recover
     * @return type
     * @throws PageNotFoundException
     * @throws InsufficientPermissionToEditPageException
     */
    public function editPage($params){ // Alerta[Xavi] Canviar per getEdit per fer-lo consistent amb getEditPartial?
        if($params["refresh"]){
            return $this->refreshEdition($params);            
        }else{
            return $this->getCodePage($params);
        }
    }

    /**
     * @param type $params
     * @return type
     * @throws PageNotFoundException
     * @throws InsufficientPermissionToEditPageException
     */
    public function refreshEdition($params)
    {
        $action = new RefreshEditionAction($this->persistenceEngine);
        $contentData = $action->get($params);
        return $contentData;
    }
    /**
     * @param type $params
     * @return type
     * @throws PageNotFoundException
     * @throws InsufficientPermissionToEditPageException
     */
    public function getCodePage($params) // Alerta[Xavi] Canviar per getEdit per fer-lo consistent amb getEditPartial?
    {
        $action = new RawPageAction($this->persistenceEngine);
        $contentData = $action->get($params);
        return $contentData;
    }

    /**
     * Crida principal de la comanda cancel
     * @param type $pid
     * @param type $prev
     * @param bool|type $keep_draft
     * @param bool $discard_changes si es cert es descartaran els canvis sense preguntar
     * @return type
     * @global type $lang
     */
    //[ALERTA Josep] Es queda aquí.
    public function cancelEdition($pars){
        $action = new CancelEditPageAction($this->persistenceEngine);
        return $action->get($pars);
//        global $lang;
//
//        $this->startPageProcess(DW_ACT_DRAFTDEL, $pid, $prev);
//        $this->doCancelEditPreprocess($pid, $keep_draft);    //[ALERTA Josep] Pot venir amb un fragment de HTML i caldria veure què es fa amb ell.
//
//        $response = $this->getFormatedPageResponse();
//
//        $response ['info'] = $this->generateInfo("warning", $lang['edition_cancelled']);
//
//        $response['structure'] = $this->getStructuredDocument(null, $pid, $prev);
//        $response['meta'] = $this->getMetaResponse($pid);
//        $response['revs'] = $this->getRevisions($pid);
//
//        return $response;
    }

    /**
     * Crida principal de la comanda save
     * @param $params
     * @return array|void
     */
    //[ALERTA Josep] Es queda aquí.
//	public function saveEdition(
//		$pid, $prev = NULL, $prange = NULL,
//		$pdate = NULL, $ppre = NULL, $ptext = NULL, $psuf = NULL, $psum = NULL
//	) {

    public function saveEdition($params)
    {
        $action = new SavePageAction($this->persistenceEngine);
        // Remove partialDraft
//        $this->clearPartialDraft($params['id']); // TODO[Xavi] Aquí o al SavePageAction? importa si es abans del $response?

        $ret = $action->get($params);
        return $ret;

//	$this->startPageProcess(
//		DW_ACT_SAVE, $pid, $prev, $prange, $psum, $pdate, $ppre, $ptext, $psuf
//	);
//	$code = $this->doSavePreProcess();    //[ALERTA Josep] Pot venir amb un fragment de HTML i caldria veure què es fa amb ell.
//      $response = $this->getSaveInfoResponse($code);
//      return $response;
    }

//    public function getMediaFileName($id, $rev = '')
//    {
//        $dataQuery = $this->persistenceEngine->createMediaDataQuery();
//        return $dataQuery->getFileName($id, array("rev" => $rev));
//    }

//    /**
//     * Obté l'identificador de la pàgina sense el seu espai de noms
//     * @param type $id
//     * @return type
//     */
//    public function getIdWithoutNs($id)
//    {
//        $dataQuery = $this->persistenceEngine->createPageDataQuery();
//        return $dataQuery->getIdWithoutNs($id);
//    }

//    /**
//     * Obté la llista de medias que estan en una espai de noms
//     * @param type $ns
//     * @return array
//     */
//    public function getMediaList($ns)
//    {
//        $dataQuery = $this->persistenceEngine->createMediaDataQuery();
//        return $dataQuery->getFileList($ns);
//    }

//    /**
//     *
//     * @param type $id
//     * @param type $rev
//     * @return type
//     */
//    public function getPageFileName($id, $rev = '')
//    {
//        $dataQuery = $this->persistenceEngine->createPageDataQuery();
//        return $dataQuery->getFileName($id, array("rev" => $rev));
//    }

//    /**
//     * Obté un link al media identificat per $image, $rev
//     * @param string $image //abans era $id. $id no s'utilitzava
//     * @param bool $rev
//     * @param bool $meta
//     *
//     * @return string
//     */
//    //[ALERTA Josep] Es deixa aquí la funció tot i que el codi es trasllada 
//    //a WikiDataSystemUtility
//    public function getMediaUrl($image, $rev = FALSE, $meta = FALSE)
//    {
//        $size = media_image_preview_size($image, $rev, $meta);
//        if ($size) {
//            $more = array();
//            if ($rev) {
//                $more['rev'] = $rev;
//            } else {
//                $t = @filemtime(mediaFN($image));
//                $more['t'] = $t;
//            }
//            $more['w'] = $size[0];
//            $more['h'] = $size[1];
//            $src = ml($image, $more);
//        } else {
//            $src = ml($image, "", TRUE);
//        }
//
//        return $src;
//    }
//
    /**
     * És la crida pincipal de la comanda save_unlinked_image.
     * Guarda un fitxer de tipus media pujat des del client
     * @param string $nsTarget
     * @param string $idTarget
     * @param string $filePathSource
     * @param bool $overWrite
     *
     * @return int
     */
    public function uploadImage($nsTarget, $idTarget, $filePathSource, $overWrite = FALSE)
    {
        /* UploadMediaAction*/
        $action  = new UploadMediaAction($this->persistenceEngine);
        return $action->get(array('nsTarget' => $nsTarget, 'mediaName' => $idTarget, 'filePathSource' => $filePathSource, 'overWrite' => $overWrite));
  }

  /**
 * És la crida principal de la comanda copy_image_to_project
 * @param string $nsTarget
 * @param string $idTarget
 * @param string $filePathSource
 * @param bool $overWrite
 *
 * @return int
     */
    public function saveImage($nsTarget, $idTarget, $filePathSource, $overWrite = FALSE)
    {
        /* MediaDataQuery*/
        $dataQuery = $this->persistenceEngine->createMediaDataQuery();
        return $dataQuery->copyImage($nsTarget, $idTarget, $filePathSource, $overWrite);
  }

  /**
 * És la crida principal de la comanda get_image_detail. Obté un html
 * amb el detall d'una imatge.
 * @global type $lang
 * @param type $imageId
 * @param type $fromPage
 * @return string
 * @throws HttpErrorCodeException
     */
    //[TODO Josep] Cal normalitzar
    public function getImageDetail($imageId, $fromPage = NULL)
    {
        global $lang;

        //[TODO Josep] Normalitzar: start do get ...

        $error = $this->startMediaProcess(DW_ACT_MEDIA_DETAIL, $imageId, $fromPage);
        if ($error == 401) {
            throw new HttpErrorCodeException($error, "Access denied");
        } else if ($error == 404) {
            throw new HttpErrorCodeException($error, "Resource " . $imageId . " not found.");
        }
        $title = $lang['img_detail_title'] . $imageId;
        $ret = array(
            "content" => $this->_getImageDetail(),
            "imageTitle" => $title,
            "imageId" => $imageId,
            "fromId" => $fromPage,
            "modifyImageLabel" => $lang['img_manager'],
            "closeDialogLabel" => $lang['img_backto']
        );

        return $ret;
    }

    /**
     * És la crida principal de la comanda ns_tree_rest
     * @global type $conf
     * @param type $currentnode
     * @param type $sortBy
     * @param type $onlyDirs
     * @return type
     */
    public function getNsTree($currentnode, $sortBy, $onlyDirs = FALSE)
    {
        $dataQuery = $this->persistenceEngine->createPageDataQuery();
        return $dataQuery->getNsTree($currentnode, $sortBy, $onlyDirs);
    }

    /**
     * Obté el missatge traduit a l'idioma actual.
     * @global type $lang
     * @param type $id
     * @return type
     */
    public function getGlobalMessage($id)
    {
        return WikiIocLangManager::getLang($id);
    }

//    /**
//     * Crea el directori on ubicar el fitxer referenciat per $filePath després
//     * d'extreure'n el nom del fitxer. Aquesta funció no crea directoris recursivamnent.
//     *
//     * @param type $filePath
//     */
//    public function makeFileDir($filePath)
//    {
//        io_makeFileDir($filePath);
//    }
//
//    public function tplIncDir()
//    {
//        global $conf;
//        if (is_callable('tpl_incdir')) {
//            $ret = tpl_incdir();
//        } else {
//            $ret = DOKU_INC . 'lib/tpl/' . $conf['template'] . '/';
//        }
//
//        return $ret;
//    }

    // configuration methods
//    /**
//     * tpl_getConf($id)
//     *
//     * use this function to access template configuration variables
//     */
//    public function tplConf($id)
//    {
//        return tpl_getConf($id);
//    }

//    //[Alerta Josep] Es trasllada a PermissionManager
//    private function setUserPagePermission($page, $user, $acl_level)
//    {
//        global $conf;
//        include_once(DOKU_PLUGIN . 'wikiiocmodel/conf/default.php');
//        $pageuser = ":" . substr($page, 0, strrpos($page, ":"));
//        $userpage = substr($pageuser, strrpos($pageuser, ":") + 1);
//        $ret = FALSE;
//        if (WikiIocInfoManager::getInfo('isadmin')
//            || WikiIocInfoManager::getInfo('ismanager')
//            || (WikiIocInfoManager::getInfo('namespace')
//                == substr($page, 0, strrpos($page, ":"))
//                && $userpage == $user
//                && $conf['userpage_allowed'] === 1
//                && ($pageuser == $conf['userpage_ns'] . $user ||
//                    $pageuser == $conf['userpage_discuss_ns'] . $user)
//            )
//        ) {
//            $ret = $this->establir_permis($page, $user, $acl_level, TRUE);
//            WikiIocInfoManager::setInfo('perm', $ret);
//        }
//        return $ret;
//    }
//
//    /**
//     * administració de permisos
//     *
//     * @param $page y $user son obligatorios
//     */
//    //[Alerta Josep] Es trasllada a PermissionManager
//    private function obtenir_permis($page, $user)
//    {
////		$acl_class = new admin_plugin_acl();
////		$acl_class->handle();
////		$acl_class->who = $user;
//        $permis = auth_quickaclcheck($page);
//
//        /* este bucle obtiene el mismo resultado que auth_quickaclcheck()
//		$permis = NULL;
//		$sub_page = $page;
//		while (!$permis && $sub_page) {
//			$acl_class->ns = $sub_page;
//			$permis = $acl_class->_get_exact_perm();
//			$sub_page = substr($sub_page,0,strrpos($sub_page,':'));
//		}
//		*/
//
//        return $permis;
//    }
//
//    /**
//     * @param bool $force : true : indica que s'ha d'establir el permís estricte
//     *                      false: si existeix algún permís, no es modifica
//     */
//    //[Alerta Josep] Es trasllada a PermissionManager
//    private function establir_permis($page, $user, $acl_level, $force = FALSE)
//    {
//        $acl_class = new admin_plugin_acl();
//        $acl_class->handle();
//        $acl_class->who = $user;
//        $permis_actual = auth_quickaclcheck($page);
//
//        if ($force || $acl_level > $permis_actual) {
//            $ret = $acl_class->_acl_add($page, $user, $acl_level);
//            if ($ret) {
//                if (strpos($page, '*') === FALSE) {
//                    if ($acl_level > AUTH_EDIT) {
//                        $permis_actual = AUTH_EDIT;
//                    }
//                } else {
//                    $permis_actual = $acl_level;
//                }
//            }
//        }
//
//        return $permis_actual;
//    }
//
//    //[Alerta Josep] Es trasllada a PermissionManager
//    private function eliminar_permis($page, $user)
//    {
//        $acl_class = new admin_plugin_acl();
//        //$acl_class->handle();
//        //$acl_class->who = $user;
//        if ($page && $user) {
//            $ret = $acl_class->_acl_del($page, $user);
//        }
//
//        return $ret;
//    }

//    /**
//     * Retorna si s'ha trobat la cadena que es cerca al principi de la cadena on es busca.
//     *
//     * @param string $haystack cadena on buscar
//     * @param string $needle cadena per buscar
//     *
//     * @return bool true si la cadena comença com la cadena passada per argument o la cadena a buscar es buida, i false
//     * en cas contrari
//     */
//    private function starsWith($haystack, $needle)
//    {
//        return $needle === "" || strpos($haystack, $needle) === 0;
//    }

//    /**
//     * Retorna si s'ha trobat la cadena que es cerca al final de la cadena on es busca.
//     *
//     * @param string $haystack cadena on buscar
//     * @param string $needle cadena per buscar
//     *
//     * @return bool true si la cadena acaba com la cadena passada per argument o la cadena a buscar es buida, i false
//     * en cas contrari
//     */
//    private function endsWith($haystack, $needle)
//    {
//        return $needle === "" || substr($haystack, -strlen($needle)) === $needle;
//    }

//
//	/**
//	 * Inicia tractament per obtenir la llista de gestions d'administració
//	 */
//	private function startAdminTaskProcess( $ptask = NULL, $pid = NULL ) {
//		global $ACT;
//		global $_REQUEST;
//		global $ID;
//		global $conf;
//
//		// Agafem l'index de la configuració
//		if ( ! isset( $pid ) ) {
//			$pid = $conf['start'];
//		}
//
//		$ID = $this->params['id'] = $pid;
//		$ACT = $this->params['do'] = DW_ACT_EXPORT_ADMIN;
//
//		WikiIocInfoManager::loadInfo();
//		$this->startUpLang();
//		if ( $ptask ) {
//			if ( ! $_REQUEST['page'] || $_REQUEST['page'] != $ptask ) {
//				$_REQUEST['page'] = $ptask;
//			}
//			$this->params['task'] = $ptask;
//		}
//
//		$this->triggerStartEvents();
//	}

//    /**
//     * Inicia tractament d'una pàgina de la dokuwiki
//     */
//    private function startCreateProcess(
//        $pdo, $pid = NULL, $prev = NULL, $prange = NULL,
//        $psum = NULL, $pdate = NULL, $ppre = NULL, $ptext = NULL,
//        $psuf = NULL
//    )
//    {
//        global $TEXT, $lang;
//        $this->startPageProcess($pdo, $pid, $prev, $prange, $psum, $pdate, $ppre, $ptext, $psuf);
//        if (!$ptext) {
//            $TEXT = $this->params['text'] = cleanText($lang['createDefaultText']);
//        }
//    }

//    /**
//     * Inicia tractament d'una pàgina de la dokuwiki
//     */
//    private function startPageProcess( // TODO[Xavi] No es pot eliminar perquè es cridat pel CreateProcess
//        $pdo, $pid = NULL, $prev = NULL, $prange = NULL,
//        $psum = NULL, $pdate = NULL, $ppre = NULL, $ptext = NULL, $psuf = NULL
//    )
//    {
//        global $ID;
//        global $ACT;
//        global $REV;
//        global $RANGE;
//        global $DATE;
//        global $PRE;
//        global $TEXT;
//        global $SUF;
//        global $SUM;
//
//        $ACT = $this->params['do'] = $pdo;
//        $ACT = act_clean($ACT);
//
//        if (!$pid) {
//            $pid = DW_DEFAULT_PAGE;
//        }
//        $ID = $this->params['id'] = $pid;
//        if ($prev) {
//            $REV = $this->params['rev'] = $prev;
//        }
//        if ($prange) {
//            $RANGE = $this->params['range'] = $prange;
//        }
//        if ($pdate) {
//            $DATE = $this->params['date'] = $pdate;
//        }
//        if ($ppre) {
//            $PRE = $this->params['pre'] = cleanText(substr($ppre, 0, -1));
//        }
//        if ($ptext) {
//            $TEXT = $this->params['text'] = cleanText($ptext);
//        }
//        if ($psuf) {
//            $SUF = $this->params['suf'] = cleanText($psuf);
//        }
//        if ($psum) {
//            $SUM = $this->params['sum'] = $psum;
//        }
//
//        WikiIocInfoManager::loadInfo();
//        $this->startUpLang();
//        $this->triggerStartEvents();
//    }

    /**
     * Inicia tractament d'una pàgina de la dokuwiki
     */
    private function startMediaProcess($pdo, $pImageId = NULL, $pFromId = NULL)
    {
        global $ID;
        global $AUTH;
        global $vector_action;
        global $loginname;
        global $IMG;
        global $ERROR;
        global $SRC;
        global $conf;
        global $lang;

        $ret = $ERROR = 0;

        $this->params['action'] = $pdo;
        if ($pdo === DW_ACT_MEDIA_DETAIL) {
            $vector_action = $GET["vecdo"] = $this->params['vector_action'] = "detail";
        }

        if ($pImageId) {
            $IMG = $this->params['imageId'] = $pImageId;
        }
        if ($pFromId) {
            $ID = $this->params['id'] = $pFromId;
        }
        // check image permissions
        $AUTH = auth_quickaclcheck($pImageId);
        if ($AUTH >= AUTH_READ) {
            // check if image exists
            $SRC = mediaFN($pImageId);
            if (!file_exists($SRC)) {
                $ret = $ERROR = 404;
            }
        } else {
            // no auth
            $ret = $ERROR = 401;
        }

        if ($ret != 0) {
            return $ret;
        }

        WikiIocInfoManager::loadMediaInfo();

        /**
         * Stores the template wide context
         *
         * This template offers discussion pages via common articles, which should be
         * marked as "special". DokuWiki does not know any "special" articles, therefore
         * we have to take care about detecting if the current page is a discussion
         * page or not.
         *
         * @var string
         * @author Andreas Haerter <development@andreas-haerter.com>
         */
        /*$vector_context = $this->params['vector_context'] = "article";
        if ( $pFromId && preg_match(
                "/^" . tpl_getConf( "ioc_template_discuss_ns" ) . "?$|^"
                . tpl_getConf( "ioc_template_discuss_ns" ) . ".*?$/i",
                                ":" . getNS( $pFromId )
            )
        ) {
            $vector_context = $this->params['vector_context'] = "discuss";
        }*/

        /**
         * Stores the name the current client used to login
         *
         * @var string
         * @author Andreas Haerter <development@andreas-haerter.com>
         */
        $loginname = $this->params['loginName'] = "";
        if (!empty($conf["useacl"])) {
            if (isset($_SERVER["REMOTE_USER"]) && //no empty() but isset(): "0" may be a valid username...
                $_SERVER["REMOTE_USER"] !== ""
            ) {
                $loginname = $this->params['loginName'] = $_SERVER["REMOTE_USER"]; //$INFO["client"] would not work here (-> e.g. if
                //current IP differs from the one used to login)
            }
        }

        $this->startUpLang();

        //detect revision
        $rev = $this->params['rev'] = (int)WikiIocInfoManager::getInfo("rev"); //$INFO comes from the DokuWiki core
        if ($rev < 1) {
            $rev = $this->params['rev'] = (int)WikiIocInfoManager::getInfo("lastmod");
        }

        $this->triggerStartEvents();

        return $ret;
    }

    private function triggerStartEvents()
    {
//      $tmp = array(); //NO DATA
//	trigger_event( 'DOKUWIKI_STARTED', $tmp );
        trigger_event('WIOC_AJAX_COMMAND_STARTED', $this->dataTmp);
    }

    private function triggerEndEvents()
    {
        $tmp = array(); //NO DATA
//		trigger_event( 'DOKUWIKI_DONE', $tmp );
        trigger_event('WIOC_AJAX_COMMAND_DONE', $tmp);
    }

    private function startUpLang()
    {
        global $conf;
        global $lang;

        if ($this->langStartedUp) {
            return;
        }

        //get needed language array
        include WikiGlobalConfig::tplIncDir() . "lang/en/lang.php";
        //overwrite English language values with available translations
        if (!empty($conf["lang"]) &&
            $conf["lang"] !== "en" &&
            file_exists(WikiGlobalConfig::tplIncDir() . "/lang/" . $conf["lang"] . "/lang.php")
        ) {
            //get language file (partially translated language files are no problem
            //cause non translated stuff is still existing as English array value)
            include WikiGlobalConfig::tplIncDir() . "/lang/" . $conf["lang"] . "/lang.php";
        }
        if (!empty($conf["lang"]) &&
            $conf["lang"] !== "en" &&
            file_exists(DOKU_PLUGIN . "wikiiocmodel/lang/" . $conf["lang"] . "/lang.php")
        ) {
            include DOKU_PLUGIN . "wikiiocmodel/lang/" . $conf["lang"] . "/lang.php";
        }
        $this->langStartedUp = true;
    }

//    /**
//     * Realitza el per-procés d'una pàgina de la dokuwiki en format HTML.
//     * Permet afegir etiquetes HTML al contingut final durant la fase de
//     * preprocés
//     *
//     * @return string
//     */
//    private function doFormatedPartialPagePreProcess()
//    {
//        $content = "";
//        if ($this->runBeforePreprocess($content)) {
//            //unlock( $this->params['id'] ); //try to unlock
//        }
//        $this->runAfterPreprocess($content);
//
//        return $content;
//    }


//    /**
//     * Realitza el per-procés d'una pàgina de la dokuwiki en format HTML.
//     * Permet afegir etiquetes HTML al contingut final durant la fase de
//     * preprocés
//     *
//     * @return string
//     */
//    private function doFormatedPagePreProcess()
//    {
//        $content = "";
//        if ($this->runBeforePreprocess($content)) {
//            unlock($this->params['id']); //try to unlock
//        }
//        $this->runAfterPreprocess($content);
//
//        return $content;
//    }

    /**
     * Realitza el per-procés per recuperar el detall d'una imatge de la dokuwiki.
     * Permet afegir etiquetes HTML al contingut final durant la fase de
     * preprocés
     *
     * @return string
     */
    private function _getImageDetail()
    {
        global $ID;
        global $AUTH;
        global $vector_action;
        global $vector_context;
        global $loginname;
        global $IMG;
        global $ERROR;
        global $SRC;
        global $conf;
        global $lang;

        ob_start();
        include WikiGlobalConfig::tplIncDir() . "inc_detail.php";
        $content = ob_get_clean();
//        $content = preg_replace(
//            '/(<!-- TOC START -->\s?)(.*\s?)(<div class=.*tocheader.*<\/div>|<h3 class=.*toggle.*<\/h3>)((.*\s)*)(<!-- TOC END -->)/i',
//            '$1<div class="dokuwiki">$2$4</div>$6', $toc
//        );

        return $content;
    }

//    // TODO[Xavi] Reactivada perquè es continua cridant (Al fer un save es crida aquesta)
//    private function doEditPagePreProcess()
//    {
//        global $ACT;
//
//        $content = "";
//        if ($this->runBeforePreprocess($content)) {
//            $ACT = act_edit($ACT);
//            $ACT = act_permcheck($ACT);
//        }
//        $this->runAfterPreprocess($content);
//
//        return $content;
//    }

//	private function doAdminTaskPreProcess() {
//		global $ACT;
//		global $conf;
//		global $ID;
//
//		$content = "";
//		if ( $this->runBeforePreprocess( $content ) ) {
//			$ACT = act_permcheck( $ACT );
//			//handle admin tasks
//			// retrieve admin plugin name from $_REQUEST['page']
//			if ( ! empty( $_REQUEST['page'] ) ) {
//				$pluginlist = plugin_list( 'admin' );
//				if ( in_array( $_REQUEST['page'], $pluginlist ) ) {
//					// attempt to load the plugin
//					if ( $plugin =& plugin_load( 'admin', $_REQUEST['page'] ) !== NULL ) {
//						if ( $plugin->forAdminOnly() 
//                                                            && !WikiIocInfoManager::getInfo('isadmin') ) {
//							// a manager tried to load a plugin that's for admins only
//							unset( $_REQUEST['page'] );
//							msg( 'For admins only', - 1 );
//						} else {
//							if ( is_callable( array( $plugin, "preventRefresh" ) ) ) {
//								$allowedRefresh = $plugin->preventRefresh();
//							}
//							$plugin->handle();
//							$this->dataTmp["needRefresh"] = is_callable( array( $plugin, "isRefreshNeeded" ) );
//							if ( $this->dataTmp["needRefresh"] ) {
//								$this->dataTmp["needRefresh"] = $plugin->isRefreshNeeded();
//							}
//							$this->dataTmp["title"] = $plugin->getMenuText( $conf['lang'] );
//							if ( isset( $allowedRefresh )
//							     && is_callable( array( $plugin, "setAllowedRefresh" ) )
//							) {
//								$plugin->setAllowedRefresh( $allowedRefresh );
//							}
//						}
//					}
//				}
//			}
//			// check permissions again - the action may have changed
//			$ACT = act_permcheck( $ACT );
//		}
//		$this->runAfterPreprocess( $content );
//
//		return $content;
//	}

//	private function doAdminTaskListPreProcess() {
//		global $ACT;
//
//		$content = "";
//		if ( $this->runBeforePreprocess( $content ) ) {
//			$ACT = act_permcheck( $ACT );
//		}
//		$this->runAfterPreprocess( $content );
//
//		return $content;
//	}

//    private function doCreatePreProcess()
//    {
//        if (WikiIocInfoManager::getInfo("exists")) {
//            throw new PageAlreadyExistsException($pid, $lang['pageExists']);
//        }
//
//        $permis_actual = $this->obtenir_permis($pid, $_SERVER['REMOTE_USER']);
//        if ($permis_actual < AUTH_CREATE) {
//            //se pide el permiso para el directorio (no para la página)
//            $permis_actual = $this->setUserPagePermission(getNS($pid) . ':*'
//                , WikiIocInfoManager::getInfo('client'), AUTH_DELETE);
//        }
//        if ($permis_actual >= AUTH_CREATE) {
//            $code = $this->doSavePreProcess();    //[ALERTA Josep] Pot venir amb un fragment de HTML i caldria veure què es fa amb ell.
//        } else {
//            throw new InsufficientPermissionToCreatePageException($pid); //TODO [Josep] cal internacionalitzar el missage per defecte
//        }
//    }

//    private function doSavePreProcess()
//    {
//        global $ACT;
//
//        $code = 0;
//        $ACT = act_permcheck($ACT);
//
//        if ($ACT == $this->params['do']) {
//            $ret = act_save($ACT);
//        } else {
//            $ret = $ACT;
//        }
//        if ($ret === 'edit') {
//            $code = 1004;
//        } else if ($ret === 'conflict') {
//            $code = 1003;
//        } else if ($ret === 'denied') {
//            $code = 1005;
//        }
//        if ($code == 0) {
//            $ACT = $this->params['do'] = DW_ACT_EDIT;
//            $this->doEditPagePreProcess();    //[ALERTA Josep] Pot venir amb un fragment de HTML i caldria veure què es fa amb ell.
//        } else {
//            //S'han trobat conflictes i no s'ha pogut guardar
//            //TODO[Josep] de moment tornem a la versió original, però cal
//            //TODO[Xavi] Això ja no es necessari perque no ha de passar mai, el frontend et tanca automàticament la edició
//            // Necessitem access:
//            //      al draft (o contingut document que s'ha volgut guardar!)
//            //      el document guardat
//
//            //cercar una solució més operativa com ara emmagatzemar un esborrany
//            //per tal que l'usuari pugui comparar i acceptar canvis
//            $ACT = $this->params['do'] = DW_ACT_SHOW;
//            $this->doFormatedPagePreProcess();    //[ALERTA Josep] Pot venir amb un fragment de HTML i caldria veure què es fa amb ell.
//        }
//
//        return $code;
//    }

//    private function doCancelEditPreProcess($id, $keep_draft = FALSE)
//    {
//        // Si es passa keep_draft = true no s'esborra
//        if (!$keep_draft) {
//            $this->clearFullDraft($id);
//            $this->clearPartialDraft($id);
//        }
//
//        $this->doFormatedPagePreProcess();    //[ALERTA Josep] Pot venir amb un fragment de HTML i caldria veure què es fa amb ell.
//    }
//
//    private function getFormatedPageResponse()
//    {
//        global $lang;
//        $pageToSend = $this->getFormatedPage();
//
//        $response = $this->getContentPage($pageToSend);
//
//        return $response;
//    }
//
//    private function getAdminTaskResponse()
//    {
//        if (!$this->dataTmp["needRefresh"]) {
//            $pageToSend = $this->getAdminTaskHtml();
//            $id = "admin_" . $this->params["task"];
//            $ret = $this->getAdminTaskPage($id, $this->params["task"], $pageToSend);
//        }
//        $ret["needRefresh"] = $this->dataTmp["needRefresh"];
//
//        return $ret;
//    }
//
//    private function getAdminTaskHtml()
//    {
//        global $conf;
//
//        ob_start();
//        trigger_event('TPL_ACT_RENDER', $ACT, "tpl_admin");
//        $html_output = ob_get_clean();
//        ob_start();
//        trigger_event('TPL_CONTENT_DISPLAY', $html_output, 'ptln');
//        $html_output = ob_get_clean();
//
//        return $html_output;
//    }

//	public function getAdminTaskListResponse() {
//		$pageToSend  = $this->getAdminTaskListHtml();
//		$containerId = cfgIdConstants::ZONA_NAVEGACIO;
//
//		return $this->getAdminTaskListPage( $containerId, $pageToSend );
//	}
//
//	private function getAdminTaskListHtml() {
//		global $conf;
//
//		ob_start();
//		trigger_event( 'TPL_ACT_RENDER', $ACT );
//
//		// build menu of admin functions from the plugins that handle them
//		$pluginlist = plugin_list( 'admin' );
//		$menu       = array();
//		foreach ( $pluginlist as $p ) {
//			if ( $obj =& plugin_load( 'admin', $p ) === NULL ) {
//				continue;
//			}
//
//			// check permissions
//			if ( $obj->forAdminOnly() && !WikiIocInfoManager::getInfo('isadmin')) {
//				continue;
//			}
//
//			$menu[ $p ] = array(
//				'plugin' => $p,
//				'prompt' => $obj->getMenuText( $conf['lang'] ),
//				'sort'   => $obj->getMenuSort()
//			);
//		}
//
//		// Admin Tasks
//		if ( count( $menu ) ) {
//			usort( $menu, 'p_sort_modes' );
//			// output the menu
//			ptln( '<div class="clearer"></div>' );
//			print p_locale_xhtml( 'adminplugins' );
//			ptln( '<ul>' );
//			foreach ( $menu as $item ) {
//				if ( ! $item['prompt'] ) {
//					continue;
//				}
//				ptln( '  <li><div class="li"><a href="' . DOKU_BASE . DOKU_SCRIPT . '?'
//				      . 'do=admin&amp;page=' . $item['plugin'] . '">' . $item['prompt']
//				      . '</a></div></li>' );
//			}
//			ptln( '</ul>' );
//		}
//
//                $html_output = ob_get_clean();
//                ob_start();
//		trigger_event('TPL_CONTENT_DISPLAY', $html_output, 'ptln');
//                $html_output = ob_get_clean();
//
//
//		return $html_output;
//	}

//	private function getCodePageResponse() {
//		global $lang;
//
//		$pageToSend = $this->cleanResponse( $this->_getCodePage() );
//
//		$resp         = $this->getContentPage( $pageToSend["content"] );
//		$resp['meta'] = $pageToSend['meta'];
//
//		$infoType = 'info';
//
//		if ( WikiIocInfoManager::getInfo('locked')) {
//			$infoType           = 'error';
//			$pageToSend['info'] = $lang['lockedby'] . ' ' 
//                                        . WikiIocInfoManager::getInfo('locked');
//		}
//
//		$resp['info']   = $this->generateInfo( $infoType, $pageToSend['info'] );
//		$resp['locked'] = WikiIocInfoManager::getInfo('locked');
//
//		return $resp;
//	}

//	private function cleanResponse( $text ) {
//		global $lang;
//
//		$pattern = "/^(?:(?!<div class=\"editBox\").)*/s";// Captura tot el contingut abans del div que contindrá l'editor
//
//		preg_match( $pattern, $text, $match );
//		$info = $match[0];
//
//		$text = preg_replace( $pattern, "", $text );
//
//		// Eliminem les etiquetes no desitgades
//		$pattern = "/<div id=\"size__ctl\".*?<\/div>\\s*/s";
//		$text    = preg_replace( $pattern, "", $text );
//
//		// Eliminem les etiquetes no desitgades
//		$pattern = "/<div class=\"editButtons\".*?<\/div>\\s*/s";
//		$text    = preg_replace( $pattern, "", $text );
//
//		// Copiem el license
//		$pattern = "/<div class=\"license\".*?<\/div>\\s*/s";
//		preg_match( $pattern, $text, $match );
//		$license = $match[0];
//
//		// Eliminem la etiqueta
//		$text = preg_replace( $pattern, "", $text );
//
//		// Copiem el wiki__editbar
//		$pattern = "/<div id=\"wiki__editbar\".*?<\/div>\\s*<\/div>\\s*/s";
//		preg_match( $pattern, $text, $match );
//		$meta = $match[0];
//
//		// Eliminem la etiqueta
//		$text = preg_replace( $pattern, "", $text );
//
//		// Capturem el id del formulari
//		$pattern = "/<form id=\"(.*?)\"/";
//		//$form = "dw__editform";
//		preg_match( $pattern, $text, $match );
//		$form = $match[1];
//
//		// Afegim el id del formulari als inputs
//		$pattern = "/<input/";
//		$replace = "<input form=\"" . $form . "\"";
//		$meta    = preg_replace( $pattern, $replace, $meta );
//
//		// Netegem el valor
//		$pattern = "/value=\"string\"/";
//		$replace = "value=\"\"";
//		$meta    = preg_replace( $pattern, $replace, $meta );
//
//		$response["content"] = $text;
//		$response["info"]    = [ $info ];
//
//		if ( $license ) {
//			$response["info"][] = $license;
//		}
//
//		$metaId           = str_replace( ":", "_", $this->params['id'] ) . '_metaEditForm';
//		$response["meta"] = [
//			( $this->getCommonPage( $metaId,
//			                        $lang['metaEditForm'],
//			                        $meta ) + [ 'type' => 'summary' ] )
//		];
//
//		return $response;
//	}

    /**
     * Genera un element amb la informació correctament formatada i afegeix el timestamp. Si no s'especifica el id
     * s'assignarà el id del document que s'estigui gestionant actualment.
     *
     * Per generar un info associat al esdeveniment global s'ha de passar el id com a buit, es a dir
     *
     * @param string $type - tipus de missatge
     * @param string|string[] $message - Missatge o missatges associats amb aquesta informació
     * @param string $id - id del document al que pertany el missatge
     * @param int $duration - Si existeix indica la quantitat de segons que es mostrarà el missatge
     *
     * @return array - array amb la configuració del item de informació
     */
    public function generateInfo($type, $message, $id = NULL, $duration = -1)
    {
        if ($id === NULL) {
            $id = str_replace(":", "_", $this->params['id']);
        }

        return [
            "id" => $id,
            "type" => $type,
            "message" => $message,
            "duration" => $duration,
            "timestamp" => date("d-m-Y H:i:s")
        ];
    }

    // En els casos en que hi hagi discrepancies i no hi hagi cap preferencia es fa servir el valor de A
    public function addInfoToInfo($infoA, $infoB)
    {
        // Els tipus global de la info serà el de major gravetat: "debug" > "error" > "warning" > "info"
        $info = [];

        if ($infoA['type'] == 'debug' || $infoB['type'] == 'debug') {
            $info['type'] = 'debug';
        } else if ($infoA['type'] == 'error' || $infoB['type'] == 'error') {
            $info['type'] = 'error';
        } else if ($infoA['type'] == 'warning' || $infoB['type'] == 'warning') {
            $info['type'] = 'warning';
        } else {
            $info['type'] = $infoA['type'];
        }

        // Si algun dels dos te duració ilimitada, aquesta perdura
        if ($infoA['duration'] == -1 || $infoB['duration'] == -1) {
            $info['duration'] = -1;
        } else {
            $info['duration'] = $infoA['duration'];
        }

        // El $id i el timestamp ha de ser el mateix per a tots dos
        $info ['timestamp'] = $infoA['timestamp'];
        $info ['id'] = $infoA['id'];

        $messageStack = [];

        if (is_string($infoA ['message'])) {

            $messageStack[] = $infoA['message'];

        } else if (is_array($infoA['message'])) {

            $messageStack = $infoA['message'];

        }

        if (is_string($infoB ['message'])) {

            $messageStack[] = $infoB['message'];

        } else if (is_array($infoB['message'])) {

            $messageStack = array_merge($messageStack, $infoB['message']);

        }

        $info['message'] = $messageStack;

        return $info;
    }

//	private function getSaveInfoResponse( $code ) {
//		global $lang;
//		global $TEXT;
//		global $ID;
//
//		$duration = - 1;
//
//		if ( $code == 1004 ) {
//			$ret         = array();
//			$ret["code"] = $code;
//			$ret["info"] = $lang['wordblock'];
//			$ret["page"] = $this->getFormatedPageResponse();
//			$type        = "error";
//		} elseif ( $code == 1003 ) {
//			$ret         = array();
//			$ret["code"] = $code;
//			$ret["info"] = $lang['conflictsSaving']; //conflict
//			$ret["page"] = $this->getFormatedPageResponse();
//			$type        = "error";
//		} else {
//			$ret = array( "code" => $code, "info" => $lang["saved"] );
//			//TODO[Josep] Cal canviar els literals per referencies dinàmiques del maincfg
//			//      dw__editform, date i changecheck
//			$ret["formId"] = "dw__editform";
//			$ret["inputs"] = array(
//				"date"        => @filemtime( wikiFN( $ID ) ),
//				"changecheck" => md5( $TEXT )
//			);
//			$type          = 'success';
//			$duration      = 10;
//		}
//
//		// TODO[Xavi] PROVES, En cas d'exit el missatge només ha de durar 10s
//		$ret["info"] = $this->generateInfo( $type, $ret["info"], NULL, $duration );
//
//		return $ret;
//	}

    /**
     * TODO[Xavi] només genera la meta pel TOC
     *
     * @return array
     */
    public function getMetaResponse($id)
    {
        global $lang;
        global $ACT;
        $act_aux = $ACT;
        $ret = array('id' => \str_replace(":", "_", $id));
        //$ret = array('docId' => \str_replace(":", "_", $this->params['id']));
        $meta = array();
        $mEvt = new Doku_Event('WIOC_ADD_META', $meta);
        if ($mEvt->advise_before()) {
            $ACT = "show";
            $toc = $this->wrapper_tpl_toc();
            $ACT = $act_aux;
            $metaId = \str_replace(":", "_", $this->params['id']) . '_toc';
            $meta[] = ($this->getCommonPage($metaId, $lang['toc'], $toc) + ['type' => 'TOC']);
        }
        $mEvt->advise_after();
        unset($mEvt);
        $ret['meta'] = $meta;

        return $ret;
    }

    public function getJsInfo()
    {
        global $JSINFO;
        WikiIocInfoManager::loadInfo();
        return $JSINFO;
    }

    public function getToolbarIds(&$value)
    {
        $value["varName"] = "toolbar";
        $value["toolbarId"] = "tool__bar";
        $value["wikiTextId"] = "wiki__text";
        $value["editBarId"] = "wiki__editbar";
        $value["editFormId"] = "dw__editform";
        $value["summaryId"] = "edit__summary";
    }

    private function runBeforePreprocess(&$content)
    {
        global $ACT;

        $brun = FALSE;
        // give plugins an opportunity to process the action
        $this->ppEvt = new Doku_Event('ACTION_ACT_PREPROCESS', $ACT);
        ob_start();
        $brun = ($this->ppEvt->advise_before());
        $content = ob_get_clean();

        return $brun;
    }

    private function runAfterPreprocess(&$content)
    {
        ob_start();
        $this->ppEvt->advise_after();
        $content .= ob_get_clean();
        unset($this->ppEvt);
    }

//    private function getContentPage($pageToSend)
//    {
//        global $REV;
//        global $lang;
//
//        $pageTitle = tpl_pagetitle($this->params['id'], TRUE);
//
//        $pattern = '/^.*Aquesta és una revisió.*<hr \/>\\n\\n/mis';
//        $count = 0;
//        $info = NULL;
//        $pageToSend = preg_replace($pattern, '', $pageToSend, -1, $count);
//
//        if ($count > 0) {
//            $info = $this->generateInfo("warning", $lang['document_revision_loaded'] . ' <b>' . $this->extractDateFromRevision($REV, self::$SHORT_FORMAT) . '</b>');
//        }
//
//        $id = $this->params['id'];
//        $contentData = array(
//            'id' => str_replace(":", "_", $id),
//            'ns' => $id,
//            'title' => $pageTitle,
//            'content' => $pageToSend,
//            'rev' => $REV,
//            'info' => $info,
//            'type' => 'html',
//            'draft' => $this->generateFullDraft($id)
//        );
//
//        return $contentData;
//    }

    /**
     * Retorna una resposta amb les dades per mostrar un dialog de selecció esborrany-document.
     *
     * @param {string} $pid - id del document
     * @param {string} $prev - nombre de la revisió
     * @param  $prange
     * @param {string} $psum - nom del resúm
     *
     * @return array - resposta
     * @throws InsufficientPermissionToEditPageException
     * @throws PageNotFoundException
     */
    //[Alerta Josep] Es crida des de la comanda edit
    public function getDraftDialog($params)
    {/*$pid, $prev = NULL, $prange = NULL, $psum = NULL ) {*/
        //[TODO Josep] Normalitzar:...

        global $lang;

        $response = $this->getCodePage($params);

        if (WikiIocInfoManager::getInfo('locked')) {
            $response['info'] = $this->generateInfo('error', $lang['lockedby'] . ' '
                . WikiIocInfoManager::getInfo('locked'));
        } else {
            $response['show_draft_dialog'] = TRUE;
        }


        return $response;
    }


    ////_______________________________________________________________________________________________________________

//    /**
//     * Retorna el nom del fitxer de esborran corresponent al document i usuari actual
//     *
//     * @param string $id - id del document
//     *
//     * @return string
//     */
//    public function getDraftFilename($id)
//    {
//        return DraftManager::getDraftFilename($id);
//    }

    /**
     * Retorna cert si existeix un esborrany o no. En cas de que es trobi un esborrany més antic que el document es
     * esborrat.
     *
     * @param $id - id del document
     *
     * @return bool - cert si hi ha un esborrany vàlid o fals en cas contrari.
     */
    public function hasDraft($id)
    {
        $draftManager = new DraftManager($this);
        return $draftManager->hasDraft($id);
    }

    /**
     * Neteja una id passada per argument per poder fer-la servir amb els fitxers i si no es passa l'argument
     * intenta obtenir-la dels paràmetres.
     *
     * @param string $id - id a netejar
     *
     * @return mixed
     */

    private function getContainerIdFromPageId($id = NULL)
    {
        if ($id == NULL) {
            $id = $this->params['id'];
        }

        return WikiPageSystemManager::getContainerIdFromPageId($id);
    }


//    private function getAdminTaskListPage($id, $toSend){
//        global $lang;
//
//        return $this->getCommonPage($id, $lang['btn_admin'], $toSend);
//    }

//	private function getAdminTaskPage( $id, $task, $toSend ) {
//		//TO DO [JOSEP] Pasar el títol correcte segons idiaoma. Cal extreure'l de
//		//plugin(admin)->getMenuText($language);
//		return $this->getCommonPage( $id, $task, $toSend );
//	}

    private function getCommonPage($id, $title, $content)
    {
        $contentData = array(
            'id' => $id,
            'title' => $title,
            'content' => $content
        );

        return $contentData;
    }

//    private function getFormatedPage()
//    {
//        global $ACT;
//
//        ob_start();
//        trigger_event('TPL_ACT_RENDER', $ACT, array($this, 'onFormatRender'));
//        $html_output = ob_get_clean();
//        ob_start();
//        trigger_event('TPL_CONTENT_DISPLAY', $html_output, 'ptln');
//        $html_output = ob_get_clean();
//
//        return $html_output;
//    }


//	private function _getCodePage() {
//		global $ACT;
//		ob_start();
//		trigger_event( 'TPL_ACT_RENDER', $ACT, array($this, 'onCodeRender') );
//		$html_output = ob_get_clean();
//                ob_start();
//		trigger_event('TPL_CONTENT_DISPLAY', $html_output, 'ptln');
//                $html_output = ob_get_clean();
//
//		return $html_output;
//	}

    /**
     * Miguel Angel Lozano 12/12/2014
     * - Obtenir el gestor de medis
     */
    //ës la crida principal de la comanda media
    public function getMediaManager($image = NULL, $fromPage = NULL, $prev = NULL)
    {
        //[TODO Josep] Normalitzar: start do get ...
        global $lang, $NS, $INPUT, $JSINFO;
        /*if(!$NS){
            $NS = $fromPage;
        }
        $INPUT->access['ns'] = $NS;*/
        //   $NS = getNS($fromPage);
        //

        $error = $this->startMediaManager(DW_ACT_MEDIA_MANAGER, $image, $fromPage, $prev);
        if ($error == 401) {
            throw new HttpErrorCodeException($error, "Access denied");
        } else if ($error == 404) {
            throw new HttpErrorCodeException($error, "Resource " . $image . " not found.");
        }
        $title = $lang['img_manager'];
        $nou = trigger_event('IOC_WF_INTER', $ACT);
        $ret = array(
            "content" => $this->doMediaManagerPreProcess(),      //[ALERTA Josep] Pot venir amb un fragment de HTML i caldria veure què es fa amb ell.
            "id" => "media",
            "title" => "media",
            "ns" => $NS,
            "imageTitle" => $title,
            "image" => $image,
            "fromId" => $fromPage,
            "modifyImageLabel" => $lang['img_manager'],
            "closeDialogLabel" => $lang['img_backto']
        );
        $JSINFO = array('id' => "media", 'namespace' => $NS);
        return $ret;
    }

    /**
     * Init per a l'obtenció del MediaManager
     * Nota: aquesta funció ha tingut com a base startMediaProcess, però la separem per les següents raons:
     * - ha de considerar que és un altre $pdo
     * - ha de consdierar que l'Id de la imatge pot ser null
     * - en el futur volem partir la resposta de getMediaManager per ubicar cada component en l'àrea adient
     *   de la nostra pàgina principal de la dokuwiki_30
     */
    private function startMediaManager($pdo, $pImage = NULL, $pFromId = NULL, $prev = NULL)
    {
        global $ID;
        global $AUTH;
        global $vector_action;
        //global $vector_context;
        //global $loginname;
        global $IMG;
        global $ERROR;
        global $SRC;
        global $conf;
        global $lang;
        global $REV;

        $ret = $ERROR = 0;

        $this->params['action'] = $pdo;

        if ($pdo === DW_ACT_MEDIA_MANAGER) {
            $vector_action = $GET["vecdo"] = $this->params['vector_action'] = "media";
        }

        if ($pImage) {
            $IMG = $this->params['image'] = $pImage;
        }
        if ($pFromId) {
            $ID = $this->params['id'] = $pFromId;
        }
        if ($prev) {
            $REV = $this->params['rev'] = $prev;
        }
        // check image permissions
        if ($pImage) {
            $AUTH = auth_quickaclcheck($pImage);
            if ($AUTH >= AUTH_READ) {
                // check if image exists
                $SRC = mediaFN($pImage);
                if (!file_exists($SRC)) {
                    $ret = $ERROR = 404;
                }
            } else {
                // no auth
                $ret = $ERROR = 401;
            }
        }

        if ($ret != 0) {
            return $ret;
        }

        WikiIocInfoManager::loadMediaInfo();

        /**
         * Stores the template wide context
         *
         * This template offers discussion pages via common articles, which should be
         * marked as "special". DokuWiki does not know any "special" articles, therefore
         * we have to take care about detecting if the current page is a discussion
         * page or not.
         *
         * @var string
         * @author Andreas Haerter <development@andreas-haerter.com>
         */
        /* $vector_context = $this->params['vector_context'] = "article";
          if (preg_match("/^".tpl_getConf("ioc_template_discuss_ns")."?$|^".tpl_getConf("ioc_template_discuss_ns").".*?$/i", ":".getNS(getID()))){
          $vector_context = $this->params['vector_context'] = "discuss";
          } */

        /**
         * Stores the name the current client used to login
         *
         * @var string
         * @author Andreas Haerter <development@andreas-haerter.com>
         */
        /* $loginname = $this->params['loginName'] = "";
          if (!empty($conf["useacl"])) {
          if (isset($_SERVER["REMOTE_USER"]) && //no empty() but isset(): "0" may be a valid username...
          $_SERVER["REMOTE_USER"] !== "") {
          $loginname = $this->params['loginName'] = $_SERVER["REMOTE_USER"]; //$INFO["client"] would not work here (-> e.g. if
          //current IP differs from the one used to login)
          }
          } */

        $this->startUpLang();

        //detect revision
        $rev = $this->params['rev'] = (int)WikiIocInfoManager::getInfo("rev"); //$INFO comes from the DokuWiki core
        if ($rev < 1) {
            $rev = $this->params['rev'] = (int)WikiIocInfoManager::getInfo("lastmod");
        }

        $this->triggerStartEvents();

        return $ret;
    }

    private function doMediaManagerPreProcess()
    {
        global $ACT;
        global $JUMPTO;

        $content = "";
        if ($this->runBeforePreprocess($content)) {
            ob_start();
            // tpl_media(); //crida antiga total del media manager
            //crida parcial: només a la llista de fitxers del directori
            $this->mediaManagerFileList();
            $content .= ob_get_clean();
            // check permissions again - the action may have changed
            $ACT = act_permcheck($ACT);
        }
        $this->runAfterPreprocess($content);

        return $content;
    }

    /**
     * Prints full-screen media manager
     *
     * @author Kate Arzamastseva <pshns@ukr.net>
     */

    function mediaManagerFileList()
    {
        global $NS, $IMG, $JUMPTO, $REV, $lang, $fullscreen, $INPUT, $AUTH;
        $fullscreen = TRUE;
        require_once DOKU_INC . 'lib/exe/mediamanager.php';

        $rev = '';
        $image = cleanID($INPUT->str('image'));
        if (isset($IMG)) {
            $image = $IMG;
        }
        if (isset($JUMPTO)) {
            $image = $JUMPTO;
        }
        if (isset($REV) && !$JUMPTO) {
            $rev = $REV;
        }

        echo '<div id="mediamanager__page">' . NL;
        if ($NS == "") {
            echo '<h1>Documents de l\'arrel de documents</h1>';
        } else {
            echo '<h1>Documents de ' . $NS . '</h1>';
        }


        echo '<div class="panel filelist ui-resizable">' . NL;
        echo '<div class="panelContent">' . NL;
        $do = $AUTH;
        $query = $_REQUEST['q'];
        if (!$query) {
            $query = '';
        }
        if ($do == 'searchlist' || $query) {
            media_searchlist($query, $NS, $AUTH, TRUE, $_REQUEST['sort']);
        } else {
            media_tab_files($NS, $AUTH, $JUMPTO);
        }
        echo '</div>' . NL;
        echo '</div>' . NL;
        echo '</div>' . NL;
    }

    public function getMediaMetaResponse()
    {
        global $NS, $IMG, $JUMPTO, $REV, $lang, $fullscreen, $INPUT;
        $fullscreen = TRUE;
        require_once DOKU_INC . 'lib/exe/mediamanager.php';

        $rev = '';
        $image = cleanID($INPUT->str('image'));
        if (isset($IMG)) {
            $image = $IMG;
        }
        if (isset($JUMPTO)) {
            $image = $JUMPTO;
        }
        if (isset($REV) && !$JUMPTO) {
            $rev = $REV;
        }
        ob_start();

        echo '<div id="mediamanager__meta">' . NL;
        echo '<h1>' . $lang['btn_media'] . '</h1>' . NL;
        html_msgarea();

        echo '<div class="panel namespaces">' . NL;
        echo '<h2>' . $lang['namespaces'] . '</h2>' . NL;
        echo '<div class="panelHeader">';
        echo $lang['media_namespaces'];
        echo '</div>' . NL;

        echo '<div class="panelContent" id="media__tree">' . NL;
        media_nstree($NS);
        echo '</div>' . NL;
        echo '</div>' . NL;
        echo '</div>' . NL;

        echo '</div>' . NL;
        $meta = ob_get_clean();
        $ret = array('id' => $NS);
        // $mEvt = new Doku_Event('WIOC_ADD_META', $meta);
        /* if ($mEvt->advise_before()) {
                $ACT = "show";
                $toc = $this->wrapper_tpl_toc();
                $ACT = $act_aux;
                $metaId = \str_replace(":", "_", $this->params['id']) . '_toc';
                $meta[] = $this->getMetaPage($metaId, $lang['toc'], $toc);
            }*/
        //$mEvt->advise_after();
        //unset($mEvt);
        $ret['meta'] = $meta;

        return $ret;
    }

    public function getMediaTabFileOptions()
    {
        global $INPUT;

        $checkThumbs = "checked";
        $checkRows = "";
        if ($INPUT->str('list')) {
            if ($INPUT->str('list') == "rows") {
                $checkThumbs = "";
                $checkRows = "checked";
            }
        }
        ob_start();
        echo '<span style="font-weight: bold;">Visualització</span></br>';
        echo '<div style="margin-left:10px;">';
        echo '  <input type="radio" data-dojo-type="dijit/form/RadioButton" name="fileoptions" id="thumbs" value="thumbs" ' . $checkThumbs . '/>
                <label for="radioOne">Thumbnails</label> <br />';
        echo '  <input type="radio" data-dojo-type="dijit/form/RadioButton" name="fileoptions" id="rows" value="rows" ' . $checkRows . '/>
                <label for="radioTwo">Rows</label> <br/><br/></div>';
        $strData = ob_get_clean();
        /*$tree_ret = array(
            'id'      => 'metaMediafileoptions',
            'title'   => "Visualització",
            'content' => $strData
        );*/

        //return $tree_ret;
        return $strData;
    }

    public function getMediaTabFileSort()
    {
        global $INPUT;
        $checkedNom = "checked";
        $checkedData = "";
        if ($INPUT->str('sort')) {
            if ($INPUT->str('sort') == "date") {
                $checkedNom = "";
                $checkedData = "checked";
            }
        }

        ob_start();
        /*echo '  <input type="radio" name="drink" id="radioOne" checked value="tea"/>
                <label for="radioOne">Tea</label> <br />';*/
        echo '<span style="font-weight: bold;">Ordenació</span></br>';
        echo '<div style="margin-left:10px;">';
        echo '  <input type="radio" data-dojo-type="dijit/form/RadioButton" name="filesort" id="nom" value="name" ' . $checkedNom . '/>
                <label for="nom">Nom</label> <br />';
        echo '  <input type="radio" data-dojo-type="dijit/form/RadioButton" name="filesort" id="data" value="date" ' . $checkedData . '/>
                <label for="data">Data</label> <br/><br/></div>';
        //echo '<div class="panelContent dokuwiki" id="metamedia__fileoptions">' . NL;
        //media_tab_files_options();
        //echo '</div>' . NL;
        $strData = ob_get_clean();
        /*$tree_ret = array(
            'id'      => 'metaMediafilesort',
            'title'   => "Ordenació",
            'content' => $strData
        );*/

        //return $tree_ret;
        return $strData;
    }

    public function getMediaTabSearch()
    {
        global $NS;
        ob_start();
        echo '<span style="font-weight: bold;">Cerca</span></br>';
        echo '<div class="search" style="margin-left:10px;">';
        echo '<form accept-charset="utf-8" method="post"  id="dw__mediasearch">';
        echo '<div class="no">';
        //echo '<p><label><span>Cerca pel nom de fitxer: </span>';
        echo '<p>';
        echo '<input type="text" id="mediaSearchq" placeholder = "Nom de fitxer" title="Cerca en: ' . $NS . '" class="edit" name="q">';
        echo '</label>';
        echo '<input type="submit" class="button" value="Filtrar" id="mediaSearchs">';
        echo '<input style="display:none" type="submit" class="button" value="Desfer filtre" id="mediaSearchr">';
        echo '</p>';
        echo '</div></form></div>';

        $strData = ob_get_clean();
        /*$tree_ret = array(
            'id'      => 'metaMediaSearch',
            'title'   => "Cerca",
            'content' => $strData
        );*/

        //return $tree_ret;
        return $strData;
    }

    public function getMediaFileUpload()
    {
        global $NS, $AUTH, $JUMPTO;
        ob_start();
        media_tab_upload($NS, $AUTH, $JUMPTO);
        $strData = ob_get_clean();
        $tree_ret = array(
            'id' => 'metaMediafileupload',
            'title' => "Càrrega de fitxers",
            'content' => $strData
        );

        return $tree_ret;
    }

    function MediaUpload()
    {
        global $NS, $MSG, $INPUT;

        if ($_FILES['qqfile']['tmp_name']) {
            $id = $INPUT->post->str('mediaid', $_FILES['qqfile']['name']);
        } elseif ($INPUT->get->has('qqfile')) {
            $id = $INPUT->get->str('qqfile');
        }

        $id = cleanID($id);

        $NS = $INPUT->str('ns');
        $ns = $NS . ':' . getNS($id);

        $AUTH = auth_quickaclcheck("$ns:*");
        if ($AUTH >= AUTH_UPLOAD) {
            io_createNamespace("$ns:xxx", 'media');
        }

        if ($_FILES['qqfile']['error']) {
            unset($_FILES['qqfile']);
        }

        // if ($_FILES['qqfile']['tmp_name']) $res = media_upload($NS, $AUTH, $_FILES['qqfile']);
        // if ($INPUT->get->has('qqfile')) $res = media_upload_xhr($NS, $AUTH);
        media_upload($NS, $AUTH);
        if ($res) {
            $result = array(
                'success' => TRUE,
                'link' => media_managerURL(array('ns' => $ns, 'image' => $NS . ':' . $id), '&'),
                'id' => $NS . ':' . $id,
                'ns' => $NS
            );
        }

        if (!$result) {
            $error = '';
            if (isset($MSG)) {
                foreach ($MSG as $msg) {
                    $error .= $msg['msg'];
                }
            }
            $result = array('error' => $msg['msg'], 'ns' => $NS);
            //$_FILES = array();
            //unset($_FILES['upload']);
            //$_FILES['upload']['error']="No s'ha pogut pujar el fitxer";
        }
        //$json = new JSON;
        //echo htmlspecialchars($json->encode($result), ENT_NOQUOTES);
    }

    //És la crida principal de la comanda ns_mediatree_rest
    public function getNsMediaTree($currentnode, $sortBy, $onlyDirs = FALSE)
    {
        $dataQuery = $this->persistenceEngine->createMediaDataQuery();
        return $dataQuery->getNsTree($currentnode, $sortBy, $onlyDirs);
    }

    /**
     * FI Miguel Angel Lozano 12/12/2014
     */

    public function getLoginName()
    {
        global $conf;

        $loginname = "";
        if (!empty($conf["useacl"])) {
            if (isset($_SERVER["REMOTE_USER"]) && //no empty() but isset(): "0" may be a valid username...
                $_SERVER["REMOTE_USER"] !== ""
            ) {
                $loginname = $_SERVER["REMOTE_USER"]; //$INFO["client"] would not work here (-> e.g. if
                //current IP differs from the one used to login)
            }
        }

        return $loginname;
    }

    public function getRevisions($id)
    {
        //[TODO Josep] Normalitzar: start do get ...
        global $ID;
        global $ACT;

        // START
        // Només definim les variables que es passen per paràmetre, la resta les ignorem

        $ACT = 'revisions';

        $this->triggerStartEvents();
        session_write_close();

        $content = "";
        if ($this->runBeforePreprocess($content)) {
            act_permcheck($ACT);
        }

        $this->runAfterPreprocess($content);

        $this->startUpLang();

        // DO real

        $revisions = getRevisions($ID, -1, 50);

        $ret = [];

        foreach ($revisions as $revision) {
            $ret[$revision] = getRevisionInfo($ID, $revision);
            $ret[$revision]['date'] = $this->extractDateFromRevision($ret[$revision]['date']);
            //unset ($ret[$revision]['id']);
        }
        $ret['current'] = @filemtime(wikiFN($ID));
        $ret['docId'] = $ID;

        $this->triggerEndEvents();

        return $ret;
    }

    /**
     * Extreu la data a partir del nombre de revisió
     *
     * @param int $revision - nombre de la revisió
     * @param int $mode - format de la data
     *
     * @return string - Data formatada
     *
     */
    public function extractDateFromRevision($revision, $mode = NULL)
    {
//            if(!$mode){
//                $mode = WikiPageSystemManager::$DEFAULT_FORMAT;
//            }
        return WikiPageSystemManager::extractDateFromRevision($revision, $mode);
    }

    public function getDiffPage($id, $rev1, $rev2 = NULL)
    {
        //[TODO Josep] Normalitzar: start do get...
        // START
        // Només definim les variables que es passen per paràmetre, la resta les ignorem

        global $ID;
        global $ACT;
        global $REV;
        global $lang;
        global $INPUT;

        $ID = $id;
        $REV = $rev1;
        $ACT = 'diff';

        $this->triggerStartEvents();
//		$tmp = [ ];
//		trigger_event( 'DOKUWIKI_START', $tmp );
        session_write_close();

//		$evt = new Doku_Event( 'ACTION_ACT_PREPROCESS', $ACT );
//		if ( $evt->advise_before() ) {
//			act_permcheck( $ACT );
//			unlock( $ID );
//		}
//		$evt->advise_after();
//		unset( $evt );
        $content = "";
        if ($this->runBeforePreprocess($content)) {
            act_permcheck($ACT);
            unlock($ID);
        }

        //desactivem aquesta crida perquè es tracta d'una crida AJAX i no es pot modificar la capçalera
//		$headers[] = 'Content-Type:application/json; charset=utf-8';
//
//		trigger_event( 'ACTION_HEADERS_SEND', $headers, 'act_sendheaders' );		

        $this->startUpLang();

        //descativem aquesta crida perquè les revisions no es retornen
        //rederitzades sinó que es rendaritzen al client
        //trigger_event( 'TPL_ACT_RENDER', $ACT, "tpl_content_core");

        // En aquest punt es on es generaria el codi HTML

        //descativem aquesta crida perquè des del dokumodeladapter el
        //display ja està fet i no servidria de res tornar a llançar
        //aquest esdeveniment.
//		$temp = [ ];
//		trigger_event( 'TPL_CONTENT_DISPLAY', $temp );

        // DO real

        //side_by_side

        if ($INPUT->ref('difftype')) {
            $difftype = $INPUT->ref('difftype');
        } else {
            $difftype = 'sidebyside';
        }

        if ($difftype == 'sidebyside') {
            ob_start();
            html_diff('', TRUE, $type = 'sidebyside');
            $content = ob_get_clean();
        } else {
            ob_start();
            html_diff('', TRUE, $type = 'inline');
            $content = ob_get_clean();
        }

        $response = [
            'id' => \str_replace(":", "_", $ID),
            'ns' => $ID,
            "title" => $ID,
            "content" => $this->clearDiff($content),
            "type" => 'diff'
        ];

        $response['info'] = $this->generateInfo("info", $lang['diff_loaded']);

        $meta = [
            ($this->getCommonPage($response['id'] . '_switch_diff_mode ',
                    $lang['switch_diff_mode'],
                    $this->extractMetaContentFromDiff($content)
                ) + ['type' => 'switch_diff_mode'])
        ];

        $response["meta"] = ['id' => $response['id'], 'meta' => $meta];

        $this->triggerEndEvents();
//		$temp = [ ];
//		trigger_event( 'DOKUWIKI_DONE', $temp );

        return $response;
    }

    /**
     * Extreu la informació que volem fer servir com a meta pels diff, en aquest cas només ens interessa el formulari.
     *
     * S'afegeix una id única pel formulari per poder seleccionar-lo al frontend.
     *
     * @param string $content - contingut del que volem extreure el formulari
     *
     * @return string - cadena amb el codi html per reconstruir el formulari.
     */
    public function extractMetaContentFromDiff($content)
    {
        global $ID;

        $pattern = '/<form.*<\/form>/s';
        preg_match($pattern, $content, $matches);

        $pattern = '/<form /s';
        $replace = '<form id="switch_mode_' . str_replace(":", "_", $ID) . '" ';

        $metaContent = preg_replace($pattern, $replace, $matches[0]);

        return $metaContent;
    }

    public function clearDiff($content)
    {
        $pattern = '/^.+?(?=<div class="table">)/s';

        return preg_replace($pattern, '', $content);
    }

    /**
     * Afegeix al paràmetre $value els selectors css que es
     * fan servir per seleccionar els forms al html del pluguin ACL
     *
     * @param array $value - array de paràmetres
     *
     */
    public function getAclSelectors(&$value)
    {
        $value["saveSelector"] = "#acl__detail form:submit";
        $value["updateSelector"] = "#acl_manager .level2 form:submit";
    }

    /**
     * Afegeix al paràmetre $value els selectors css que es
     * fan servir per seleccionar els forms al html del pluguin PLUGIN
     *
     * @param array $value - array de paràmetres
     *
     */
    public function getPluginSelectors(&$value)
    {
        $value["commonSelector"] = "div.common form:submit";
        $value["pluginsSelector"] = "form.plugins:submit";
    }

    /**
     * Afegeix al paràmetre $value els selectors css que es
     * fan servir per seleccionar els forms al html del pluguin CONFIG
     *
     * @param array $value - array de paràmetres
     *
     */
    public function getConfigSelectors(&$value)
    {
        $value["configSelector"] = "#config__manager form:submit";
    }

    /**
     * Afegeix al paràmetre $value els selectors css que es
     * fan servir per seleccionar els forms al html del pluguin USERMANAGER
     *
     * @param array $value - array de paràmetres
     *
     */
    public function getUserManagerSelectors(&$value)
    {
        $value["formsSelector"] = "#user__manager form:submit";
        $value["exportCsvName"] = "fn[export]";
    }

    /**
     * Afegeix al paràmetre $value els selectors css que es
     * fan servir per seleccionar els forms al html del pluguin REVERT
     *
     * @param array $value - array de paràmetres
     *
     */
    public function getRevertSelectors(&$value)
    {
        $value["revertSelector"] = "#admin_revert form:submit";
    }

    /**
     * Afegeix al paràmetre $value els selectors css que es
     * fan servir per seleccionar els forms al html del pluguin LATEX
     *
     * @param array $value - array de paràmetres
     *
     */
    public function getLatexSelectors(&$value)
    {
        $value["latexSelector"] = "div.level2 form:submit"; //form
        $value["latexpurge"] = "latexpurge"; // input name purge
        $value["dotest"] = "dotest"; // input name test
    }

    /**
     * Miguel Angel Lozano 21/04/2015
     * MEDIA DETAILS: Obtenció dels detalls de un media
     */
    //És la crida principal de la comanda mediadetails
    public function getMediaDetails($image)
    {
        //[TODO Josep] Normalitzar: start do get ...
        global $lang, $NS, $JSINFO, $MSG, $INPUT;

        $error = $this->startMediaDetails(DW_ACT_MEDIA_DETAILS, $image);
        if ($error == 401) {
            throw new HttpErrorCodeException($error, "Access denied");
        } else if ($error == 404) {
            throw new HttpErrorCodeException($error, "Resource " . $image . " not found.");
        }
        $title = $lang['img_manager'];
        $ret = array(
            "content" => $this->doMediaDetailsPreProcess(),     //[ALERTA Josep] Pot venir amb un fragment de HTML i caldria veure què es fa amb ell.
            "id" => $image,
            "title" => $image,
            "ns" => $NS,
            "imageTitle" => $image,
            "image" => $image
        );
        $do = $INPUT->str('mediado');
        if ($do == 'diff') {
            $ret["mediado"] = "diff";
        }
        if ($MSG[0]) {
            if ($MSG[0]['lvl'] == 'error') {
                throw new HttpErrorCodeException(404, $MSG[0]['msg']);
            }
        }
        $JSINFO = array('id' => $image, 'namespace' => $NS);

        return $ret;
    }

    /**
     * Init per a l'obtenció del Media Details
     * Nota: aquesta funció ha tingut com a base startMediaProcess, però la separem per les següents raons:
     */
    private function startMediaDetails($pdo, $pImage)
    {
        global $ID;
        global $AUTH;
        global $IMG;
        global $ERROR;
        global $SRC;
        global $conf;
        global $lang;
        global $REV;
        $ret = $ERROR = 0;
        $this->params['action'] = $pdo;

        if ($pImage) {
            $IMG = $this->params['image'] = $pImage;
        }
        $ID = $pImage;

        // check image permissions
        if ($pImage) {
            $AUTH = auth_quickaclcheck($pImage);
            if ($AUTH >= AUTH_READ) {
                // check if image exists
                $SRC = mediaFN($pImage);
                if (!file_exists($SRC)) {
                    $ret = $ERROR = 404;
                }
            } else {
                // no auth
                $ret = $ERROR = 401;
            }
        }

        if ($ret != 0) {
            return $ret;
        }

        WikiIocInfoManager::loadMediaInfo();
        $this->startUpLang();

        //detect revision
        $rev = $this->params['rev'] = (int)WikiIocInfoManager::getInfo("rev"); //$INFO comes from the DokuWiki core
        if ($rev < 1) {
            $rev = $this->params['rev'] = (int)WikiIocInfoManager::getInfo("lastmod");
        }

        $this->triggerStartEvents();

        return $ret;
    }

    private function doMediaDetailsPreProcess()
    {
        global $ACT;
        global $JUMPTO;

        $content = "";
        if ($this->runBeforePreprocess($content)) {
            ob_start();
            $content = $this->mediaDetailsContent();

            // check permissions again - the action may have changed
            $ACT = act_permcheck($ACT);
        }
        $this->runAfterPreprocess($content);

        return $content;
    }

    /**
     * Prints full-screen media details
     */

    function mediaDetailsContent()
    {


        global $NS, $IMG, $JUMPTO, $REV, $lang, $conf, $fullscreen, $INPUT, $AUTH;
        $fullscreen = TRUE;
        require_once DOKU_INC . 'lib/exe/mediamanager.php';

        $rev = '';
        $image = cleanID($INPUT->str('image'));
        if (isset($IMG)) {
            $image = $IMG;
        }
        if (isset($JUMPTO)) {
            $image = $JUMPTO;
        }
        if (isset($REV) && !$JUMPTO) {
            $rev = $REV;
        }

        $content = "";
        $do = $INPUT->str('mediado');
        if ($do == 'diff') {
            echo '<div id="panelMedia_' . $image . '" class="panelContent">' . NL;
            media_diff($image, $NS, $AUTH);
            echo '</div>' . NL;
            $content .= ob_get_clean();
            $patrones = array();
            $patrones[0] = '/<form id="mediamanager__btn_restore"/';
            $patrones[1] = '/<form id="mediamanager__btn_delete"/';
            $patrones[2] = '/<form id="mediamanager__btn_update"/';
            $sustituciones = array();
            $sustituciones[0] = '<form id="mediamanager__btn_restore_' . $image . '"';
            $sustituciones[1] = '<form id="mediamanager__btn_delete_' . $image . '"';
            $sustituciones[2] = '<form id="mediamanager__btn_update_' . $image . '"';
            $content = preg_replace($patrones, $sustituciones, $content);
        } else {
            echo '<div id="panelMedia_' . $image . '" class="panelContent">' . NL;
            $meta = new JpegMeta(mediaFN($image, $rev));
            $size = media_image_preview_size($image, $rev, $meta);
            if ($size) {
                echo '<div style="float:left;width:47%;margin-right:10px;">' . NL;
                media_preview($image, $AUTH, $rev, $meta);
                echo '</div>' . NL;
            }

            echo '<div style="float:left;width:20%;">' . NL;
            echo '<h1>Dades de ' . $image . '</h1>';
            media_details($image, $auth, $rev, $meta);
            echo '</div>' . NL;

            if ($_REQUEST['tab_details']) {
                if (!$size) {
                    $tr = ob_get_clean();
                    throw new HttpErrorCodeException(1001, "No es poden editar les dades d'aquest element");
                } else {
                    if ($_REQUEST['tab_details'] == 'edit') {
                        //$this->params['id'] = "form_".$image;
                        echo '<div style="float:right;margin-right:5px;width:29%;">' . NL;
                        echo "<h1>Formulari d'edició de " . $image . '</h1>';
                        media_metaform($image, $AUTH);
                        echo '</div>' . NL;
                    }
                }
            }
            echo '</div>' . NL;
            $content .= ob_get_clean();
            $patrones = array();
            $patrones[0] = '/<form/';
            $patrones[1] = '/style="max-width:+\s+\d+px;"/';
            $sustituciones = array();
            $sustituciones[0] = '<form id="form_' . $image . '"';
            $sustituciones[1] = '<img style="width: 60%;"';
            $content = preg_replace($patrones, $sustituciones, $content);
        }
        return $content;


    }

    /**
     * Per a històric de media details a la meta
     */

    function mediaDetailsHistory()
    {
        global $NS, $IMG, $JUMPTO, $REV, $lang, $conf, $fullscreen, $INPUT, $AUTH;
        $fullscreen = TRUE;
        require_once DOKU_INC . 'lib/exe/mediamanager.php';

        $image = cleanID($INPUT->str('image'));
        $ns = $INPUT->str('ns');

        ob_start();
        //media_tab_history($image, $ns, $AUTH);
        $first = $INPUT->int('first');
        html_revisions($first, $image);
        $content = ob_get_clean();
        /*
                 * Substitució de l'id del form per fer-ho variable
                 */
        $patrones = array();
        $patrones[0] = '/form id="page__revisions"/';
        $sustituciones = array();
        $sustituciones[0] = 'form id="page__revisions_' . $image . '"';
        $content = preg_replace($patrones, $sustituciones, $content);
        return $content;
    }


    // TODO[Xavi] PER SUBISTIUIR PEL PLUGIN DEL RENDER
    private static function getInstructionsForDocument($id, $rev = null)
    {
        $file = wikiFN($id, $rev);
        //[ALERTA JOSEP] CAL FER SERVIR p_cached_instructions. A més caldria traslladar aquesta funció a PageDataQuery i cridar el mètode adient de PagedataQuery des d'aquí. 
//        $cache = new cache_instructions($id, $file); // TODO[Xavi] Això fa falta?
//        $instructions = unserialize(io_readFile($cache->cache));
        $instructions = p_cached_instructions($file, FALSE, $id);
        return $instructions;
    }

    // TODO[Xavi] PER SUBISTIUIR PEL PLUGIN DEL RENDER
    private static function getHtmlForDocument($id, $rev = null)
    {
        $html = self::p_wiki_xhtml($id, $rev, true);

        return $html;
    }


    /**
     * Hi ha un casos en que no hi ha selected, per exemple quan es cancela un document.
     *
     * @param $selected
     * @param $id
     * @param $rev
     * @param null $editing
     * @return array
     * @throws InsufficientPermissionToViewPageException
     * @throws PageNotFoundException
     * @internal param $ {string|null} $selected - Chunk seleccionat $selected - Chunk seleccionat
     */
    // TODO[Xavi] PER REFACTORITZAR QUANT TINGUEM EL PLUGIN DEL RENDER. Fer privada?
    public function getStructuredDocument($selected, $id, $rev = null, $editing = null, $recoverDraft = false)
    {

        if (!$editing && $selected) {
            $editing = [$selected];
        } else if (!$editing) {
            $editing = [];
        }

        $document = [];


        $document['title'] = tpl_pagetitle($id, TRUE);
        $document['ns'] = $id;
        $document['id'] = str_replace(":", "_", $id);
        $document['rev'] = $rev;
        $document['selected'] = $selected;
        $document['date'] = WikiIocInfoManager::getInfo('meta')['date']['modified'] + 1;

        $html = self::getHtmlForDocument($id, $rev);
        $document['html'] = $html;

        $headerIds = $this->getHeadersFromHtml($html);

        //S'han unificat les dues instruccions següents a PageDataQuery sota el nom únic de getChunks
        $instructions = self::getInstructionsForDocument($id, $rev);

        $chunks = self::getChunks($instructions);

        $editingChunks = [];
        $dictionary = [];

        $this->getEditingChunks($editingChunks, $dictionary, $chunks, $id, $headerIds, $editing);

        // Afegim el suf
        $lastSuf = count($editingChunks) - 1;
        //[ALERTA JOSEP] Cal passar rawWikiSlices a PageDataQuery i fer la crida des d'allà
        $document['suf'] = rawWikiSlices($editingChunks[$lastSuf]['start'] . "-" . $editingChunks[$lastSuf]['end'], $id)[2];


        $this->addPreToChunks($editingChunks, $id);

        $document['chunks'] = $chunks;
        $document['dictionary'] = $dictionary;
        $document['locked'] = $this->checklock($id); // Nom del usuari que el te bloquejat o false es lliure

        return $document;
    }

    private static function getHeadersFromHtml($html)
    {
        $pattern = '/(?:<h[123] class="sectionedit\d+" id=")(.+?)">/s'; // aquest patró només funciona si s'aplica el scedit
        preg_match_all($pattern, $html, $match);
        return $match[1]; // Conté l'array amb els ids trobats per cada secció
    }

    private static function getEditingChunks(&$editingChunks, &$dictionary = [], &$chunks, $id, $headerIds, $editing)
    {
        for ($i = 0; $i < count($chunks); $i++) {
            $chunks[$i]['header_id'] = $headerIds[$i];
            // Afegim el text només al seleccionat i els textos en edició
            if (in_array($headerIds[$i], $editing)) {
                $chunks[$i]['text'] = [];
                //TODO[Xavi] compte! s'ha d'agafar sempre el editing per montar els nostres pre i suf!
                $chunks[$i]['text']['editing'] = rawWikiSlices($chunks[$i]['start'] . "-" . $chunks[$i]['end'], $id)[1];
                $chunks[$i]['text']['changecheck'] = md5($chunks[$i]['text']['editing']);

                $editingChunks[] = &$chunks[$i];

            }
            $dictionary[$headerIds[$i]] = $i;
        }
    }

    public static function getAllChunksWithText($id)
    {
        $html = self::getHtmlForDocument($id);
        $headerIds = self::getHeadersFromHtml($html);
        $instructions = self::getInstructionsForDocument($id);
        $chunks = self::getChunks($instructions);
        $editing = $headerIds;
        $editingChunks = [];
        $dictionary = [];

        self::getEditingChunks($editingChunks, $dictionary, $chunks, $id, $headerIds, $editing);

        return ['chunks' => $editingChunks, 'dictionary' => $dictionary];

    }

//    // Hi ha draft pel chunk a editar?
//    private function thereIsStructuredDraftFor($id, $document, $selected = null)
//    {
//        if (!$selected) {
//            return false;
//        }
//
//        $draft = $this->getStructuredDraft($id);
//
//        for ($i = 0; $i < count($document['chunks']); $i++) {
//            if (array_key_exists($document['chunks'][$i]['header_id'], $draft)
//                && $document['chunks'][$i]['header_id'] == $selected
//            ) {
//
//                // Si el contingut del draft i el propi es igual, l'eliminem
//                if ($document['chunks'][$i]['text'] . ['editing'] == $draft[$selected]['content']) {
//                    $this->removeStructuredDraft($id, $selected);
//                } else {
//                    return true;
//                }
//
//            }
//
//        }
//
//        return false;
//    }


    // TODO[Xavi] PER SUBISTIUIR PEL PLUGIN DEL RENDER
    private function addPreToChunks(&$chunks, $id)
    {
        //[ALERTA JOSEP] Cal passar rawWikiSlices a PageDataQuery i fer la crida des d'allà
        $lastPos = 0;

        for ($i = 0; $i < count($chunks); $i++) {
            // El pre de cada chunk va de $lastPos fins al seu start
            $chunks[$i]['text']['pre'] = rawWikiSlices($lastPos . "-" . $chunks[$i]['start'], $id)[1];

            // el text no forma part del 'pre'
            $lastPos = $chunks[$i]['end'];
        }

    }


    // TODO[Xavi] PER SUBISTIUIR PEL PLUGIN DEL RENDER
    // Només son editables parcialment les seccions de nivell 1, 2 i 3
    private static function getChunks($instructions)
    {
        $sections = [];
        $currentSection = [];
        $lastClosePosition = 0;
        $lastHeaderRead = '';
        $firstSection = true;


        for ($i = 0; $i < count($instructions); $i++) {
            $currentSection['type'] = 'section';

            if ($instructions[$i][0] === 'header') {
                $lastHeaderRead = $instructions[$i][1][0];
            }

            if ($instructions[$i][0] === 'section_open' && $instructions[$i][1][0] < 4) {
                // Tanquem la secció anterior
                if ($firstSection) {
                    // Ho descartem, el primer element no conté informació
                    $firstSection = false;
                } else {
                    $currentSection['end'] = $instructions[$i][2];
                    $sections[] = $currentSection;
                }

                // Obrim la nova secció
                $currentSection = [];
                $currentSection['title'] = $lastHeaderRead;
                $currentSection['start'] = $instructions[$i][2];
                $currentSection['params']['level'] = $instructions[$i][1][0];
            }

            // Si trobem un tancament de secció actualitzem la ultima posició de tancament
            if ($instructions[$i][0] === 'section_close') {
                $lastClosePosition = $instructions[$i][2];
            }

        }
        // La última secció es tanca amb la posició final del document
        $currentSection['end'] = $lastClosePosition;
        $sections[] = $currentSection;

        return $sections;
    }

/*
    // Només la pàgina actual pot ser editada parcialment
    public function getPartialPage($pid, $prev = NULL, $prange, $psum, $psection)
    {
        global $lang;

        //$this->startPageProcess(DW_ACT_EDIT, $pid, NULL, $prange, $psum); EDIT?
        $this->startPageProcess(DW_ACT_SHOW, $pid, NULL, $prange, $psum);
        
         if (!WikiIocInfoManager::getInfo("exists")) {
            throw new PageNotFoundException($id, $lang['pageNotFound']);
        }
        if (!WikiIocInfoManager::getInfo("perm")) {
            throw new InsufficientPermissionToViewPageException($id); //TODO [Josep] Internacionalització missatge per defecte!
        }

        $this->doFormatedPartialPagePreProcess();   

        $response['structure'] = $this->getStructuredDocument($psection, $pid, NULL);

        // TODO: afegir el 'info' que correspongui

        // Si no s'ha especificat cap altre missatge mostrem el de carrega
        if (!$response['info']) {
            $response['info'] = $this->generateInfo("info", $lang['document_loaded']);
        }

        // TODO: afegir el 'meta' que correspongui
        $response['meta'] = $this->getMetaResponse($pid);

        // TODO: afegir les revisions
        $response['revs'] = $this->getRevisions($pid);

        return $response;
    }
*/

    public function cancelPartialEdition($params){
        $action = new CancelPartialEditPageAction($this->persistenceEngine);
        return $action->get($params);
        
//    public function cancelPartialEdition($pid, $prev = NULL, $psum = NULL, $selected, $editing_chunks = NULL, $keep_draft = false)
//    {
//        global $lang;
//
//        $this->startPageProcess(DW_ACT_SHOW, $pid, null, NULL, $psum);
//
//        $response['structure'] = $this->getStructuredDocument(null, $pid, NULL, $editing_chunks);
//        $response['structure']['cancel'] = [$selected];
//
//        if (!$keep_draft) {
//            $this->removeStructuredDraft($pid, $selected);
//        }
//
//        // TODO: afegir el 'info' que correspongui
//        if (!$response['info']) {
//            $response['info'] = $this->generateInfo("info", $lang['chunk_closed']);
//        }
//
//        // TODO: afegir el 'meta' que correspongui
////        $response['meta'] = $this->getMetaResponse( $pid ); // No cal, ja ha de ser actualitzat
//
//        // TODO: Sí s'afegeix la meta, s'ha d'afegir també els 'revs' perquè s'esborren!
////        $response['revs'] = $this->getRevisions($pid); // No cal, ja ha de ser actualitzat
//
//
//        return $response;
    }

    // TODO[Xavi] normalitzar params
    //CRIDAT Per la comanda save_partial
    public function savePartialEdition($params){
        $action = new SavePartialPageAction($this->persistenceEngine);
        return $action->get($params);
//    public function savePartialEdition(
//        $pid, $prev = NULL, $prange = NULL,
//        $pdate = NULL, $ppre = NULL, $ptext = NULL, $psuf = NULL, $psum = NULL, $selected, $editing_chunks = NULL
//    )
//    {
//        global $lang;
//
////        $response = $this->saveEdition($pid, NULL, $prange, $pdate, $ppre, $ptext, $psuf, $psum);
//        $response = $this->saveEdition(['id' => $pid, 'range' => $prange, 'date' => $pdate, 'pre' => $ppre, 'text' => $ptext, 'suf' => $psuf, 'sum' =>$psum]);
//
//
//        $response['structure'] = $this->getStructuredDocument($selected, $pid, $prev, $editing_chunks);
//
//        // TODO: afegir el 'info' que correspongui
//        if (!$response['info']) {
////            $response['info'] = $this->generateInfo("info", $lang['document_saved']); // TODO[Xavi] Aquesta info s'afegeix en algún lloc, s'ha de moure aquí i fe la localització
//        }
//
//        // TODO: afegir el 'meta' que correspongui
//        $response['meta'] = $this->getMetaResponse($pid);
//
//
//        // TODO: afegir les 'revs' que correspongui
//        $response['revs'] = $this->getRevisions($pid);
//
//        $this->removeStructuredDraft($pid, $selected);
//
//        $this->lock($pid);
//
//        return $response;
    }

//    public function getPartialEdit($pid, $prev = NULL, $psum = NULL, $selected, $editing_chunks, $recoverDraft = null){
    public function getPartialEdit($paramsArr){
        $action = new RawPartialPageAction($this->persistenceEngine);
        return $action->get($paramsArr);
//        global $lang;
//
//        $this->startPageProcess(DW_ACT_SHOW, $pid, NULL, NULL, $psum);
//        $response['structure'] = $this->getStructuredDocument($selected, $pid, null, $editing_chunks, $recoverDraft);
//
//
//        // TODO[Xavi] si es troba una draft per la edició, no es retornarà la resposta edit_html
//        // TODO[Xavi] aquí s'haura d'afegir la comprovació de que no s'ha passat el paràmetre recover draft
//
//        // TODO[Xavi] La diferencia en aquest if es que aquest primer bloc es pel draft parcial
//
//        if ($this->thereIsStructuredDraftFor($pid, $response['structure'], $selected) && $recoverDraft === null) {
//            $response['show_draft_dialog'] = true;
//            $response['content'] = $this->getChunkFromStructureById($response['structure'], $selected);
//            $response['draft'] = $this->getStructuredDraftForHeader($pid, $selected);
//            if ($response['draft']['content'] === $response['content']['editing']) {
//                $this->removeStructuredDraft($pid, $selected);
//                unset($response['draft']);
//                $response['show_draft_dialog'] = false;
//            }
//
//
//            $response['original_call'] = $this->generateOriginalCall($selected, $editing_chunks, $prev, $pid, $psum);
//            $response['info'] = $this->generateInfo('warning', $lang['partial_draft_found']);
//        }
//
//
//        // Trobat draft de document complet
//        if ($this->existsFullDraft($pid)) {
//            $response['original_call'] = $this->generateOriginalCall($selected, $editing_chunks, $prev, $pid, $psum);
//            $response['id'] = $pid;
//            $response['full_draft'] = true;
//            $response['info'] = $this->generateInfo('warning', $lang['draft_found']);
//
//            // Es recupera l'esborrany
//        } else if ($recoverDraft === true) {
//
//            $draftContent = $this->getStructuredDraftForHeader($pid, $selected);
////            $response['structure'] = $this->setContentForChunkByHeader($response['structure'], $selected, $draftContent);
//            $this->setContentForChunkByHeader($response['structure'], $selected, $draftContent);
//            $response['info'] = $this->generateInfo('warning', $lang['draft_editing']);
//
//
//        } else {
//
//
//            $locked = $this->lock($pid);
//
//            if ($locked['timeout'] < 0) {
//                $response['info'] = $locked['info'];
//            } else {
//                $response['info'] = $this->generateInfo('success', $lang['chunk_editing'] . $pid . ':' . $selected);
//            }
//
//        }
//
//
//        // TODO: afegir el 'meta' que correspongui
//
//        // TODO: Sí s'afegeix la meta, s'ha d'afegir també els 'revs' perquè s'esborren!
//
//        return $response;
    }

//    private function generateOriginalCall($selected, $editing_chunks, $prev, $pid, $psum)
//    {
//        $originalCall = [];
//
//        $originalCall['section_id'] = $selected;
//        $originalCall['editing_chunks'] = implode(',', $editing_chunks); // TODO[Xavi] s'ha de convertir en string
//        $originalCall['rev'] = $prev;
//        $originalCall['range'] = '-'; // TODO[Xavi] Això sembla que no es necessari
//        $originalCall['target'] = 'section';
//        $originalCall['id'] = $this->cleanIDForFiles($pid);
//        $originalCall['ns'] = $pid;
//        $originalCall['summary'] = $psum; // TODO[Xavi] Comprovar si es correcte, ha de ser un array
//
//        return $originalCall;
//    }
//
//    private function setContentForChunkByHeader(&$structure, $selected, $content)
//    {
//        for ($i = 0; $i < count($structure['chunks']); $i++) {
//            if ($structure['chunks'][$i]['header_id'] == $selected) {
//                $structure['chunks'][$i]['text']['editing'] = $content['content'];
//                break;
//            }
//        }
//        return $structure;
//    }
//
//
//    private function getChunkFromStructureById($structure, $selected)
//    {
//        $chunks = $structure['chunks'];
//        foreach ($chunks as $chunk) {
//            if ($chunk['header_id'] == $selected) {
//                return $chunk['text'];
//            }
//        }
//        return null;
//    }
//

    //És la crida principal de la comanda lock
    public function lock($pid)
    {
        global $lang,
               $conf;

        $ns = $pid;
        $cid = $this->getContainerIdFromPageId($pid);
        $lockManager = new LockManager($this);
        $locker = $lockManager->lock($pid);

        if ($locker === false) {

            $info = $this->generateInfo('info', "S'ha refrescat el bloqueig"); // TODO[Xavi] Localitzar el missatge
            $response = ['id' => $cid, 'ns' => $ns, 'timeout' => $conf['locktime'], 'info' => $info];

        } else {

            $response = ['id' => $cid, 'ns' => $ns, 'timeout' => -1, 'info' => $this->generateInfo('error', $lang['lockedby'] . ' ' . $locker)];
        }

        return $response;
    }

    public function unlock($pid)
    {
        $lockManager = new LockManager($this);

        $ns = $pid;
        $cid = $this->getContainerIdFromPageId($pid);

        $lockManager->unlock($pid);

        $info = $this->generateInfo('success', "S'ha alliberat el bloqueig");
        $response = ['id' => $cid, 'ns' => $ns, 'timeout' => -1, 'info' => $info]; // TODO[Xavi] Localitzar el missatge

        return $response;
    }

//    public function checklock($pid)
//    {
//        //[ALERTA JOSEP] Cal passar checklock a LockDataQuery i fer la crida des d'allà
//        return checklock($this->cleanIDForFiles($pid));
//    }

    public function draft($params)
    {
        $action = new DraftPageAction($this->persistenceEngine);
        $ret = $action->get($params);
        return $ret;
    }

    public function saveDraft($draft)
    {
        return DraftManager::saveDraft($draft);
    }

    public function removeDraft($draft)
    {
        return DraftManager::removeDraft($draft);
    }

//    public function getStructuredDraft($id)
//    {
//        return DraftManager::getStructuredDraft($id);
//    }

//    public function removeStructuredDraft($id, $header_id)
//    {
//        DraftManager::removeStructuredDraft($id, $header_id);
//    }

//    /**
//     * Retorna cert si existeix un draft o fals en cas contrari. Si es troba un draft però es més antic que el document
//     * corresponent aquest draft s'esborra.
//     *
//     * @param {string} $id id del document a comprovar
//     * @return bool
//     */
//    public function existsFullDraft($id)
//    {
//        return DraftManager::existsFullDraft($id);
//    }

//    public function existsPartialDraft($id)
//    {
//        return DraftManager::existsPartialDraft($id);
//    }

//    public function clearFullDraft($id)
//    {
//        global $ACT, $ID;
//
//        $ID = $id;
//
//        WikiIocInfoManager::setInfo('draft', $this->getDraftFilename($id));
//        $ACT = act_draftdel($ACT);
//
//    }

//    public function clearPartialDraft($id)
//    {
//        DraftManager::removeStructuredDraftAll($id);
//    }

//    public function getStructuredDraftForHeader($id, $header)
//    {
//        return DraftManager::getStructuredDraftForHeader($id, $header);
//    }

//    /**
//     * Retorna el contingut del esborrany pel document passat com argument si existeix i es vàlid. En cas de trobar
//     * un esborrany antic es esborrat automàticament.
//     *
//     * @param string $id - id del document
//     *
//     * @return array - Hash amb dos valors per el contingut i la data respectivament.
//     */
//    public function generateFullDraft($id)
//    {
//        return DraftManager::generateFullDraft($id);
//    }

    public function logoff(){
        auth_logoff(TRUE);
        WikiIocInfoManager::setInfo('isadmin', FALSE);
        WikiIocInfoManager::setInfo('ismanager', FALSE);
    }

//    /**
//     * Mostra una pàgina de la DokuWiki.
//     * TODO[Xavi] no es fa res amb l'argument
//     *
//     * Based on "html_show" function written by Andreas Gohr
//     *
//     * @param string $data
//     */
//    function onFormatRender($data)
//    {
//        if ($this->params['rev']) {
//            $secedit = false;
//        } else {
//            $secedit = true;
//        }
//
//        //	html_show();
//        //if ($REV) print p_locale_xhtml('showrev');
//        $html = p_wiki_xhtml($this->params['id'],
//            $this->params['rev'],
//            true);
//        $html = html_secedit($html, $secedit);
//        //if($INFO['prependTOC']) $html = tpl_toc(true).$html;
//        $html = html_hilight($html, $HIGH);
//        echo $html;
//    }

//    /**
//     * Returns the parsed Wikitext in XHTML for the given id and revision.
//     *
//     * If $excuse is true an explanation is returned if the file
//     * wasn't found
//     *
//     * @author Andreas Gohr <andi@splitbrain.org>
//     */
//
//    //[ALERTA Josep] CAL revisar per fer servir el PageDataQuery!
//    // TODO[Xavi] Convertida en static (temporalment?) necessaria per reconstruir els drafts a partir de parcials
//    static function p_wiki_xhtml($id, $rev = '', $excuse = true)
//    {
//        $file = wikiFN($id, $rev);
//        $ret = '';
//
//        //ensure $id is in global $ID (needed for parsing)
//        global $ID;
//        $keep = $ID;
//        $ID = $id;
//
//        if ($rev) {
//            if (@file_exists($file)) {
//                $ret = p_render('xhtml', p_get_instructions(io_readWikiPage($file, $id, $rev)), $info); //no caching on old revisions
//            } elseif ($excuse) {
//                $ret = p_locale_xhtml('norev');
//            }
//        } else {
//            if (@file_exists($file)) {
//                $ret = p_cached_output($file, 'xhtml', $id);
//            } elseif ($excuse) {
//                $ret = p_locale_xhtml('newpage');
//            }
//        }
//
//        //restore ID (just in case)
//        $ID = $keep;
//
//        return $ret;
//    }
//


//    /**
//     * Segons el valor de $data activa la edició del document('edit' i 'recover'), la previsualització ('preview') o mostra
//     * el missatge de denegat ('denied').
//     *
//     * @param string $data els valors admessos son 'edit', 'recover', 'preview' i 'denied'
//     */
//    function onCodeRender( $data ) {
//            global $TEXT;
//
//            switch ( $data ) {
//                    case 'locked':
//                    case 'edit':
//                    case 'recover':
//                            html_edit();
//                            break;
//                    case 'preview':
//                            html_edit();
//                            html_show( $TEXT );
//                            break;
//                    case 'denied':
//                            print p_locale_xhtml( 'denied' );
//                            break;
//            }
//    }


    /**
     * Retorna la taula de continguts modificada amb la nostra cadena.
     *
     * @return string taula de continguts
     */
    function wrapper_tpl_toc()
    {
        //[ALERTA JOSEP] analitzar si cal passar tpl_toc a PageDataQuery
        $toc = tpl_toc(TRUE);
        $toc = preg_replace(
            '/(<!-- TOC START -->\s?)(.*\s?)(<div class=.*tocheader.*<\/div>|<h3 class=.*toggle.*<\/h3>)((.*\s)*)(<!-- TOC END -->)/i',
            '$1<div class="dokuwiki">$2$4</div>$6', $toc
        );

        return $toc;
    }

    // ALERTA[Xavi] Afegit pel notifier
    public function notify($params) // Alerta[Xavi] Canviar per getEdit per fer-lo consistent amb getEditPartial?
    {
        $action = new NotifyAction($this->persistenceEngine);
        $contentData = $action->get($params);
        return $contentData;
    }
}

