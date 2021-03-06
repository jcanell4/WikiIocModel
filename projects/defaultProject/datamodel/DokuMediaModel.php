<?php
/**
 * Description of DokuMediaModel
 * @author josep
 */
if (!defined("DOKU_INC")) die();
require_once DOKU_INC."inc/media.php";
require_once(DOKU_INC. 'inc/pageutils.php');
require_once(DOKU_INC. 'inc/common.php');

class DokuMediaModel extends AbstractWikiModel {
    protected $id;
    protected $mediaName;
    protected $nstarget;
    protected $ns;
    protected $rev;
    protected $meta;
    protected $fromId;
    protected /*MediaDataQuery*/ $dataQuery;

    public function __construct($persistenceEngine) {
        parent::__construct($persistenceEngine);
        $this->dataQuery = $persistenceEngine->createMediaDataQuery();
    }

    public function initWithId($id, $rev = null, $meta = FALSE, $fromId=NULL){
        if($id)
            $this->id = $id;
        if($rev)
            $this->rev = $rev;
        if($meta){
            $this->meta = $meta;
        }else if(!$this->meta){
            $this->meta = $meta;
        }
        if($id){
            $this->ns = $this->nstarget = $this->dataQuery->getNs($id);
            $this->mediaName = $this->dataQuery->getIdWithoutNs($id);
        }
        if($fromId || $id){
            $this->fromId = ($fromId !== NULL) ? $fromId : $this->ns.":*";
        }
        if(!$this->ns){
            $this->ns = $this->dataQuery->getNs($fromId);
        }
    }

    public function initWhitTarget($nsTarget, $mediaName, $rev = null, $meta = FALSE, $fromId=NULL){
        if($nsTarget)
            $this->ns = $this->nstarget=$nsTarget;
        if($mediaName)
            $this->mediaName = $mediaName;
        if($rev)
            $this->rev = $rev;
        if($meta){
            $this->meta = $meta;
        }else if(!$this->meta){
            $this->meta = $meta;
        }
        if($nsTarget && $mediaName)
            $this->id = $nsTarget . ':' . $mediaName;

        $this->fromId = $fromId!==NULL?$fromId:$this->ns.":*";
    }

    public function init($id, $rev = null, $meta = FALSE, $fromId=NULL, $nsTarget=NULL){
        if($nsTarget){
            $this->initWhitTarget($nsTarget, $id, $rev, $meta, $fromId);
        }else{
            $this->initWithId($id, $rev, $meta, $fromId);
        }
    }

    public function exist(){
        return file_exists(mediaFN($this->id));
    }

    public function delete() {
        return $this->dataQuery->delete($this->id);
    }

    public function getData() {
        return $this->getUrl();
    }

    public function setData($toSet, $overWrite=FALSE) {
        if(is_array($toSet)){
            $params=$toSet;
        }else{
            $params=array('filePathSource' => $toSet, 'overWrite' => $overWrite);
        }

        return $this->dataQuery->save($this->id, $params['filePathSource'], $params['overWrite']);
    }

    public function getNS() {
        return $this->ns;
    }

    public function overWriteData($toSet) {
        return $this->setData($toSet, TRUE);
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
