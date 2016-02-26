<?php
if (!defined("DOKU_INC")) {
    die();
}
if (!defined('DOKU_PLUGIN')) {
    define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
}
require_once DOKU_PLUGIN."wikiiocmodel/datamodel/AbstractWikiDataModel.php";
require_once DOKU_PLUGIN."wikiiocmodel/WikiIocModelExceptions.php";
require_once DOKU_INC."inc/media.php";
require_once(DOKU_INC. 'inc/pageutils.php');
require_once(DOKU_INC. 'inc/common.php');



/**
 * Description of DokuMediaModel
 *
 * @author josep
 */
class DokuMediaModel extends AbstractWikiDataModel {
    protected $id;
    protected $mediaName;
    protected $nstarget;
    protected $rev;    
    protected $meta;    
    protected /*MediaDataQuery*/ $dataQuery;
    
    public function __construct($persistenceEngine) {
        $this->dataQuery = $persistenceEngine->createMediaDataQuery();
    }
    
    public function initWithId($id, $rev = null, $meta = FALSE){
        $this->id = $id;
        $this->rev = $rev;
        $this->meta = $meta;
        $this->nstarget = $this->dataQuery->getNs($id);
        $this->mediaName = $this->dataQuery->getIdWithoutNs($id);
    }
    
    public function initWhitTarget($nsTarget, $mediaName, $rev = null, $meta = FALSE){
        $this->nstarget=$nsTarget;
        $this->mediaName = $mediaName;               
        $this->rev = $rev;
        $this->meta = $meta;
        $this->id = $nsTarget . ':' . $mediaName;        
    }
    
    public function init($id, $rev = null, $meta = FALSE, $nsTarget=NULL){
        if($nsTarget){
            $this->initWhitTarget($nsTarget, $id, $rev, $meta);
        }else{
            $this->initWithId($id, $rev, $meta);
        }
    }
    


    public function getData() {
        return $this->getUrl();
    }

    public function setData($toSet) {
        if(is_array($toSet)){
            $params=$toSet;
        }else{
            $params=array('filePathSource' => $toSet, 'overWrite' => FALSE);
        }

        $this->dataQuery->save($this->id, $params['filePathSource'], $params['overWrite']);
    }

    public function upLoadData($toSet) {
        if(is_array($toSet)){
            $params=$toSet;
        }else{
            $params=array('filePathSource' => $toSet, 'overWrite' => FALSE);
        }

       return $this->dataQuery->upload($this->nstarget, $this->mediaName, $params['filePathSource'], $params['overWrite']);
    }
    
   /**
     * Obté un link al media identificat per $image, $rev
     * @param string $image //abans era $id. $id no s'utilitzava
     * @param bool $rev
     * @param bool $meta
     *
     * @return string
     */
    //[ALERTA Josep] Es deixa aquí la funció tot i que el codi es trasllada 
    //a WikiDataSystemUtility
    public function getUrl()
    {
        $size = media_image_preview_size($this->id, $this->rev, $this->meta);
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
}
