<?php
/**
 * Render Plugin for IOC projects
 *
 * @author Josep Cañellas <jcanell4@ioc.cat>
 */

if(!defined('DOKU_INC')) die();
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');

require_once DOKU_INC . 'inc/parser/xhtml.php';

/**
 * The Renderer
 */
class renderer_plugin_wikiiocmodel_xhtml extends Doku_Renderer_xhtml {
    var $docId="";
    var $text;
//    var $initialized;
    var $documentRawChunks;
    var $saveStructure=FALSE;
    var $sectionBaseNumber=1;
    var $chunksCounter=0;
    var $newInstances=TRUE;
    
    function canRender($format) {
      return ($format=='xhtml');
    }

    function init($docId, $rev='') {
       $this->docId = $docId;
       $this->rev = $rev;
       $this->initialized=FALSE;
       $this->newInstances=FALSE;
    }
    
    public function startSectionEdit($start, $type, $title = null) {
        $ret = parent::startSectionEdit($start, $type, $title);
        $this->documentRawChunks[$this->chunksCounter]=[];
        $this->documentRawChunks[$this->chunksCounter]["start"]=$start;
        $this->documentRawChunks[$this->chunksCounter]["type"]=$type;
        $this->documentRawChunks[$this->chunksCounter]["title"]=$title;
        $this->documentRawChunks[$this->chunksCounter]["params"]["level"]=$this->lastlevel;
        $this->documentRawChunks[$this->chunksCounter]["header_id"]=  $this->_headerToLink($title);
        $this->documentRawChunks[$this->chunksCounter]["text"]="";
        return $ret;
    }
    
//    public function toc_additem($id, $text, $level) {
//        parent::toc_additem($id, $text, $level);
//        $this->documentRawChunks[$this->chunksCounter]["params"]["level"]=$this->lastlevel;
//        $this->documentRawChunks[$this->chunksCounter]["header_id"]=  $id;        
//    }


    public function cdata($text) {
        parent::cdata($text);
//        $this->documentRawChunks[$this->chunksCounter]["text"].=$text;
    }
    
    public function finishSectionEdit($end = null) {
        parent::finishSectionEdit($end);
        if(!$end){
            $end = strlen($this->text);
        }
        $this->documentRawChunks[$this->chunksCounter]["end"]=$end;
        $start = $this->documentRawChunks[$this->chunksCounter]["start"]-1;
        $this->documentRawChunks[$this->chunksCounter]["text"]=  substr($this->text, $start, $end-$start);
        ++$this->chunksCounter;
    }

    public function _headerToLink($title,$create=false){
        $ret = parent::_headerToLink($title, $create);
        $ret .="__".str_replace(":", "_", $this->docId);
        return $ret;
    }

    public function isSingleton() {
//        $ret = $this->newInstances;
//        $this->newInstances = TRUE;                
//        return $ret;
        return parent::isSingleton();
    }
    
    function document_start() {
       parent::document_start();
       if(!$this->initialized){
            $this->doc = '';
            $this->footnotes = array();
            $this->lastsec = 0;
            $this->store = '';
            $this->_counter = array();
            $this->node = array(0,0,0,0,0);
            $this->_codeblock = 0; 
            $this->documentRawChunks=array();
            $this->text = io_readWikiPage(wikiFN($this->docId, $this->rev), $this->docId, $this->rev);               
            $this->initialized=TRUE;
       }
    }

}

//Setup VIM: ex: et ts=4 enc=utf-8 :
