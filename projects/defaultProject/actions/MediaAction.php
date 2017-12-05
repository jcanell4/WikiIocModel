<?php
/**
 * Description of MediaAction
 * @author josep
 */
if (!defined("DOKU_INC")) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');

require_once DOKU_PLUGIN . "wikiiocmodel/persistence/WikiPageSystemManager.php";
require_once DOKU_PLUGIN . "wikiiocmodel/projects/defaultProject/DokuAction.php";
require_once DOKU_PLUGIN . "wikiiocmodel/projects/defaultProject/datamodel/DokuMediaModel.php";
require_once DOKU_PLUGIN . "ajaxcommand/defkeys/MediaKeys.php";

abstract class MediaAction extends DokuAction
{
    protected $dokuModel;
    protected $persistenceEngine;

    public function __construct($persistenceEngine)
    {
        $this->persistenceEngine = $persistenceEngine;
        $this->dokuModel = new DokuMediaModel($persistenceEngine);
    }

   /**
     * És un mètode per sobrescriure. Per defecte no fa res, però la
     * sobrescriptura permet fer assignacions a les variables globals de la
     * wiki a partir dels valors de DokuAction#params.
     */
    abstract protected function initModel();

    protected function startProcess()
    {
        global $ID;
        global $IMG;
        global $REV;
        global $ERROR;
        global $SRC;
        global $NS;
        global $DEL;

        if (!$this->params[MediaKeys::KEY_ID]) {
            if($this->params[MediaKeys::KEY_FROM_ID]){
                $this->params[MediaKeys::KEY_ID] = $this->params[MediaKeys::KEY_FROM_ID];
            }else if($this->params[MediaKeys::KEY_NS]){
                $this->params[MediaKeys::KEY_ID] = $this->params[MediaKeys::KEY_FROM_ID] = $this->params[MediaKeys::KEY_NS].":*";
            }
        }else{
            $this->params[MediaKeys::KEY_FROM_ID] = $this->params[MediaKeys::KEY_ID];
        }
        $ID = $this->params[MediaKeys::KEY_ID];

        if ($this->params[MediaKeys::KEY_REV]) {
            $REV = $this->params[MediaKeys::KEY_REV];
        }

        if($this->params[MediaKeys::KEY_IMAGE_ID]){
            $IMG = $this->params[MediaKeys::KEY_IMG_ID] = $this->params[MediaKeys::KEY_IMAGE_ID];
            $SRC = mediaFN($this->params[MediaKeys::KEY_IMAGE_ID]);
        }else if($this->params[MediaKeys::KEY_IMG_ID]){
            $IMG = $this->params[MediaKeys::KEY_IMAGE_ID] = $this->params[MediaKeys::KEY_IMG_ID];
            $SRC = mediaFN($this->params[MediaKeys::KEY_IMAGE_ID]);
        }

        if($this->params[MediaKeys::KEY_DELETE]){
            $DEL = $this->params[MediaKeys::KEY_DELETE];
            if(!$this->params[MediaKeys::KEY_IMAGE_ID]){
                $IMG = $this->params[MediaKeys::KEY_IMG_ID] = $this->params[MediaKeys::KEY_IMAGE_ID]=$this->params[MediaKeys::KEY_DELETE];
            }
        }else if($this->params[MediaKeys::KEY_MEDIA_DO]
                && $this->params[MediaKeys::KEY_MEDIA_DO]=  MediaKeys::KEY_DELETE
                && $this->params[MediaKeys::KEY_IMAGE_ID]){
            $DEL = $this->params[MediaKeys::KEY_IMAGE_ID];
        }

        if($this->params[MediaKeys::KEY_MEDIA_ID] && !$this->params[MediaKeys::KEY_MEDIA_NAME]){
            $this->params[MediaKeys::KEY_MEDIA_NAME] = $this->params[MediaKeys::KEY_MEDIA_ID];
        }elseif($this->params[MediaKeys::KEY_MEDIA_NAME] && !$this->params[MediaKeys::KEY_MEDIA_ID]){
            $this->params[MediaKeys::KEY_MEDIA_ID] = $this->params[MediaKeys::KEY_MEDIA_NAME];
        }

        $this->initModel();

        $NS = $this->params[MediaKeys::KEY_NS] = $this->dokuModel->getNS();
    }

    protected function getModel(){
        return $this->dokuModel;
    }

//    protected function getRevisionList()
//    {
//        $extra = array();
//        $mEvt = new Doku_Event('WIOC_ADD_META_REVISION_LIST', $extra);
//        if ($mEvt->advise_before()) {
//            $ret = $this->getModel()->getRevisionList();
//        }
//        $mEvt->advise_after();
//        unset($mEvt);
//        return $ret;
//    }

    function mediaManagerFileList(){
        $content = "";
//        global $NS, $IMG, $JUMPTO, $REV, $lang, $fullscreen, $INPUT, $AUTH;
        $fullscreen = TRUE;
//        require_once DOKU_INC . 'lib/exe/mediamanager.php';

        $rev = '';
        $image = cleanID($this->params[MediaKeys::KEY_IMAGE_ID]);
//        if (isset($JUMPTO)) {
//            $image = $JUMPTO;
//        }
        if (isset($this->params[MediaKeys::KEY_REV])) {
            $rev = $this->params[MediaKeys::KEY_REV];
        }else{
            $jumpto = $image;
        }

        $content .= '<div id="mediamanager__page">' . NL;
        if ($this->params[MediaKeys::KEY_NS] == "") {
            $content .= '<h1>Documents de l\'arrel de documents</h1>';
        } else {
            $content .= '<h1>Documents de ' . $this->params[MediaKeys::KEY_NS] . '</h1>';
        }


        $content .= '<div class="panel filelist ui-resizable">' . NL;
        $content .= '<div class="panelContent">' . NL;
        $do = $this->params[MediaKeys::KEY_MEDIA_DO];     //$do = $AUTH;
        $query = $this->params[MediaKeys::KEY_QUERY];    //$_REQUEST['q'];
        if (!$query) {
            $query = '';
        }

        ob_start();
        if ($do == 'searchlist' || $query) {
            media_searchlist($query, $this->params[MediaKeys::KEY_NS], $do, TRUE, $this->params[MediaKeys::KEY_SORT]);
        } else {
            media_tab_files($this->params[MediaKeys::KEY_NS], $do, $jumpto);
        }
        $content .= ob_get_clean();

        $content .= '</div>' . NL;
        $content .= '</div>' . NL;
        $content .= '</div>' . NL;

        return $content;
    }


}
