<?php

/**
 * Description of DokuModelAdapter
 *
 * @author Josep Cañellas <jcanell4@ioc.cat>
 */
if (!defined('DOKU_INC'))
    die();
//require common
require_once DOKU_INC . 'inc/actions.php';
require_once DOKU_INC . 'inc/pageutils.php';
require_once DOKU_INC . 'inc/common.php';
require_once DOKU_INC . 'inc/media.php';
require_once DOKU_INC . 'inc/auth.php';
require_once DOKU_INC . 'inc/confutils.php';
require_once DOKU_INC . 'inc/io.php';
require_once DOKU_INC . 'inc/auth.php';
require_once DOKU_INC . 'inc/template.php';

if (!defined('DOKU_PLUGIN'))
    define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
require_once(DOKU_PLUGIN . 'wikiiocmodel/WikiIocModel.php');
require_once(DOKU_PLUGIN . 'wikiiocmodel/WikiIocModelExceptions.php');

if (!defined('DW_DEFAULT_PAGE'))
    define('DW_DEFAULT_PAGE', "start");
if (!defined('DW_ACT_SHOW'))
    define('DW_ACT_SHOW', "show");
if (!defined('DW_ACT_DRAFTDEL'))
    define('DW_ACT_DRAFTDEL', "draftdel");
if (!defined('DW_ACT_SAVE'))
    define('DW_ACT_SAVE', "save");
if (!defined('DW_ACT_EDIT'))
    define('DW_ACT_EDIT', "edit");
if (!defined('DW_ACT_PREVIEW'))
    define('DW_ACT_PREVIEW', "preview");
if (!defined('DW_ACT_RECOVER'))
    define('DW_ACT_RECOVER', "recover");
if (!defined('DW_ACT_DENIED'))
    define('DW_ACT_DENIED', "denied");
if (!defined('DW_ACT_MEDIA_DETAIL'))
    define('DW_ACT_MEDIA_DETAIL', "media_detail");
if (!defined('DW_ACT_MEDIA_MANAGER'))
    define('DW_ACT_MEDIA_MANAGER', "media");
if(!defined('DW_ACT_EXPORT_ADMIN'))
    define('DW_ACT_EXPORT_ADMIN', "admin");

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
 * Mostra una pàgina de la DokuWiki.
 *
 * TODO[Xavi] no es crida en lloc i no es fa servir el argument per  res.
 *
 * @param string $data
 */
function onFormatRender($data) {
    html_show();
}

/**
 * Segons el valor de $data activa la edició del document('edit' i 'recover'), la previsualització ('preview') o mostra
 * el missatge de denegat ('denied').
 *
 * @param string $data els valors admessos son 'edit', 'recover', 'preview' i 'denied'
 */
function onCodeRender($data) {
    global $TEXT;

    switch ($data) {
        case 'edit':
        case 'recover':
            html_edit();
            break;
        case 'preview':
            html_edit();
            html_show($TEXT);
            break;
        case 'denied':
            print p_locale_xhtml('denied');
            break;
    }
}

/**
 * Retorna la taula de continguts modificada amb la nostra cadena.
 *
 * @return string taula de continguts
 */
function wrapper_tpl_toc() {
    $toc = tpl_toc(TRUE);
    $toc = preg_replace(
        '/(<!-- TOC START -->\s?)(.*\s?)(<div class=.*tocheader.*<\/div>|<h3 class=.*toggle.*<\/h3>)((.*\s)*)(<!-- TOC END -->)/i',
        '$1<div class="dokuwiki">$2$4</div>$6', $toc
    );
    return $toc;
}

/**
 * Class DokuModelAdapter
 * Adaptador per passar les nostres comandes a la Dokuwiki.
 *
 * @author Josep Cañellas <jcanell4@ioc.cat>
 */
class DokuModelAdapter implements WikiIocModel {
    const ADMIN_PERMISSION = "admin";

    protected $params;
    protected $dataTmp;
    protected $ppEvt;
    
    public function getAdminTask($ptask){
        
        $this->startAdminTaskProcess($ptask);        
        $this->doAdminTaskPreProcess();        
        $response = $this->getAdminTaskResponse();           
        $response['info'] = $this->generateInfo("info", $lang['admin_task_loaded']);
        return $response;
    }

    public function getAdminTaskList(){
        
        $this->startAdminTaskProcess();        
        $this->doAdminTaskListPreProcess();        
        return $this->getAdminTaskListResponse();        
    }

    public function createPage($pid, $text = NULL) {
        global $INFO;
        global $lang;

        $this->startUpLang();

        if (!$text) {
            $text = $lang['createDefaultText'];
        }

        $this->startPageProcess(
             DW_ACT_SAVE, $pid, NULL, NULL, $lang['created'], NULL,
             "", $text, ""
        );
        if($INFO["exists"]){
            throw new PageAlreadyExistsException($pid,$lang['pageExists']);
        }
        $this->doSavePreProcess();
        return $this->getFormatedPageResponse();
    }

