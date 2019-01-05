<?php
/**
 * LaTeX Plugin: Export content to LaTeX
 * @license GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author  Marc Català <mcatala@ioc.cat>
 */
if(!defined('DOKU_INC')) die();
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
if (!defined('EXPORT_TMP')) define('EXPORT_TMP', DOKU_PLUGIN."tmp/latex/");
require_once DOKU_INC.'inc/parser/renderer.php';
require_once(DOKU_PLUGIN.'iocexportl/lib/renderlib.php');

abstract class AbstractNodeDoc{
    private $owner;
    var $type;
    
    protected function setOwner($owner){
        return $this->owner = $owner;
    }    

    public function __construct($type) {
        $this->type = $type;        
    }

    public function getOwner(){
        return $this->owner;
    }    

    public function getType(){
        return $this->type;
    }
    
    public abstract function getEncodeJson();
}

class CellNodeDoc extends StructuredNodeDoc{
    const TABLEHEADER_TYPE = "tableheader";
    const TABLECELL_TYPE = "tablecell";
    
    var $hasBorder = false;
    var $colspan;
    var $align;
    var $rowspan;
    
    public function __construct($type, $colspan = 1, $align = null, $rowspan = 1, $hasBorder=FALSE) {
        parent::__construct($type);
        $this->colspan = $colspan;
        $this->rowspan = $rowspan;
        $this->align = $align;
    }
    
    public function getEncodeJson() {
        $ret = "{\"type\":\"".$this->type."\",\"colspan\":\"".$this->colspan."\",\"rowspan\":\"".$this->rowspan."\",\"align\":\"".$this->align."\",\"content\":".$this->getContentEncodeJson();
        $ret .= "}";
        return $ret;
    }    
}

class TableNodeDoc extends StructuredNodeDoc{
    const TABLE_TYPE = "table";

    var $hasBorder = false;
    
    public function __construct($type, $hasBorder=FALSE) {
        parent::__construct($type);
        $this->hasBorder= $hasBorder;
    }
    
    public function getEncodeJson() {
        $ret = "{\"type\":\"".$this->type."\",\"hasborder\":\"".$this->hasBorder."\",\"content\":".$this->getContentEncodeJson();
        $ret .= "}";
        return $ret;
    }    
}

class StructuredNodeDoc extends AbstractNodeDoc{
    const PARAGRAPH_TYPE = "p";
    const STRONG_TYPE = "strong";
    const EMPHASIS_TYPE = "em";
    const UNDERLINE_TYPE = "u";
    const MONOSPACE_TYPE = "mono";
    const SUBSCRIPT_TYPE = "sub";
    const SUPERSCRIPT_TYPE = "sup";
    const DELETED_TYPE = "del";
    const FOOT_NOTE_TYPE = "footnote";
    const UNORDERED_LIST_TYPE = "ul";
    const ORDERED_LIST_TYPE = "ol";
    const LIST_CONTENT_TYPE = "listcontent";
    const QUOTE_TYPE = "quote";
    const SINGLEQUOTE_TYPE = "singlequote";
    const DOUBLEQUOTE_TYPE = "doublequote";
    const TABLEROW_TYPE = "tablerow";

    var $content;
    
    public function __construct($type) {
        parent::__construct($type);
        $this->content = array();
    }
        
    public function addContent(&$node){
        $this->content []= &$node;
        $node->setOwner($this);        
    }

    public function getContent($i=-1){
        if($i==-1){
            $ret = $this->content;
        }else{
            $ret = $this->content[$i];
        }
        return $ret;
    }
    
    public function sizeContent(){
        return count($this->content);
    }

    public function getContentEncodeJson() {
        $sep="";
        $ret .= "[";
        foreach ($this->content as $child){
            $ret .= $sep.$child->getEncodeJson();
            if(empty($sep)){
               $sep="," ;
            }
        }
        $ret .= "]";
        return $ret;        
    }
    
    public function getEncodeJson() {
        $ret = "{\"type\":\"".$this->type."\",\"content\":".$this->getContentEncodeJson();
        $ret .= "}";
        return $ret;
    }

}

class ListItemNodeDoc extends StructuredNodeDoc{
    const LIST_ITEM_TYPE = "li";
    
    var $level;

    public function __construct($level) {
        parent::__construct(self::LIST_ITEM_TYPE);
        $this->level = $level;
    }    

