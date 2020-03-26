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
require_once(DOKU_INC . 'inc/JSON.php');
require_once(DOKU_INC . 'inc/JpegMeta.php');

if (!defined('DOKU_TPL_INCDIR')) define('DOKU_TPL_INCDIR', WikiGlobalConfig::tplIncDir());
if (!defined('DOKU_LIB_IOC')) define('DOKU_LIB_IOC', DOKU_INC.'lib/lib_ioc/');
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');

require_once(DOKU_LIB_IOC . "wikiiocmodel/DefaultProjectModelExceptions.php");
require_once(DOKU_LIB_IOC . 'wikiiocmodel/PagePermissionManager.php');

require_once(DOKU_PLUGIN . 'wikiiocmodel/BasicModelAdapter.php');

require_once(DOKU_PLUGIN . 'acl/admin.php');

// TODO[Xavi] Afegit per mi per extreure la funcionalitat dels locks a una altra classe
require_once(DOKU_PLUGIN . 'wikiiocmodel/LockManager.php');
require_once(DOKU_PLUGIN . 'wikiiocmodel/DraftManager.php');
require_once(DOKU_PLUGIN . 'wikiiocmodel/actions/NotifyAction.php');

require_once(DOKU_PLUGIN . 'ajaxcommand/defkeys/PageKeys.php');

/**
 * Class DokuModelAdapter
 * Adaptador per passar les nostres comandes a la Dokuwiki.
 */
class DokuModelAdapter extends BasicModelAdapter {
    const ADMIN_PERMISSION = "admin";

    protected $params;
    protected $dataTmp;
    protected $ppEvt;

    /**
     * És la crida principal de la comanda copy_image_to_project
     * @param string $nsTarget, $idTarget, $filePathSource
     * @param bool $overWrite
     *
     * @return int
     */
    public function saveImage($nsTarget, $idTarget, $filePathSource, $overWrite=FALSE) {
        /* MediaDataQuery*/
        $dataQuery = $this->persistenceEngine->createMediaDataQuery();
        return $dataQuery->copyImage($nsTarget, $idTarget, $filePathSource, $overWrite);
  }

    /**
     * És la crida principal de la comanda get_image_detail. Obté un html
     * amb el detall d'una imatge.
     * @return string
     * @throws HttpErrorCodeException
     */
    public function getImageDetail($imageId, $fromPage = NULL)
    {
        global $lang;
        //[TODO Josep] Normalitzar: start do get ...

        $error = $this->startMediaProcess(PageKeys::DW_ACT_MEDIA_DETAIL, $imageId, $fromPage);
        if ($error == 401) {
            throw new HttpErrorCodeException("Access denied", $error);
        } else if ($error == 404) {
            throw new HttpErrorCodeException("Resource " . $imageId . " not found.", $error);
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
     * MOGUT a NsTreeAction
     *****************************************
     * És la crida principal de la comanda ns_tree_rest
     *//*
    public function getNsTree($currentnode, $sortBy, $onlyDirs=FALSE, $expandProject=FALSE, $hiddenProjects=FALSE, $fromRoot=FALSE)
    {
        $dataQuery = $this->persistenceEngine->createPageDataQuery();
        if($fromRoot){
            $root=$fromRoot;
        }
        return $dataQuery->getNsTree($currentnode, $sortBy, $onlyDirs, $expandProject, $hiddenProjects, $root);
    }*/

    /**
     * Obté el missatge traduit a l'idioma actual.
     * @global type $lang
     * @param type $id
     * @return type
     *//*
    public function getGlobalMessage($id)
    {
        return WikiIocLangManager::getLang($id);
    }*/


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
        if ($pdo === PageKeys::DW_ACT_MEDIA_DETAIL) {
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
            $this->params['rev'] = (int)WikiIocInfoManager::getInfo("lastmod");
        }

        $this->triggerStartEvents();

        return $ret;
    }

    private function triggerStartEvents() {
        trigger_event('WIOC_AJAX_COMMAND_STARTED', $this->dataTmp);
    }

    private function triggerEndEvents() {
        $tmp = array(); //NO DATA
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
        include DOKU_TPL_INCDIR . "lang/en/lang.php";
        //overwrite English language values with available translations
        if (!empty($conf["lang"]) &&
            $conf["lang"] !== "en" &&
            file_exists(DOKU_TPL_INCDIR . "/lang/" . $conf["lang"] . "/lang.php")
        ) {
            //get language file (partially translated language files are no problem
            //cause non translated stuff is still existing as English array value)
            include DOKU_TPL_INCDIR . "/lang/" . $conf["lang"] . "/lang.php";
        }
        if (!empty($conf["lang"]) &&
            $conf["lang"] !== "en" &&
            file_exists(DOKU_PLUGIN . "wikiiocmodel/lang/" . $conf["lang"] . "/lang.php")
        ) {
            include DOKU_PLUGIN . "wikiiocmodel/lang/" . $conf["lang"] . "/lang.php";
        }
        $this->langStartedUp = true;
    }

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
        include DOKU_TPL_INCDIR . "inc_detail.php";
        $content = ob_get_clean();
        return $content;
    }

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
    public function generateInfo($type, $message, $id = NULL, $duration = -1) {
        if ($id === NULL) {
            $id = str_replace(":", "_", $this->params['id']);
        }
        return IocCommon::generateInfo($type, $message, $id, $duration);
    }