    public function getHtmlPage($pid, $prev = NULL) {
        global $INFO;
        global $lang;
        $this->startPageProcess(DW_ACT_SHOW, $pid, $prev);
        if(!$INFO["exists"]){
            throw new PageNotFoundException($pid,$lang['pageNotFound']);
        }
        $this->doFormatedPagePreProcess();

        $response = $this->getFormatedPageResponse();
        $response['info'] = $this->generateInfo("info", $lang['document_loaded']);
        return $response;
    }

    public function getCodePage($pid, $prev = NULL, $prange = NULL, $psum = NULL) {
        global $INFO;
        global $lang;        
        $this->startPageProcess(DW_ACT_EDIT, $pid, $prev, $prange, $psum);
        if(!$INFO["exists"]){
            throw new PageNotFoundException($pid,$lang['pageNotFound']);
        }
        $this->doEditPagePreProcess();
        return $this->getCodePageResponse();
    }

    public function cancelEdition($pid, $prev = NULL) {
        global $lang;

        $this->startPageProcess(DW_ACT_DRAFTDEL, $pid, $prev);
        $this->doCancelEditPreprocess();

        $response = $this->getFormatedPageResponse();
        $response ['info'] = $this->generateInfo("info", $lang['edition_cancelled']);

        return $response;
    }

    public function saveEdition($pid, $prev = NULL, $prange = NULL,
        $pdate = NULL, $ppre = NULL, $ptext = NULL, $psuf = NULL, $psum = NULL) {
        $this->startPageProcess(
             DW_ACT_SAVE, $pid, $prev, $prange, $psum, $pdate,
             $ppre, $ptext, $psuf
        );
        $code = $this->doSavePreProcess();  
        return $this->getSaveInfoResponse($code);
    }

    public function isDenied() {
        global $ACT;
        $this->params['do'] = $ACT;
        return $this->params['do'] == DW_ACT_DENIED;
    }

    public function getMediaFileName($id, $rev = '') {
        return mediaFN($id, $rev);
    }

    public function getIdWithoutNs($id) {
        return noNS($id);
    }

    public function getMediaList($ns) {
        $dir = $this->getMediaFileName($ns);
        $arrayDir = scandir($dir);
        if ($arrayDir) {
            unset($arrayDir[0]);
            unset($arrayDir[1]);
            $arrayDir = array_values($arrayDir);
        } else {
            $arrayDir = array();
        }
        return $arrayDir;
    }

    public function imagePathToId($path) {
        global $conf;
        if ($this->starsWith($path, "/")) { //absolute path
            $path = str_replace($conf['mediadir'] . "/", "", $path);
        }
        $id = str_replace('/', ':', $path);
        return $id;
    }

    public function getPageFileName($id, $rev = '') {
        return wikiFN($id, $rev);
    }

    /**
     * @param string $image //abans era $id. $id no s'utilitzava
     * @param bool   $rev
     * @param bool   $meta
     *
     * @return string
     */
    public function getMediaUrl($image, $rev = FALSE, $meta = FALSE) {
        $size = media_image_preview_size($image, $rev, $meta);
        if ($size) {
            $more = array();
            if ($rev) {
                $more['rev'] = $rev;
            } else {
                $t = @filemtime(mediaFN($image));
                $more['t'] = $t;
            }
            $more['w'] = $size[0];
            $more['h'] = $size[1];
            $src = ml($image, $more);
        } else {
            $src = ml($image, "", TRUE);
        }
        return $src;
    }

    /**
     * @param string $nsTarget
     * @param string $idTarget
     * @param string $filePathSource
     * @param bool   $overWrite
     *
     * @return int
     */
    public function uploadImage($nsTarget, $idTarget, $filePathSource, $overWrite = FALSE) {
        return $this->_saveImage(
                        $nsTarget, $idTarget, $filePathSource
                        , $overWrite, "move_uploaded_file"
        );
    }

    /**
     * @param string $nsTarget
     * @param string $idTarget
     * @param string $filePathSource
     * @param bool   $overWrite
     *
     * @return int
     */
    public function saveImage($nsTarget, $idTarget, $filePathSource, $overWrite = FALSE) {
        return $this->_saveImage(
                        $nsTarget, $idTarget, $filePathSource
                        , $overWrite, "copy"
        );
    }