    public function getLevel(){
      return $this->level;  
    }
    public function getEncodeJson() {
        $ret = "{\"type\":\"".$this->type."\",\"level\":\"".$this->getLevel()."\",\"content\":".$this->getContentEncodeJson();
        $ret .= "}";
        return $ret;
    }
}

class LeveledNodeDoc extends StructuredNodeDoc{
    var $level;
    var $father;
    var $children;

    public function __construct($type, $level=0, LeveledNodeDoc &$parent=NULL) {
        parent::__construct($type);
        $this->father = &$parent;
        if($parent!=NULL){
            $this->father->addChild($this);
        }
        $this->level = $level;
        $this->children = array();
    }    
    
    public function getFather(){
        return $this->father;
    }    
    
    public function getLevel(){
      return $this->level;  
    }
    
    private function addChild(&$node){
        $this->children []= &$node;
    }

    public function getChildren(){
        return $this->children;
    }
    
    public function getChild($i){
        return $this->children[$i];
    }
    
    public function sizeChlidren(){
        return count($this->children);
    }

    public function getChildrenEncodeJson() {
        $sep="";
        $ret .= "[";
        foreach ($this->children as $child){
            $ret .= $sep.$child->getEncodeJson();
            if(empty($sep)){
               $sep="," ;
            }
        }
        $ret .= "]";
        return $ret;        
    }    
}

class HeaderNodeDoc extends LeveledNodeDoc{
    const HEADER_TEXT_TYPE = "ht";
    var $title;

    public function __construct($title, $level=1, LeveledNodeDoc &$parent=NULL) {
        parent::__construct(self::HEADER_TEXT_TYPE, $level, $parent);
        $this->title = $title;
    }    

    public function getEncodeJson() {
        $ret = "{\"type\":\"".$this->type."\",\"title\":\"".$this->title."\",\"level\":\"".$this->getLevel()."\",\"content\":".$this->getContentEncodeJson();
        $ret .= ",\"children\":". $this->getChildrenEncodeJson();
        $ret .= "}";
        return $ret;
    }
}

class RootNodeDoc extends LeveledNodeDoc{
    const ROOT_TYPE = "root";

    public function __construct() {
        parent::__construct(self::ROOT_TYPE);
    }
    
    public function getLevel(){
      return $this->level;  
    }
    
    public function getEncodeJson() {
        return $this->getChildrenEncodeJson();
    }
}

class LeafNodeDoc extends AbstractNodeDoc{
    const LINE_BREAK_TYPE = "lb";
    const HORIZONTAL_RULE_TYPE = "hr";
    const APOSTROPHE_TYPE = "apostrophe";
//    const QUOTE_TYPE = "quote";
    
    public function __construct($type) {
        parent::__construct($type);
    }
    
    public function getEncodeJson() {
        $ret = "{\"type\":\"".$this->type."\"}";
        return $ret;        
    }
}

class CodeNodeDoc extends TextNodeDoc{
    const CODE_TEXT_TYPE = "code";
    var $language;
    
    public function __construct($type, $text, $lang) {
        parent::__construct($type, $text);
        $this->language = $lang;
    }
    
    public function getEncodeJson() {
        return "{\"type\":\"".$this->type."\",\"language\":\"".$this->language."\",\"text\":\"".$this->text."\"}";
    }        
}

class TextNodeDoc extends AbstractNodeDoc{
    const PLAIN_TEXT_TYPE = "TEXT";
    const UNFORMATED_TEXT_TYPE = "unformatedText";
    const HTML_TEXT_TYPE = "htmlText";
    const PREFORMATED_TEXT_TYPE = "preformatedText";
     var $text;
     
     
    public function __construct($type, $text) {
        parent::__construct($type);
        $this->text = $text;
    }
    
    public function getEncodeJson() {
        return "{\"type\":\"".$this->type."\",\"text\":\"".$this->text."\"}";
    }

}

class renderer_plugin_wikiiocmodel_psdom extends Doku_Renderer {
    const BORDER_TYPES = ["pt_taula"];
    
    var $toc = NULL;
    var $rootNode = NULL;
    var $currentNode = NULL;
    
//    var $pdfBuilder;
    /**
     * Esta función construye el renderer a partir de las parámetros de configuración recibidos
     * @param array $params
     */
    public function init($params) {
        $this->toc = NULL;
        $this->rootNode = NULL;
        $this->currentNode = NULL;
        $this->doc = '';
    }