    public function addInfoToInfo($infoA, $infoB) {
        return IocCommon::addInfoToInfo($infoA, $infoB);
    }

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
            $response['info'] = self::generateInfo('error', $lang['lockedby'] . ' '
                . WikiIocInfoManager::getInfo('locked'));
        } else {
            $response['show_draft_dialog'] = TRUE;
        }


        return $response;
    }


    /**
     * Neteja una id passada per argument per poder fer-la servir amb els fitxers i si no es passa l'argument
     * intenta obtenir-la dels paràmetres.
     * @param string $id - id a netejar
     * @return mixed
     */
    private function getContainerIdFromPageId($id = NULL) {
        if ($id == NULL) {
            $id = $this->params['id'];
        }
        return WikiPageSystemManager::getContainerIdFromPageId($id);
    }

    private function getCommonPage($id, $title, $content)
    {
        $contentData = array(
            'id' => $id,
            'title' => $title,
            'content' => $content
        );

        return $contentData;
    }

    /**
     * és la crida principal de la comanda media
     * Miguel Angel Lozano 12/12/2014
     */
    public function getMediaManager($image=NULL, $fromPage=NULL, $prev=NULL) {
        //[TODO Josep] Normalitzar: start do get ...
        global $lang, $NS, $INPUT, $JSINFO;

        $error = $this->startMediaManager(PageKeys::DW_ACT_MEDIA_MANAGER, $image, $fromPage, $prev);
        if ($error == 401) {
            throw new HttpErrorCodeException("Access denied", $error);
        } else if ($error == 404) {
            throw new HttpErrorCodeException("Resource " . $image . " not found.", $error);
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
    private function startDeleteMediaManager($pImage = NULL, $pFromId = NULL, $prev = NULL)
    {
        global $DEL;

        $DEL  = $this->params['delete'] = $pImage;

        $ret = $this->startMediaManager($pdo, $pImage, $pFromId, $prev);
         if ($pImage) {
            if ($AUTH < AUTH_DELETE) {
                // no auth
                $ret = $ERROR = 401;
            }
        }
        return $ret;
    }

    private function startMediaManager($pdo, $pImage = NULL, $pFromId = NULL, $prev = NULL)
    {
        global $ID;
        global $AUTH;
        global $vector_action;
        global $IMG;
        global $ERROR;
        global $SRC;
        global $REV;

        $ret = $ERROR = 0;

        $this->params['action'] = $pdo;

        if ($pdo === PageKeys::DW_ACT_MEDIA_MANAGER) {
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

        $this->startUpLang();

        //detect revision
        $REV = $this->params['rev'] = (int)WikiIocInfoManager::getInfo("rev"); //$INFO comes from the DokuWiki core
        if ($this->params['rev'] < 1) {
            $REV = $this->params['rev'] = (int)WikiIocInfoManager::getInfo("lastmod");
        }

        $this->triggerStartEvents();

        return $ret;
    }

    //[Rafa] NO SE UTILIZA
//    private function doDeleteMediaManagerPreProcess(){
//        global $DEL;
//
//        $content = "";
//        if ($this->runBeforePreprocess($content)) {
//            $res = 0;
//            if(checkSecurityToken()) {
//                $res = media_delete($DEL,$AUTH);
//            }
//        }
//        $this->runAfterPreprocess($content);
//        return $res;
//    }

    private function doMediaManagerPreProcess() {
        global $ACT;

        $content = "";
        if ($this->runBeforePreprocess($content)) {
            ob_start();
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
    public function getNsMediaTree($currentnode, $sortBy, $onlyDirs=FALSE, $expandProject=FALSE, $hiddenProjects=FALSE)
    {
        $dataQuery = $this->persistenceEngine->createMediaDataQuery();
        return $dataQuery->getNsTree($currentnode, $sortBy, $onlyDirs, $expandProject, $hiddenProjects);
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




    /**
     * Extreu la data a partir del nombre de revisió
     *
     * @param int $revision - nombre de la revisió
     * @param int $mode - format de la data
     *
     * @return string - Data formatada
     *
     */
    public function extractDateFromRevision($revision, $mode = NULL) {
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
        session_write_close();

        $content = "";
        if ($this->runBeforePreprocess($content)) {
            act_permcheck($ACT);
            unlock($ID);
        }

        $this->startUpLang();

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
            'id' =>  \str_replace(":", "_", $ID) . '_diff',
            'ns' => $ID,
            'title' => $ID,
            'content' => $this->clearDiff($content),
            'type' => 'diff',
            'rev1' => $rev1
        ];

        if ($rev2) {
            $response['rev1'] = $rev2[0];
            $response['rev2'] = $rev2[1];
        }

        $response['info'] = self::generateInfo("info", $lang['diff_loaded'], $response['id']);

        $meta = [
            ($this->getCommonPage($response['id'] . '_switch_diff_mode ',
                    $lang['switch_diff_mode'],
                    $this->extractMetaContentFromDiff($content)
                ) + ['type' => 'switch_diff_mode'])
        ];

        $response["meta"] = ['id' => $response['id'], 'meta' => $meta];

        $this->triggerEndEvents();

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
     * MEDIA DETAILS: Obtenció dels detalls de un media. És la crida principal de la comanda mediadetails
     */
    public function getMediaDetails($image) {
        //[TODO Josep] Normalitzar: start do get ...
        global $NS, $JSINFO, $MSG, $INPUT;

        $error = $this->startMediaDetails(PageKeys::DW_ACT_MEDIA_DETAILS, $image);
        if ($error == 401) {
            throw new HttpErrorCodeException("Access denied", $error);
        } else if ($error == 404) {
            throw new HttpErrorCodeException("Resource " . $image . " not found.", $error);
        }

        $mdpp = $this->doMediaDetailsPreProcess();
        if ($mdpp['error']) {
            throw new UnknownMimeTypeException();
        }
        if ($mdpp['newImage']) {
            $image = $mdpp['newImage'];
        }
        $ret = array(
            "content" => $mdpp['content'],   //[ALERTA Josep] Pot venir amb un fragment de HTML i caldria veure què es fa amb ell.
            "id" => $image,
            "title" => $image,
            "ns" => $NS,
            "imageTitle" => $image,
            "image" => $image,
            "newImage" => ($mdpp['newImage']) ? TRUE : NULL
        );
        $do = $INPUT->str('mediado');
        if ($do === 'diff') {
            $ret["mediado"] = $do;
        }
        if ($MSG[0] && $MSG[0]['lvl'] == 'error') {
            throw new HttpErrorCodeException($MSG[0]['msg'], 404);
        }
        $JSINFO = array('id' => $image, 'namespace' => $NS);

        return $ret;
    }

    /**
     * Omple alguns valors de $this->params
     * Retorna l'ERROR de permisos de la imatge
     */
    private function startMediaDetails($pdo, $pImage) {
        global $ID, $AUTH, $IMG, $ERROR, $SRC, $REV, $INPUT;

        $ret = $ERROR = 0;
        $this->params['action'] = $pdo;
        $ID = $pImage;

        if ($pImage) {
            $IMG = $this->params['image'] = $pImage;
            $AUTH = auth_quickaclcheck($pImage);
            if ($AUTH >= AUTH_READ) {
                $SRC = mediaFN($pImage);
                if (!file_exists($SRC)) {
                    $ret = $ERROR = 404;
                }
            } else {
                $ret = $ERROR = 401;
            }
        }

        if(!$this->params['ns'] && !$this->params['img']){
            $INPUT->set('img', $IMG);
        }

        if ($ret != 0) {
            return $ret;
        }

        WikiIocInfoManager::loadMediaInfo();
        $this->startUpLang();

        //detect revision
        $REV = $this->params['rev'] = (int)WikiIocInfoManager::getInfo("rev"); //$INFO comes from the DokuWiki core

        $this->triggerStartEvents();
        return $ret;
    }

    private function doMediaDetailsPreProcess() {
        global $ACT;

        $content = "";
        if ($this->runBeforePreprocess($content)) {
            ob_start();
            $ret = $this->mediaDetailsContent();
            $ret['content'] = $content . $ret['content'];
            // check permissions again - the action may have changed
            $ACT = act_permcheck($ACT);
        }
        $this->runAfterPreprocess($ret['content']);
        return $ret;
    }

    /**
     * Prints full-screen media details
     */
    function mediaDetailsContent() {
        global $NS, $IMG, $JUMPTO, $REV, $lang, $conf, $fullscreen, $INPUT, $AUTH, $MSG;
        $fullscreen = TRUE;
        require_once DOKU_INC . 'lib/exe/mediamanager.php';

        $rev = '';
        $image = cleanID($INPUT->str('image'));
        if (isset($IMG)) {
            $image = $IMG;
        }
        if (isset($JUMPTO)) {
            if ($JUMPTO === false) {
                $ret['error'] = "UnknownMimeType";
                return $ret;
            }elseif ($JUMPTO != $image) {
                //éste es el caso de un nuevo fichero con un nuevo nombre, cuando se hace upload en una página mediadetails
                $ret['newImage'] = $JUMPTO;
                $image = $JUMPTO;
            }
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
            media_details($image, $AUTH, $rev, $meta);
            echo '</div>' . NL;

            if ($_REQUEST['tab_details']) {
                if (!$size) {
                    throw new HttpErrorCodeException("No es poden editar les dades d'aquest element", -1);//JOSEP: Alerta! Excepció incorrecta, cal buscar o crear una execpció adient!
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
        $ret['content'] = $content;
        return $ret;
    }

    /**
     * Omple la pestanya històric de la zona de metadades del mediadetails
     */
    function mediaDetailsHistory($ns, $image) {
        global $NS, $IMG, $INPUT;
        $NS = $ns;
        $IMG = $image;

        ob_start();
        $first = $INPUT->int('first');
        html_revisions($first, $image);
        $content = ob_get_clean();

        // Substitució de l'id del form per fer-ho variable
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
        $instructions = p_cached_instructions($file, FALSE, $id);
        return $instructions;
    }

    // TODO[Xavi] PER SUBISTIUIR PEL PLUGIN DEL RENDER
    private static function getHtmlForDocument($id, $rev = null)
    {
        $html = p_wiki_xhtml($id, $rev, true);

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

            $info = self::generateInfo('info', "S'ha refrescat el bloqueig"); // TODO[Xavi] Localitzar el missatge
            $response = ['id' => $cid, 'ns' => $ns, 'timeout' => $conf['locktime'], 'info' => $info];

        } else {

            $response = ['id' => $cid, 'ns' => $ns, 'timeout' => -1, 'info' => self::generateInfo('error', $lang['lockedby'] . ' ' . $locker)];
        }

        return $response;
    }

    public function unlock($pid)
    {
        $lockManager = new LockManager($this);

        $ns = $pid;
        $cid = $this->getContainerIdFromPageId($pid);

        $lockManager->unlock($pid);

        $info = self::generateInfo('success', "S'ha alliberat el bloqueig");
        $response = ['id' => $cid, 'ns' => $ns, 'timeout' => -1, 'info' => $info]; // TODO[Xavi] Localitzar el missatge

        return $response;
    }

    /** SEMBLA SER QUE AQUESTA FUNCIÓ NO S'UTILITZA
     * S'ha de fer servir getRevisionsList en lloc d'aquest
     * @deprecated
     * @param $id
     * @return array
     */
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

    public function logoff(){
        auth_logoff(TRUE);
        WikiIocInfoManager::setInfo('isadmin', FALSE);
        WikiIocInfoManager::setInfo('ismanager', FALSE);
    }

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

    /**
     * MOGUT a: login_command i notify_command
     *****************************************
    // ALERTA[Xavi] $secure : si és true s'ha cridat des d'un client d'admin
    public function notify($params, $isAdmin = false) // Alerta[Xavi] Canviar per getEdit per fer-lo consistent amb getEditPartial?
    {
        $action = new NotifyAction($this->persistenceEngine, $isAdmin);
        $contentData = $action->get($params);
        return $contentData;
    }*/
}