    public function getImageDetail($imageId, $fromPage = NULL) {
        global $lang;

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

    public function getNsTree($currentnode, $sortBy, $onlyDirs = FALSE) {
        global $conf;
        $sortOptions = array(0 => 'name', 'date');
        $nodeData = array();
        $children = array();

        if ($currentnode == "_") {
            return array('id' => "", 'name' => "", 'type' => 'd');
        }
        if ($currentnode) {
            $node = $currentnode;
            $aname = split(":", $currentnode);
            $level = count($aname);
            $name = $aname[$level - 1];
        } else {
            $node = '';
            $name = '';
            $level = 0;
        }
        $sort = $sortOptions[$sortBy];
        $base = $conf['datadir'];

        $opts = array('ns' => $node);
        $dir = str_replace(':', '/', $node);
        search(
            $nodeData, $base, 'search_index',
            $opts, $dir, 1
        );
        foreach (array_keys($nodeData) as $item) {
            if ($onlyDirs && $nodeData[$item]['type'] == 'd' || !$onlyDirs) {
                $children[$item]['id'] = $nodeData[$item]['id'];
                $aname = split(":", $nodeData[$item]['id']); //TODO[Xavi] @deprecated substitur per explode()
                $children[$item]['name'] = $aname[$level];
                $children[$item]['type'] = $nodeData[$item]['type'];
            }
        }

        $tree = array(
            'id' => $node, 'name' => $node,
            'type' => 'd', 'children' => $children
        );
        return $tree;
    }

    public function getGlobalMessage($id) {
        global $lang;
        return $lang[$id];
    }

    /**
     * Crea el directori on ubicar el fitxer referenciat per $filePath després 
     * d'extreure'n el nom del fitxer. Aquesta funció no crea directoris recursivamnent.
     *
     * @param type $filePath
     */
    public function makeFileDir($filePath) {
        io_makeFileDir($filePath);
    }

    public function tplIncDir() {
        global $conf;
        if (is_callable('tpl_incdir')) {
            $ret = tpl_incdir();
        } else {
            $ret = DOKU_INC . 'lib/tpl/' . $conf['template'] . '/';
        }
        return $ret;
    }

    /**
     * Retorna si s'ha trobat la cadena que es cerca al principi de la cadena on es busca.
     *
     * @param string $haystack cadena on buscar
     * @param string $needle   cadena per buscar
     *
     * @return bool true si la cadena comença com la cadena passada per argument o la cadena a buscar es buida, i false
     * en cas contrari
     */
    private function starsWith($haystack, $needle) {
        return $needle === "" || strpos($haystack, $needle) === 0;
    }

    /**
     * Retorna si s'ha trobat la cadena que es cerca al final de la cadena on es busca.
     *
     * @param string $haystack cadena on buscar
     * @param string $needle   cadena per buscar
     *
     * @return bool true si la cadena acaba com la cadena passada per argument o la cadena a buscar es buida, i false
     * en cas contrari
     */
    private function endsWith($haystack, $needle) {
        return $needle === "" || substr($haystack, -strlen($needle)) === $needle;
    }

    /**
     * @param string   $nsTarget
     * @param string   $idTarget
     * @param string   $filePathSource
     * @param boolean  $overWrite
     * @param callable $copyFunction funció que es cridarà per moure el fitxer de la ruta tempora a la ruta final.
     *                               Aquesta funciò ha de rebre com a paràmetres dos strings, el primer amb el nom del
     *                               fitxer temporal i el segon amb el nom del fitxer final
     *
     * @return int enter corresponent a un dels següents codis:
     *       0 = OK
     *      -1 = UNAUTHORIZED
     *      -2 = OVER_WRITING_NOT_ALLOWED
     *      -3 = OVER_WRITING_UNAUTHORIZED
     *      -5 = FAILS
     *      -4 = WRONG_PARAMS
     *      -6 = BAD_CONTENT
     *      -7 = SPAM_CONTENT
     *      -8 = XSS_CONTENT
     */
    private function _saveImage($nsTarget, $idTarget, $filePathSource, $overWrite
    , $copyFunction) {
        global $conf;
        $res = NULL; //(0=OK, -1=UNAUTHORIZED, -2=OVER_WRITING_NOT_ALLOWED,
        //-3=OVER_WRITING_UNAUTHORIZED, -5=FAILS, -4=WRONG_PARAMS
        //-6=BAD_CONTENT, -7=SPAM_CONTENT, -8=XSS_CONTENT)
        $auth = auth_quickaclcheck(getNS($idTarget) . ":*");

        if ($auth >= AUTH_UPLOAD) {
            io_createNamespace("$nsTarget:xxx", 'media');
            list($ext, $mime, $dl) = mimetype($idTarget);
            $res_media = media_save(
                array(
                    'name' => $filePathSource,
                    'mime' => $mime,
                    'ext'  => $ext
                ),
                $nsTarget . ':' . $idTarget,
                $overWrite,
                $auth,
                $copyFunction
            );

            if (is_array($res_media)) {
                if ($res_media[1] == 0) {
                    if ($auth < (($conf['mediarevisions']) ? AUTH_UPLOAD : AUTH_DELETE)) {
                        $res = -3;
                    } else {
                        $res = -2;
                    }
                } else if ($res_media[1] == -1) {
                    $res = -5;
                    $res += media_contentcheck($filePathSource, $mime);
                }
            } else if (!$res_media) {
                $res = -4;
            } else {
                $res = 0;
            }
        } else {
            $res = -1; //NO AUTORITZAT
        }

        return $res;
    }

            /**
     * Inicia tractament per obtenir la llista de gestions d'administració
     */
    private function startAdminTaskProcess($ptask=null) {
        global $ACT;
        global $_REQUEST;
        
        $ACT = $this->params['do'] = DW_ACT_EXPORT_ADMIN;

        $this->fillInfo();
        $this->startUpLang();
        if($ptask){
            if(!$_REQUEST['page'] || $_REQUEST['page']!=$ptask){
                $_REQUEST['page']=$ptask;
            }
            $this->params['task'] = $ptask;
        }
    }

        /**
     * Inicia tractament d'una pàgina de la dokuwiki
     */
    private function startPageProcess($pdo, $pid = NULL, $prev = NULL, $prange = NULL,
         $psum = NULL, $pdate = NULL, $ppre = NULL, $ptext = NULL, $psuf = NULL) {
        global $ID;
        global $ACT;
        global $REV;
        global $RANGE;
        global $DATE;
        global $PRE;
        global $TEXT;
        global $SUF;
        global $SUM;

        $ACT = $this->params['do'] = $pdo;
        $ACT = act_clean($ACT);
        
        if (!$pid) {
            $pid = DW_DEFAULT_PAGE;
        }
        $ID = $this->params['id'] = $pid;
        if ($prev) {
            $REV = $this->params['rev'] = $prev;
        }
        if ($prange) {
            $RANGE = $this->params['range'] = $prange;
        }
        if ($pdate) {
            $DATE = $this->params['date'] = $pdate;
        }
        if ($ppre) {
            $PRE = $this->params['pre'] = cleanText(substr($ppre, 0, -1));
        }
        if ($ptext) {
            $TEXT = $this->params['text'] = cleanText($ptext);
        }
        if ($psuf) {
            $SUF = $this->params['suf'] = cleanText($psuf);
        }
        if ($psum) {
            $SUM = $this->params['sum'] = $psum;
        }

        $this->fillInfo();
        $this->startUpLang();

//        trigger_event('DOKUWIKI_STARTED',  $this->dataTmp);
//        trigger_event('WIOC_AJAX_COMMAND_STARTED',  $this->dataTmp);
    }

    /**
     * Inicia tractament d'una pàgina de la dokuwiki
     */
    private function startMediaProcess($pdo, $pImageId = NULL, $pFromId = NULL) {
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
        global $INFO;

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

        $INFO = array_merge(pageinfo(), mediainfo());

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
        $vector_context = $this->params['vector_context'] = "article";
        if($pFromId && preg_match(
                "/^" . tpl_getConf("vector_discuss_ns") . "?$|^"
                            .tpl_getConf("vector_discuss_ns").".*?$/i", ":"
                . getNS(getID())
            )
        ) {
            $vector_context = $this->params['vector_context'] = "discuss";
        }

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
        $rev = $this->params['rev'] = (int) $INFO["rev"]; //$INFO comes from the DokuWiki core
        if ($rev < 1) {
            $rev = $this->params['rev'] = (int) $INFO["lastmod"];
        }

//        trigger_event('DOKUWIKI_STARTED',  $this->dataTmp);
//        trigger_event('WIOC_AJAX_COMMAND_STARTED',  $this->dataTmp);
        return $ret;
    }

    private function startUpLang() {
        global $conf;
        global $lang;

        //get needed language array
        include $this->tplIncDir() . "lang/en/lang.php";
        //overwrite English language values with available translations
        if (!empty($conf["lang"]) &&
                $conf["lang"] !== "en" &&
            file_exists($this->tplIncDir() . "/lang/" . $conf["lang"] . "/lang.php")
        ) {
            //get language file (partially translated language files are no problem
            //cause non translated stuff is still existing as English array value)
            include $this->tplIncDir() . "/lang/" . $conf["lang"] . "/lang.php";
        }
        if (!empty($conf["lang"]) &&
                $conf["lang"] !== "en" &&
            file_exists(DOKU_PLUGIN . "wikiiocmodel/lang/" . $conf["lang"] . "/lang.php")
        ) {
            include DOKU_PLUGIN."wikiiocmodel/lang/".$conf["lang"]."/lang.php";            
        }
    }

    /**
     * Realitza el per-procés d'una pàgina de la dokuwiki en format HTML.
     * Permet afegir etiquetes HTML al contingut final durant la fase de
     * preprocés
     *
     * @return string
     */
    private function doFormatedPagePreProcess() {
        $content = "";
        if ($this->runBeforePreprocess($content)) {
            unlock($this->params['id']); //try to unlock   
        }
        $this->runAfterPreprocess($content);
        return $content;
    }

    /**
     * Realitza el per-procés per recuperar el detall d'una imatge de la dokuwiki.
     * Permet afegir etiquetes HTML al contingut final durant la fase de
     * preprocés
     *
     * @return string
     */
    private function _getImageDetail() {
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
        include $this->tplIncDir() . "inc_detail.php";
        $content = ob_get_clean();
//        $content = preg_replace(
//            '/(<!-- TOC START -->\s?)(.*\s?)(<div class=.*tocheader.*<\/div>|<h3 class=.*toggle.*<\/h3>)((.*\s)*)(<!-- TOC END -->)/i',
//            '$1<div class="dokuwiki">$2$4</div>$6', $toc
//        );

        return $content;
    }

    private function doEditPagePreProcess() {
        global $ACT;

        $content = "";
        if($this->runBeforePreprocess($content)) {
            $ACT = act_edit($ACT);
            $ACT = act_permcheck($ACT);
        }
        $this->runAfterPreprocess($content);
        return $content;
    }

    private function doAdminTaskPreProcess() {
        global $ACT;
        global $INFO;
        global $conf;

        $content = "";
        if($this->runBeforePreprocess($content)) {
            $ACT = act_permcheck($ACT);
            //handle admin tasks
            // retrieve admin plugin name from $_REQUEST['page']
            if (!empty($_REQUEST['page'])) {
                $pluginlist = plugin_list('admin');
                if (in_array($_REQUEST['page'], $pluginlist)) {
                    // attempt to load the plugin
                    if ($plugin =& plugin_load('admin',$_REQUEST['page']) !== null){
                        if($plugin->forAdminOnly() && !$INFO['isadmin']){
                            // a manager tried to load a plugin that's for admins only
                            unset($_REQUEST['page']);
                            msg('For admins only',-1);
                        }else{
                            $plugin->handle();
                            $this->dataTmp["title"]= $plugin->getMenuText($conf['lang']);
                        }
                    }
                }
            }
            // check permissions again - the action may have changed
            $ACT = act_permcheck($ACT);            
        }
        $this->runAfterPreprocess($content);
        return $content;
    }

    private function doAdminTaskListPreProcess() {
        global $ACT;

        $content = "";
        if($this->runBeforePreprocess($content)) {
            $ACT = act_permcheck($ACT);
        }
        $this->runAfterPreprocess($content);
        return $content;
    }

    private function doSavePreProcess() {
        global $ACT;

        $code = 0;
        $ret = act_save($ACT);
        if ($ret === 'edit') {
            $code = 1004;
        } else if ($ret === 'conflict') {
            $code = 1003;
        }
        if ($code == 0) {
            $ACT = $this->params['do'] = DW_ACT_EDIT;
            $this->doEditPagePreProcess();
        } else {
            //S'han trobat conflictes i no s'ha pogut guardar
            //TODO[Josep] de moment tornem a la versió original, però cal 
            //cercar una solució més operativa com ara emmagatzemar un esborrany
            //per tal que l'usuari pugui comparar i acceptar canvis
            $ACT = $this->params['do'] = DW_ACT_SHOW;
            $this->doFormatedPagePreProcess();
        }
        return $code;
    }

    private function doCancelEditPreProcess() {
        global $ACT;

        $ACT = act_draftdel($ACT);
        $this->doFormatedPagePreProcess();
    }

    private function getFormatedPageResponse() {
        global $lang;
        $pageToSend = $this->getFormatedPage();

        $response = $this->getContentPage($pageToSend);
        return $response;
    }

    private function getAdminTaskResponse() {
        $pageToSend = $this->getAdminTaskHtml();
        $id = "admin_".$this->params["task"];
        return $this->getAdminTaskPage($id, $this->params["task"], $pageToSend);
    }
    
    private function getAdminTaskHtml(){
        global $INFO;
        global $conf;
        
        ob_start();
        trigger_event('TPL_ACT_RENDER', $ACT, "tpl_admin");
        
        $html_output = ob_get_clean() . "\n";
        return $html_output;
    }

    
    private function getAdminTaskListResponse() {
        $pageToSend = $this->getAdminTaskListHtml();
        //TODO[JOSEP] Cal agafar l'ide del contenidor del mainCFG o similar
        $containerId = "tb_admin";
        return $this->getAdminTaskListPage($containerId, $pageToSend);
    }
    
    private function getAdminTaskListHtml(){
        global $INFO;
        global $conf;
        
        ob_start();
        trigger_event('TPL_ACT_RENDER', $ACT);

        // build menu of admin functions from the plugins that handle them
        $pluginlist = plugin_list('admin');
        $menu = array();
        foreach ($pluginlist as $p) {
            if($obj =& plugin_load('admin',$p) === null) continue;

            // check permissions
            if($obj->forAdminOnly() && !$INFO['isadmin']) continue;

            $menu[$p] = array('plugin' => $p,
                    'prompt' => $obj->getMenuText($conf['lang']),
                    'sort' => $obj->getMenuSort()
                    );
        }
    
        // Admin Tasks
        if(count($menu)){
            usort($menu, 'p_sort_modes');
            // output the menu
            ptln('<div class="clearer"></div>');
            print p_locale_xhtml('adminplugins');
            ptln('<ul>');
            foreach ($menu as $item) {
                if (!$item['prompt']) continue;
                ptln('  <li><div class="li"><a href="'.DOKU_BASE.DOKU_SCRIPT.'?'
                        .'do=admin&amp;page='.$item['plugin'].'">'.$item['prompt']
                        .'</a></div></li>');
            }
            ptln('</ul>');
        }       
        
        $html_output = ob_get_clean() . "\n";
        return $html_output;
    }

    private function getCodePageResponse() {
        $pageToSend = $this->cleanResponse($this->_getCodePage());
        $resp = $this->getContentPage($pageToSend['content']);
        $resp['meta'] = $pageToSend['meta'];
        $resp["info"] = $this->generateInfo("info", $pageToSend['info']);

        return $resp;
    }

    private function cleanResponse($text) {
        global $lang;
        $patternStart = ".*(<p>\nEditeu la pàgina.*?"; // Fins la primera coincidencia de tancament
        $patternCloseA = "<\/p>)";
        $patternCloseB = "\\*!]]>\\*\/<\/script>)";

        // Capturem la informació
        $pattern = '/' . $patternStart . $patternCloseA . '/s';
        preg_match($pattern, $text, $match);
        $info = $match[1];

        // Eliminem la informació i el script inicial del contingut
        $pattern = '/' . $patternStart . $patternCloseB . '\\s*/s';
        $text = preg_replace($pattern, "", $text);

        // Eliminem les etiquetes no desitgades
        $pattern = "/<div id=\"size__ctl\".*?<\/div>\\s*/s";
        $text = preg_replace($pattern, "", $text);

        // Eliminem les etiquetes no desitgades
        $pattern = "/<div class=\"editButtons\".*?<\/div>\\s*/s";
        $text = preg_replace($pattern, "", $text);

        // Copiem el license
        $pattern = "/<div class=\"license\".*?<\/div>\\s*/s";
        preg_match($pattern, $text, $match);
        $license = $match[0];

        // Eliminem la etiqueta
        $text = preg_replace($pattern, "", $text);


        // Copiem el wiki__editbar
        $pattern = "/<div id=\"wiki__editbar\".*?<\/div>\\s*<\/div>\\s*/s";
        preg_match($pattern, $text, $match);
        $meta= $match[0];

        // Eliminem la etiqueta
        $text = preg_replace($pattern, "", $text);

        // Capturem el id del formulari
        $pattern = "/<form id=\"(.*?)\"/";
        //$form = "dw__editform";
        preg_match($pattern, $text, $match);
        $form = $match[1];


        // Afegim el id del formulari als inputs
        $pattern = "/<input/";
        $replace = "<input form=\"". $form . "\"";
        $meta = preg_replace($pattern, $replace, $meta);



        $response["content"] = $text;
        $response["info"] = [$info, $license];
        $metaId = str_replace(":", "_", $this->params['id']) . '_metaEditForm';
        $response["meta"] = [$this->getCommonPage($metaId, 
                                            $lang['metaEditForm'], 
                                            $meta)];

        return $response;
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
    private function generateInfo($type, $message, $id = null, $duration = -1) {
        if ($id === null) {
            $id = str_replace(":", "_", $this->params['id']);
        }

        return array (
            "id" => $id,
            "type" => $type,
            "message" => $message,
            "duration" => $duration,
            "timestamp" => date("d-m-Y H:i:s"));
    }

    private function getSaveInfoResponse($code){
        global $lang;
        global $TEXT;
        global $ID;

        $duration = -1;

        if($code==1004){
            $ret = array();
            $ret["code"] = $code;
            $ret["info"] = $lang['wordblock'];
            $ret["page"] = $this->getFormatedPageResponse();
            $type = "error";
        } elseif ($code == 1003) {
            $ret = array();
            $ret["code"] = $code;
            $ret["info"] = $lang['conflictsSaving']; //conflict
            $ret["page"] = $this->getFormatedPageResponse();
            $type = "error";
        } else {
            $ret = array("code" => $code, "info" => $lang["saved"]);
            //TODO[Josep] Cal canviar els literals per referencies dinàmiques del maincfg
            //      dw__editform, date i changecheck
            $ret["formId"] = "dw__editform";
            $ret["inputs"] = array(
                "date"        => @filemtime(wikiFN($ID)),
                "changecheck" => md5($TEXT)
            );
            $type = 'success';
            $duration = 10;
        }

        // TODO[Xavi] PROVES, En cas d'exit el missatge només ha de durar 10s
        $ret["info"] = $this->generateInfo($type, $ret["info"], null, $duration);

        return $ret;
    }

    public function getMetaResponse() {
        global $lang;
        global $ACT;
        $act_aux = $ACT;
        $ret = array('docId' => \str_replace(":", "_", $this->params['id']));
        $meta = array();
        $mEvt = new Doku_Event('WIOC_ADD_META', $meta);
        if ($mEvt->advise_before()) {
            $ACT = "show";
            $toc = wrapper_tpl_toc();
            $ACT = $act_aux;
            $metaId = \str_replace(":", "_", $this->params['id']) . '_toc';
            $meta[] = $this->getCommonPage($metaId, $lang['toc'], $toc);
        }
        $mEvt->advise_after();
        unset($mEvt);
        $ret['meta'] = $meta;
        return $ret;
    }

    public function getJsInfo() {
        global $JSINFO;
        $this->fillInfo();
        return $JSINFO;
    }

    public function getToolbarIds(&$value) {
        $value["varName"] = "toolbar";
        $value["toolbarId"] = "tool__bar";
        $value["wikiTextId"] = "wiki__text";
        $value["editBarId"] = "wiki__editbar";
        $value["editFormId"] = "dw__editform";
        $value["summaryId"] = "edit__summary";
    }

    private function runBeforePreprocess(&$content) {
        global $ACT;

        $brun = FALSE;
        // give plugins an opportunity to process the action
        $this->ppEvt = new Doku_Event('ACTION_ACT_PREPROCESS', $ACT);
        ob_start();
        $brun = ($this->ppEvt->advise_before());
        $content = ob_get_clean();
        return $brun;
    }

    private function runAfterPreprocess(&$content) {
        ob_start();
        $this->ppEvt->advise_after();
        $content .= ob_get_clean();
        unset($this->ppEvt);
    }

    private function fillInfo() {
        global $JSINFO;
        global $INFO;

        $INFO = pageinfo();
        //export minimal infos to JS, plugins can add more

        return $JSINFO;
    }

    private function getContentPage($pageToSend) {
        $pageTitle = tpl_pagetitle($this->params['id'], TRUE);
        $contentData = array(
            'id' => \str_replace(":", "_", $this->params['id']),
            'ns' => $this->params['id'],
            'title' => $pageTitle,
            'content' => $pageToSend
        );
        return $contentData;
    }

//    private function getMetaPage($metaId, $metaTitle, $metaToSend) {
//        $contentData = array(
//            'id' => $metaId,
//            'title' => $metaTitle,
//            'content' => $metaToSend
//        );
//        return $contentData;
//    }

    private function getAdminTaskListPage($id, $toSend) {
        global $lang;
        return $this->getCommonPage($id, $lang['btn_admin'], $toSend);
    }
    
    private function getAdminTaskPage($id, $task, $toSend) {
        //TO DO [JOSEP] Pasar el títol correcte segons idiaoma. Cal extreure'l de
        //plugin(admin)->getMenuText($language);
        return $this->getCommonPage($id, $task, $toSend);
    }
    
    private function getCommonPage($id, $title, $content){
        $contentData = array(
            'id' => $id,
            'title' => $title,
            'content' => $content
        );
        return $contentData;
    }

    private function getFormatedPage() {
        global $ACT;

        ob_start();
        trigger_event('TPL_ACT_RENDER', $ACT, 'onFormatRender');
        $html_output = ob_get_clean() . "\n";
        return $html_output;
    }

    private function _getCodePage() {
        global $ACT;

        ob_start();
        trigger_event('TPL_ACT_RENDER', $ACT, 'onCodeRender');
        $html_output = ob_get_clean() . "\n";
        return $html_output;
    }

    /**
     * Miguel Angel Lozano 12/12/2014
     * - Obtenir el gestor de medis
     */
    public function getMediaManager($image = NULL, $fromPage = NULL, $prev = NULL) {
        global $lang;

        $error = $this->startMediaManager(DW_ACT_MEDIA_MANAGER, $image, $fromPage, $prev);
        if ($error == 401) {
            throw new HttpErrorCodeException($error, "Access denied");
        } else if ($error == 404) {
            throw new HttpErrorCodeException($error, "Resource " . $image . " not found.");
        }
        $title = $lang['img_manager'];
        $ret = array(
            "content" => $this->doMediaManagerPreProcess(),
            "id" => "media",
            "title" => "media",
            "ns" => $fromPage,
            "imageTitle" => $title,
            "image" => $image,
            "fromId" => $fromPage,
            "modifyImageLabel" => $lang['img_manager'],
            "closeDialogLabel" => $lang['img_backto']
        );
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
    private function startMediaManager($pdo, $pImage = NULL, $pFromId = NULL, $prev = NULL) {
        global $ID;
        global $AUTH;
        //global $vector_action;
        //global $vector_context;
        //global $loginname;
        global $IMG;
        global $ERROR;
        global $SRC;
        global $conf;
        global $lang;
        global $INFO;
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

        $INFO = array_merge(pageinfo(), mediainfo());

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
        $rev = $this->params['rev'] = (int) $INFO["rev"]; //$INFO comes from the DokuWiki core
        if ($rev < 1) {
            $rev = $this->params['rev'] = (int) $INFO["lastmod"];
        }

//        trigger_event('DOKUWIKI_STARTED',  $this->dataTmp);
//        trigger_event('WIOC_AJAX_COMMAND_STARTED',  $this->dataTmp);
        return $ret;
    }

    private function doMediaManagerPreProcess() {
        global $ACT;
        global $JUMPTO;

        $content = "";
        if ($this->runBeforePreprocess($content)) {
            ob_start();
            // tpl_media(); //crida antiga total del media manager
            //crida parcial: només a la llista de fitxers del directori
            $this->mediaManagerFileList();
            if (isset($JUMPTO)) {
                if ($JUMPTO == false) {
                    throw new HttpErrorCodeException($error, "Bad request");
                }
            }
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
function mediaManagerFileList() {
    global $NS, $IMG, $JUMPTO, $REV, $lang, $fullscreen, $INPUT;
    $fullscreen = true;
    require_once DOKU_INC . 'lib/exe/mediamanager.php';

    $rev = '';
    $image = cleanID($INPUT->str('image'));
    if (isset($IMG))
        $image = $IMG;
    if (isset($JUMPTO))
        $image = $JUMPTO;
    if (isset($REV) && !$JUMPTO)
        $rev = $REV;

    echo '<div id="mediamanager__page">' . NL;
    echo '<h1>' . $lang['btn_media'] . '</h1>' . NL;
    html_msgarea();

    echo '<div class="panel namespaces">' . NL;
    echo '<h2>' . $lang['namespaces'] . '</h2>' . NL;
    echo '<div class="panelHeader">';
    echo $lang['media_namespaces'];
    echo '</div>' . NL;

   
    echo '<div class="panel filelist">' . NL;
    tpl_mediaFileList();
    echo '</div>' . NL;
    echo '</div>' . NL;
    echo '</div>' . NL;
}
    
public function getMediaMetaResponse() {
    global $NS, $IMG, $JUMPTO, $REV, $lang, $fullscreen, $INPUT;
    $fullscreen = true;
    require_once DOKU_INC . 'lib/exe/mediamanager.php';

    $rev = '';
    $image = cleanID($INPUT->str('image'));
    if (isset($IMG))
        $image = $IMG;
    if (isset($JUMPTO))
        $image = $JUMPTO;
    if (isset($REV) && !$JUMPTO)
        $rev = $REV;
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
        $ret = array('docId' => $NS);
       // $mEvt = new Doku_Event('WIOC_ADD_META', $meta);
       /* if ($mEvt->advise_before()) {
            $ACT = "show";
            $toc = wrapper_tpl_toc();
            $ACT = $act_aux;
            $metaId = \str_replace(":", "_", $this->params['id']) . '_toc';
            $meta[] = $this->getMetaPage($metaId, $lang['toc'], $toc);
        }*/
        //$mEvt->advise_after();
        //unset($mEvt);
        $ret['meta'] = $meta;
        return $ret;
    }
    
    public function getNsMediaTree($currentnode, $sortBy, $onlyDirs = FALSE) {
        global $conf;
        $sortOptions = array(0 => 'name', 'date');
        $nodeData = array();
        $children = array();
        $tree;

        if ($currentnode == "_") {
            return array('id' => "", 'name' => "", 'type' => 'd');
        }
        if ($currentnode) {
            $node = $currentnode;
            $aname = split(":", $currentnode);
            $level = count($aname);
            $name = $aname[$level - 1];
        } else {
            $node = '';
            $name = '';
            $level = 0;
        }
        $sort = $sortOptions[$sortBy];
        $base = $conf['datadir'];

        $opts = array('ns' => $node);
        $dir = str_replace(':', '/', $node);
        search(
            $nodeData, $base, 'search_index',
            $opts, $dir, 1
        );
        foreach (array_keys($nodeData) as $item) {
            if ($onlyDirs && $nodeData[$item]['type'] == 'd' || !$onlyDirs) {
                $children[$item]['id'] = $nodeData[$item]['id'];
                $aname = split(":", $nodeData[$item]['id']); //TODO[Xavi] @deprecated substitur per explode()
                $children[$item]['name'] = $aname[$level];
                $children[$item]['type'] = $nodeData[$item]['type'];
            }
        }

        /*$tree = array(
            'id' => $node, 'name' => $node,
            'type' => 'd', 'children' => $children
        );*/
        $tree = array(
            'id' => 'metaMedia', 'title' => $node,
            'content' => '<div>HOLA HOLA </div>'
        );
        return $tree;
    }
    /**
     * FI Miguel Angel Lozano 12/12/2014
     */
    
    public function getLoginName(){
        global $_SERVER;
        
        $loginname = "";
        if (!empty($conf["useacl"])){
            if (isset($_SERVER["REMOTE_USER"]) && //no empty() but isset(): "0" may be a valid username...
                $_SERVER["REMOTE_USER"] !== ""){
                $loginname = $_SERVER["REMOTE_USER"]; //$INFO["client"] would not work here (-> e.g. if
                                                      //current IP differs from the one used to login)
            }
        }
        return  $loginname;
    }
}