    /**
     * Returns the format produced by this renderer.
     */
    function getFormat(){ 
        return 'wikiiocmodel_psdom';
    }

    function reset(){
        $this->init($params);
    }

    
    
    function document_start() {
        $this->rootNode = new RootNodeDoc();
    }

    function document_end() {
        $this->doc = $this->rootNode->getEncodeJson();
    }

    function render_TOC() { return ''; }

    function toc_additem($id, $text, $level) {
        
    }

    function header($text, $level, $pos) {
        if($this->currentNode!=NULL){
            if($this->currentNode->getLevel()<$level){
                //fill
                $father = $this->currentNode;
            }else if($this->currentNode->getLevel()==$level){
                //germans
                $father = $this->currentNode->getFather();
            }else{
                //antecesor
                $father = $this->currentNode->getFather();
                while($father!=NULL && $father->getLevel()>=$level){
                    $father = $father->getFather();
                }
            }            
        }else{
            $father = $this->rootNode;
        }
        
        $this->currentNode = new HeaderNodeDoc($text, $level, $father);
    }

    function section_open($level) {
    }

    function section_close() {
    }

    function cdata($text) {
        $this->currentNode->addContent(new TextNodeDoc(TextNodeDoc::PLAIN_TEXT_TYPE, $text));
    }

    function p_open() {
        $paragraph = new StructuredNodeDoc(StructuredNodeDoc::PARAGRAPH_TYPE);
        $this->currentNode->addContent($paragraph);
        $this->currentNode = $paragraph;
    }

    function p_close() {
        $this->currentNode = $this->currentNode->getOwner();
    }

    function linebreak() {
        $this->currentNode->addContent(new LeafNodeDoc(LeafNodeDoc::LINE_BREAK_TYPE));
    }

    function hr() {
        $this->currentNode->addContent(new LeafNodeDoc(LeafNodeDoc::HORIZONTAL_RULE_TYPE));
    }

    function strong_open() {
        $node = new StructuredNodeDoc(StructuredNodeDoc::STRONG_TYPE);
        $this->currentNode->addContent($node);
        $this->currentNode = $node;        
    }

    function strong_close() {
        $this->currentNode = $this->currentNode->getOwner();        
    }

    function emphasis_open() {
        $node = new StructuredNodeDoc(StructuredNodeDoc::EMPHASIS_TYPE);
        $this->currentNode->addContent($node);
        $this->currentNode = $node;        
    }

    function emphasis_close() {
        $this->currentNode = $this->currentNode->getOwner();                
    }

    function underline_open() {
        $node = new StructuredNodeDoc(StructuredNodeDoc::UNDERLINE_TYPE);
        $this->currentNode->addContent($node);
        $this->currentNode = $node;                
    }

    function underline_close() {
        $this->currentNode = $this->currentNode->getOwner();                        
    }

    function monospace_open() {
        $node = new StructuredNodeDoc(StructuredNodeDoc::MONOSPACE_TYPE);
        $this->currentNode->addContent($node);
        $this->currentNode = $node;                
    }

    function monospace_close() {
        $this->currentNode = $this->currentNode->getOwner();                                
    }

    function subscript_open() {
        $node = new StructuredNodeDoc(StructuredNodeDoc::SUBSCRIPT_TYPE);
        $this->currentNode->addContent($node);
        $this->currentNode = $node;                        
    }

    function subscript_close() {
        $this->currentNode = $this->currentNode->getOwner();                                        
    }

    function superscript_open() {
        $node = new StructuredNodeDoc(StructuredNodeDoc::SUPERSCRIPT_TYPE);
        $this->currentNode->addContent($node);
        $this->currentNode = $node;                                
    }

    function superscript_close() {
        $this->currentNode = $this->currentNode->getOwner();                                                
    }

    function deleted_open() {
        $node = new StructuredNodeDoc(StructuredNodeDoc::DELETED_TYPE);
        $this->currentNode->addContent($node);
        $this->currentNode = $node;                                
    }

    function deleted_close() {
        $this->currentNode = $this->currentNode->getOwner();                                                
    }

    function footnote_open() {
        $node = new StructuredNodeDoc(StructuredNodeDoc::FOOT_NOTE_TYPE);
        $this->currentNode->addContent($node);
        $this->currentNode = $node;                                
    }

    function footnote_close() {
        $this->currentNode = $this->currentNode->getOwner();                                                
    }

    function listu_open() {
        $node = new StructuredNodeDoc(StructuredNodeDoc::UNORDERED_LIST_TYPE);
        $this->currentNode->addContent($node);
        $this->currentNode = $node;                                
    }


    function listu_close() {
        $this->currentNode = $this->currentNode->getOwner();                                                
    }

    function listo_open() {
        $node = new StructuredNodeDoc(StructuredNodeDoc::ORDERED_LIST_TYPE);
        $this->currentNode->addContent($node);
        $this->currentNode = $node;                                
    }


    function listo_close() {
        $this->currentNode = $this->currentNode->getOwner();                                                
    }

    function listitem_open($level) {
        $node = new ListItemNodeDoc($level);
        $this->currentNode->addContent($node);
        $this->currentNode = $node;                                
    }

    function listitem_close() {
        $this->currentNode = $this->currentNode->getOwner();                                                
    }

//    function listcontent_open() {
////        $node = new StructuredNodeDoc(StructuredNodeDoc::LIST_CONTENT_TYPE);
////        $this->currentNode->addContent($node);
////        $this->currentNode = $node;         
//        throw new UnavailableMethodExecutionException("renderer_plugin_wikiiocmodel_psdom#listcontent_open");        
//    }


//    function listcontent_close() {
////        $this->currentNode = $this->currentNode->getOwner();                                                
//        throw new UnavailableMethodExecutionException("renderer_plugin_wikiiocmodel_psdom#listcontent_close");        
//    }


    function unformatted($text) {
        $this->currentNode->addContent(new TextNodeDoc(TextNodeDoc::UNFORMATED_TEXT_TYPE, $this->_xmlEntities($text)));        
    }

    function php($text, $wrapper="code") {
        global $conf;
        if ($conf['phpok']){
            ob_start();
            eval($text);
            $ntext = ob_get_contents();
            ob_end_clean();
            $this->currentNode->addContent(new TextNodeDoc(TextNodeDoc::HTML_TEXT_TYPE, $next));                
        } else {
            $ntext = p_xhtml_cached_geshi($text, 'php', $wrapper);
            $this->currentNode->addContent(new CodeNodeDoc(CodeNodeDoc::CODE_TEXT_TYPE, $next, "php"));                
        }                
    }

    function phpblock($text) {
        $this->php($text, 'pre');        
    }

    function html($text, $wrapper="code") {
        global $conf;

        if ($conf['htmlok']){
            $ntext = $text;
            $this->currentNode->addContent(new TextNodeDoc(TextNodeDoc::HTML_TEXT_TYPE, $next));                
        } else {
            $next = p_xhtml_cached_geshi($text, 'html4strict', $wrapper);
            $this->currentNode->addContent(new CodeNodeDoc(CodeNodeDoc::CODE_TEXT_TYPE, $next, "html"));                
        }        
    }

    function htmlblock($text) {
        $this->html($text, 'pre');        
    }

    function preformatted($text) {
        $this->currentNode->addContent(new TextNodeDoc(TextNodeDoc::PREFORMATED_TEXT_TYPE, trim($this->_xmlEntities($text),"\n\r")));        
    }

    function quote_open() {
        $node = new StructuredNodeDoc(StructuredNodeDoc::QUOTE_TYPE);
        $this->currentNode->addContent($node);
        $this->currentNode = $node;                                
    }

    function quote_close() {
        $this->currentNode = $this->currentNode->getOwner();                                                        
    }

//    function file($text, $lang = null, $file = null ) {
//        //TOODO
//        throw new UnavailableMethodExecutionException("renderer_plugin_wikiiocmodel_psdom#file");
//    }
//
//    function code($text, $lang = null, $file = null ) {
//        //TOODO        
//        throw new UnavailableMethodExecutionException("renderer_plugin_wikiiocmodel_psdom#code");
//    }
//
//    function acronym($acronym) {
//        throw new UnavailableMethodExecutionException("renderer_plugin_wikiiocmodel_psdom#acronym");
//    }
//
//    function smiley($smiley) {
//        throw new UnavailableMethodExecutionException("renderer_plugin_wikiiocmodel_psdom#smiley");        
//    }
//
//    function wordblock($word) {        
//        throw new UnavailableMethodExecutionException("renderer_plugin_wikiiocmodel_psdom#wordblock");        
//    }
//
//
//    function entity($entity) {}
//
//    // 640x480 ($x=640, $y=480)
//    function multiplyentity($x, $y) {}

    function singlequoteopening() {
        $node = new StructuredNodeDoc(StructuredNodeDoc::SINGLEQUOTE_TYPE);
        $this->currentNode->addContent($node);
        $this->currentNode = $node;                                        
    }

    function singlequoteclosing() {
        $this->currentNode = $this->currentNode->getOwner();                                                                
    }

    function apostrophe() {
        $this->currentNode->addContent(new LeafNodeDoc(LeafNodeDoc::APOSTROPHE_TYPE));              
    }

    function doublequoteopening() {
        $node = new StructuredNodeDoc(StructuredNodeDoc::DOUBLEQUOTE_TYPE);
        $this->currentNode->addContent($node);
        $this->currentNode = $node;                                        
    }

    function doublequoteclosing() {
        $this->currentNode = $this->currentNode->getOwner();                                                                        
    }

//    function internalmedia ($src, $title=null, $align=null, $width=null, $height=null, $cache=null, $linking=null) {
//        global $ID;
//        list($src, $hash) = explode('#', $src,2);
//        resolve_mediaid(getNS($ID), $src, $exists);
//
//        list($ext, $mime) = mimetype($src);
//        $type = substr($mime,0,5);
//        if ($type === 'image'){
//            $file = mediaFN($src);
//            $this->_latexAddImage($file, $width, $height, $align, $title, $linking);
//        }elseif($type === 'appli'){
//            if (preg_match('/\.pdf$/', $src)){
//                $_SESSION['qrcode'] = TRUE;
//                $src = $this->_xmlEntities(DOKU_URL.'lib/exe/fetch.php?media='.$src);
//                qrcode_media_url($this, $src, $title, 'pdf');
//            }
//        }else{
//            if (!$_SESSION['u0']){
//                $this->code('FIXME internalmedia ('.$type.'): '.$src);
//            }
//        }
//    }
//
//    function externalmedia ($src, $title=NULL, $align=NULL, $width=NULL, $height=NULL, $cache=NULL, $linking=NULL) {
//        list($ext, $mime) = mimetype($src);
//        if (substr($mime,0,5) == 'image'){
//            $tmp_name = tempnam($this->tmp_dir.'/media', 'ext');
//            $client = new DokuHTTPClient;
//            $img = $client->get($src);
//            if (!$img) {
//                $this->externallink($src, $title);
//            } else {
//                $tmp_img = fopen($tmp_name, "w") or die("Can't create temp file $tmp_img");
//                fwrite($tmp_img, $img);
//                fclose($tmp_img);
//		//Add and convert image to pdf
//                $this->_latexAddImage($tmp_name, $width, $height, $align, $title, $linking, TRUE);
//            }
//        }else{
//            $this->externallink($src, $title);
//        }
//    }

//    function camelcaselink($link) {
//        $this->internallink($link, $link);
//    }
//
//    /**
//     * Render an internal Wiki Link
//     */
//    function internallink($id, $name = NULL) {
//        // default name is based on $id as given
//        $default = $this->_simpleTitle($id);
//        // now first resolve and clean up the $id
//        resolve_pageid(getNS($this->id),$id,$exists);
//        $name = $this->_getLinkTitle($name, $default, $isImage, $id);
//        list($page, $section) = preg_split('/#/', $id, 2);
//        if (!empty($section)){
//            $cleanid = noNS(cleanID($section, TRUE));
//        }else{
//            $cleanid = noNS(cleanID($id, TRUE));
//        }
//        $md5 = md5($cleanid);
//
//        $this->doc .= '\hyperref[';
//        $this->doc .= $md5;
//        $this->doc .= ']{';
//        $this->doc .= $name;
//        $this->doc .= '}';
//    }

//    /**
//     * Add external link
//     */
//    function externallink($url, $title = NULL) {
//        //Escape # only inside iocelem
//        if ($_SESSION['iocelem']){
//            $url = preg_replace('/(#|%)/','\\\\$1', $url);
//        }
//        if (!$title){
//            $this->doc .= '\url{'.$url.'}';
//        }else{
//            $title = $this->_getLinkTitle($title, $url, $isImage);
//            if (is_string($title)){
//                $this->doc .= '\href{'.$url.'}{'.$title.'}';
//            }else{//image
//                if (preg_match('/http|https|ftp/', $title['src'])){
//                    $this->externalmedia($title['src'],null,$title['align'],$title['width'],null,null,$url);
//                }else{
//                    $this->internalmedia($title['src'],null,$title['align'],$title['width'],null,null,$url);
//                }
//            }
//        }
//   }

//    /**
//     * Just print local links
//     * @fixme add image handling
//     */
//    function locallink($hash, $name = NULL){
//        $name = $this->_getLinkTitle($name, $hash, $isImage);
//        $this->doc .= $name;
//    }

//    /**
//     * InterWiki links
//     */
//    function interwikilink($match, $name = NULL, $wikiName, $wikiUri) {}
//
//    /**
//     * Just print WindowsShare links
//     * @fixme add image handling
//     */
//    function windowssharelink($url, $name = NULL) {
//        $this->unformatted('[['.$url.'|'.$name.']]');
//    }
//
//    /**
//     * Just print email links
//     * @fixme add image handling
//     */
//    function emaillink($address, $name = NULL) {
//        $this->doc .= '\href{mailto:'.$this->_xmlEntities($address).'}{'.$this->_xmlEntities($address).'}';
//    }
//
//    /**
//     * Construct a title and handle images in titles
//     * @author Harry Fuecks <hfuecks@gmail.com>
//     */
//    function _getLinkTitle($title, $default, & $isImage, $id=null) {
//        global $conf;
//
//        $isImage = FALSE;
//        if ( is_null($title) ) {
//            if ($conf['useheading'] && $id) {
//                $heading = p_get_first_heading($id);
//                if ($heading) {
//                      return $this->_latexEntities($heading);
//                }
//            }
//            return $this->_latexEntities($default);
//        } else if ( is_string($title) ) {
//            return $this->_latexEntities($title);
//        } else if ( is_array($title) ) {
//            $isImage = TRUE;
//            if (isset($title['caption'])) {
//                $title['title'] = $title['caption'];
//            } else {
//                $title['title'] = $default;
//            }
//            return $title;
//        }
//    }
    
//    function rss ($url,$params) {}

    function table_open($maxcols = null, $numrows = null, $pos = null){
        $isBorderType = $this->_isBorderTypeTable($_SESSION["table_types"]);
        $node = new TableNodeDoc(TableNodeDoc::TABLE_TYPE, $isBorderType);
        $this->currentNode->addContent($node);
        $this->currentNode = $node;                                                
    }

    function table_close($pos = null){
        $this->currentNode = $this->currentNode->getOwner();                                                                        
    }

    function tablerow_open(){
        $node = new StructuredNodeDoc(StructuredNodeDoc::TABLEROW_TYPE);
        $this->currentNode->addContent($node);
        $this->currentNode = $node;                                                
    }

    function tablerow_close(){
        $this->currentNode = $this->currentNode->getOwner();                                                                        
    }

    function tableheader_open($colspan = 1, $align = null, $rowspan = 1){
        $isBorderType = $this->_isBorderTypeTable($_SESSION["table_types"]);
        $node = new CellNodeDoc(CellNodeDoc::TABLEHEADER_TYPE, $colspan, $align, $rowspan, $isBorderType);
        $this->currentNode->addContent($node);
        $this->currentNode = $node;                                                
    }

    function tableheader_close(){
        $this->currentNode = $this->currentNode->getOwner();                                                                        
    }

    function tablecell_open($colspan = 1, $align = null, $rowspan = 1){
        $isBorderType = $this->_isBorderTypeTable($_SESSION["table_types"]);
        $node = new CellNodeDoc(CellNodeDoc::TABLECELL_TYPE, $colspan, $align, $rowspan, $isBorderType);
        $this->currentNode->addContent($node);
        $this->currentNode = $node;                                                
    }

    function tablecell_close(){
        $this->currentNode = $this->currentNode->getOwner();                                                                        
    }
    
    private function _isBorderTypeTable($types){
        return count(array_intersect($types, self::BORDER_TYPES))!=0;
    }
}
