<?php
/**
 * LaTeX Plugin: Export content to LaTeX
 * @license GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author  Marc Català <mcatala@ioc.cat>
 */
if(!defined('DOKU_INC')) die();
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
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

class IocElemNodeDoc extends LeveledNodeDoc {
    const IOC_ELEM_TYPE = "iocElemType";
    const IOC_ELEM_TYPE_EXAMPLE = "example";
    const IOC_ELEM_TYPE_IMPORTANT = "important";
    const IOC_ELEM_TYPE_COMP = "text";
    const IOC_ELEM_TYPE_COMP_LARGE = "textl";
    const IOC_ELEM_TYPE_NOTE = "note";
    const IOC_ELEM_TYPE_REF = "reference";
    const IOC_ELEM_TYPE_CLIP = "copytoclipboard";
    const IOC_ELEM_TYPE_QUOTE = "quote";
    const IOC_ELEM_TYPE_INCLUDE = "include";

    protected $title;
    protected $offset;
    protected $width;
    protected $elemType;

    public function __construct($type, $title, $offset=FALSE, $width=FALSE, $level=0) {
        parent::__construct(self::IOC_ELEM_TYPE, $level);
        $this->elemType= $type;
        $this->title = $title;
        $this->offset = $offset;
        $this->width= $width;
    }

    public function getEncodeJson() {
        $ret = "{\n\"type\":\"".trim($this->type)."\""
                .",\n\"elemType\":\"".trim($this->elemType)."\""
                .",\n\"title\":\"".trim($this->title)."\"";
        if($this->offset){
            $ret .= ",\n\"offset\":\"".trim($this->offset)."\"";
        }
        if($this->width){
            $ret .= ",\n\"width\":\"".trim($this->width)."\"";
        }
        $ret .= ",\n\"content\":".$this->getContentEncodeJson();
        $ret .= "\n}";
        return $ret;
    }

}

class FigureFrame extends StructuredNodeDoc {
    const FRAME_TYPE_FIGURE = "frameFigure";

    protected $id = false;
    protected $title;
    protected $footer;
    protected $hasBorder;

    public function __construct($type, $id="", $title="", $footer="", $hasBorder=FALSE) {
        parent::__construct($type);
        $this->id       = $id;
        $this->title    = $title;
        $this->footer   = $footer;
        $this->hasBorder= $hasBorder;
    }

    public function setNodeParams($title="", $footer="", $hasBorder=FALSE) {
        $this->title  = $title;
        $this->footer = $footer;
        $this->hasBorder= $hasBorder;
    }

    public function getEncodeJson() {

        $sanitizedTitle = trim(str_replace("\"", "\\\"", $this->title));
        $sanitizedFooter = trim(str_replace("\"", "\\\"", $this->footer));
        $ret = "{\n\"type\":\"".trim($this->type)."\""
                .",\n\"id\":\"".trim($this->id)."\""
                .",\n\"title\":\"". $sanitizedTitle ."\""
                .",\n\"footer\":\"".$sanitizedFooter."\""
                .",\n\"hasBorder\":\"".trim($this->hasBorder)."\""
                .",\n\"content\":".$this->getContentEncodeJson();
        $ret .= "\n}";
        return $ret;
    }
}

class TableFrame extends StructuredNodeDoc {
    const FRAME_TABLE= "frameTable";
    const TABLEFRAME_TYPE_TABLE= "tableframetypetable";
    const TABLEFRAME_TYPE_ACCOUNTING = "tableframetypeaccounting";

    protected $id = false;
    protected $title;
    protected $footer;
    protected $widths;
    protected $types;
    protected $hasBorder;

    public function __construct($type, $id="", $title="", $footer="", $widths="", $types="", $hasBorder=FALSE) {
        parent::__construct(self::FRAME_TABLE);
        $this->id       = $id==NULL ? "" : $id;
        $this->title    = $title==NULL ? "" : $title;
        $this->footer   = $footer==NULL ? "" : $footer;
        $this->widths   = $widths==NULL ? "" : $widths;
        $this->types    = $types==NULL ? ($type==self::TABLEFRAME_TYPE_TABLE ? "typetable" : "typeaccounting") : $types;
        $this->hasBorder= $hasBorder==NULL ? FALSE : $hasBorder;
    }

    public function getEncodeJson() {
        $sanitizedTitle = trim(str_replace("\"", "\\\"", $this->title));
        $sanitizedFooter = trim(str_replace("\"", "\\\"", $this->footer));

        $ret = "{\n\"type\":\"".trim($this->type)."\""
                .",\n\"id\":\"".trim($this->id)."\""
                .",\n\"title\":\"".$sanitizedTitle."\""
                .",\n\"footer\":\"".$sanitizedFooter."\""
                .",\n\"widths\":\"".trim($this->widths)."\""
                .",\n\"types\":\"".trim($this->types)."\""
                .",\n\"hasBorder\":\"".trim($this->hasBorder)."\""
                .",\n\"content\":".$this->getContentEncodeJson();
        $ret .= "\n}";
        return $ret;
    }
}

class RowNodeDoc extends StructuredNodeDoc {
    var $openHead = NULL;
    var $closeHead = NULL;
    var $isOpened = FALSE;
    var $isClosed = FALSE;

    public function addContent(&$node){
        parent::addContent($node);
        $isHead = ($node->type === CellNodeDoc::TABLEHEADER_TYPE);
        $pare = $this->getOwner();
        $g = ($pare->sizeContent() > 1) ? 2 : (($pare->sizeContent() == 1) ? 1 : 0);
        $germa = $pare->getContent($pare->sizeContent()-$g, FALSE);
        $primer_germa = $pare->getContent(0, FALSE);
        if ($isHead) {
            $this->openHead = ($this->openHead || (!$this->openHead && !$germa->isOpened)) ? TRUE : NULL;
            $this->isOpened  = $germa->isOpened || $this->openHead;
        }else {
            $this->closeHead = ($primer_germa->openHead && !$germa->isClosed && !$germa->closeHead) ? TRUE : NULL;
            $this->isClosed = $germa->isClosed || $this->closeHead;
        }
    }

    public function getEncodeJson() {
        $openHead = ($this->openHead) ? ",\n\"openHead\":\"".$this->openHead."\"" : "";
        $closeHead = ($this->closeHead) ? ",\n\"closeHead\":\"".$this->closeHead."\"" : "";
        $ret = "{\n\"type\":\"".$this->type."\""
              .$openHead
              .$closeHead
              .",\n\"content\":".$this->getContentEncodeJson();
        $ret .= "\n}";
        return $ret;
    }
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
        $this->hasBorder = $hasBorder;
    }

    public function getEncodeJson() {
        $ret = "{\n\"type\":\"".$this->type."\""
                .",\n\"colspan\":\"".$this->colspan."\""
                .",\n\"rowspan\":\"".$this->rowspan."\""
                .",\n\"align\":\"".$this->align."\""
                .",\n\"hasBorder\":\"".$this->hasBorder."\""
                .",\n\"content\":".$this->getContentEncodeJson();
        $ret .= "\n}";
        return $ret;
    }
}

class TableNodeDoc extends StructuredNodeDoc{
    const TABLE_TYPE = "table";

    private $hasBorder = false;
    private $maxcols;
    private $numrows;
    private $pos;

    public function __construct($type, $hasBorder = FALSE, $maxcols = NULL, $numrows = NULL, $pos = NULL) {
        parent::__construct($type);
        $this->hasBorder= $hasBorder;
        $this->maxcols= $maxcols;
        $this->numrows= $numrows;
        $this->pos= $pos;
    }

    public function getEncodeJson() {
        $ret = "{\n\"type\":\"{$this->type}\""
                .",\n\"hasBorder\":\"{$this->hasBorder}\""
                .",\n\"maxcols\":\"{$this->maxcols}\""
                .",\n\"numrows\":\"{$this->numrows}\""
                .",\n\"pos\":\"{$this->pos}\""
                .",\n\"content\":".$this->getContentEncodeJson();
        $ret .= "\n}";
        return $ret;
    }
}

class StructuredNodeDoc extends AbstractNodeDoc{
    const DELETED_TYPE          = "del";
    const DOUBLEQUOTE_TYPE      = "doublequote";
    const EMPHASIS_TYPE         = "em";
    const FOOT_NOTE_TYPE        = "footnote";
    const LIST_CONTENT_TYPE     = "listcontent";
    const MONOSPACE_TYPE        = "mono";
    const ORDERED_LIST_TYPE     = "ol";
    const PARAGRAPH_TYPE        = "p";
    const QUOTE_TYPE            = "quote";
    const ROOTCONTENT_TYPE      = "rootcontent";
    const SINGLEQUOTE_TYPE      = "singlequote";
    const STRONG_TYPE           = "strong";
    const SUBSCRIPT_TYPE        = "sub";
    const SUPERSCRIPT_TYPE      = "sup";
    const TABLEROW_TYPE         = "tablerow";
    const UNDERLINE_TYPE        = "u";
    const UNORDERED_LIST_TYPE   = "ul";

    protected $content;

    public function __construct($type) {
        parent::__construct($type);
        $this->content = array();
    }

    public function addContent(&$node){
        $this->content []= &$node;
        $node->setOwner($this);
    }

    public function getContent($i = -1, $forceFull=TRUE){
        if ($i === -1 && $forceFull) {
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
        $sep = "";
        $ret = "[\n";
        foreach ($this->content as $child){
            $ret .= $sep.$child->getEncodeJson();
            if (empty($sep)) {
                $sep = ",\n" ;
            }
        }
        $ret .= "\n]";
        return $ret;
    }

    public function getEncodeJson() {
        $ret = "{\n\"type\":\"".$this->type."\""
                .",\n\"content\":".$this->getContentEncodeJson();
        $ret .= "\n}";
        return $ret;
    }

}

class SpecialBlockNodeDoc extends StructuredNodeDoc{
    const BLOCVERD_TYPE         = "blocverd";
    const HIDDENCONTAINER_TYPE  = "hiddenContainer";
    const NEWCONTENT_TYPE       = 'newcontent';
    const PROTECTED_TYPE        = 'protected';
    const SOL_TYPE              = 'sol';
    const SOLUCIO_TYPE          = 'solucio';
    const VERD_TYPE             = "verd";
    const EDITTABLE_TYPE        = "edittable";

    public function __construct($type) {
        parent::__construct($type);
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
        $ret = "{\n\"type\":\"".$this->type."\""
                .",\n\"level\":\"".$this->getLevel()."\""
                .",\n\"content\":".$this->getContentEncodeJson();
        $ret .= "\n}";
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
        $sep = "";
        $ret = "[\n";
        foreach ($this->children as $child){
            $ret .= $sep.$child->getEncodeJson();
            if (empty($sep)) {
               $sep = ",\n" ;
            }
        }
        $ret .= "\n]";
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
        $sanitizedTitle = trim(str_replace("\"", "\\\"", $this->title));

        $ret = "{\n\"type\":\"".$this->type."\""
                .",\n\"title\":\"".$sanitizedTitle."\""
                .",\n\"level\":\"".$this->getLevel()."\""
                .",\n\"content\":".$this->getContentEncodeJson();
        $ret .= ",\n\"children\":".$this->getChildrenEncodeJson();
        $ret .= "\n}";
        return $ret;
    }
}

class RootContentNode extends StructuredNodeDoc{
    private $rootNode;

    public function __construct($rootNode) {
        parent::__construct(self::ROOTCONTENT_TYPE);
        $this->rootNode = $rootNode;
    }

    public function getLevel(){
        return 1;
    }

    public function getFather(){
        return $this->rootNode;
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
        $content = trim($this->getContentEncodeJson(), "[\n]");
        $children = trim($this->getChildrenEncodeJson(), "[\n]");
        return "[\n$content,$children\n]";
    }
}

class LeafNodeDoc extends AbstractNodeDoc{
    const LINE_BREAK_TYPE       = "lb";
    const HORIZONTAL_RULE_TYPE  = "hr";
    const APOSTROPHE_TYPE       = "apostrophe";
    const DOUBLEAPOSTROPHE_TYPE = "doubleapostrophe";
    const BACKSLASH_TYPE        = "backslash";
    const DOUBLEHYPHEN_TYPE     = "doublehyphen";
    const GRAVE_TYPE            = "grave";
    const ACRONYM_TYPE          = "acronym";
    const OP_SINGLEQUOTE_TYPE   = "open_singlequote";
    const CL_SINGLEQUOTE_TYPE   = "close_singlequote";
    const NO_BREAK_SPACE_TYPE   = "nbsp";
    const EXTRA_WIDTH_TYPE      = "ExtraWidth";
    const NORMAL_WIDTH_TYPE     = "NormalWidth";

    private $acronym;

    public function __construct($type) {
        parent::__construct($type);
    }

    public function convertAcronym($key_acronym, $value_acronym) {
        $this->acronym = $key_acronym; //key:No traduce, value:sí traduce
    }

    public function getEncodeJson() {
        $ret = "{\"type\":\"".$this->type."\"";
        if ($this->acronym) {
            $ret .= ",\n\"acronym\":\"{$this->acronym}\"";
        }
        $ret.= "\n}";
        return $ret;
    }
}

class ReferenceNodeDoc extends AbstractNodeDoc{
    const REFERENCE_TYPE = "reference_to";
    const REF_FIGURE_TYPE = "ref_fig";
    const REF_TABLE_TYPE = "ref_tab";
    const REF_WIKI_LINK = "wikilink";
    const REF_INTERNAL_LINK = "internallink";
    const REF_EXTERNAL_LINK = "externallink";
    private $refId;
    private $refType;
    private $refTitle;

    public function __construct($refId, $refType="", $refTitle="") {
        parent::__construct(self::REFERENCE_TYPE);
        $this->refId = $refId;
        $this->refType = $refType;
        $this->refTitle = $refTitle;
    }

    public function getEncodeJson() {
        return "{\n\"type\":\"".$this->type."\""
                .",\n\"referenceId\":\"".$this->refId."\""
                .",\n\"referenceType\":\"".$this->refType."\"\n"
                .",\n\"referenceTitle\":\"".$this->refTitle."\""
                ."\n}";
    }

}

class TextNodeDoc extends AbstractNodeDoc{
    const PLAIN_TEXT_TYPE       = "TEXT";
    const UNFORMATED_TEXT_TYPE  = "unformatedText";
    const HTML_TEXT_TYPE        = "htmlText";
    const PREFORMATED_TEXT_TYPE = "preformatedText";
    protected $text;

    public function __construct($type, $text) {
        parent::__construct($type);
        $this->text = $text;
    }

    public function getEncodeJson() {
        if(($this->type=== self::PREFORMATED_TEXT_TYPE)
                || ($this->type=== self::UNFORMATED_TEXT_TYPE)){
            $cr = "<br>";
        }else{
            $cr = " ";
        }
        $text = preg_replace(array("/\t/", "/ *\r\n/", "/ *\n/"), array("    ", $cr, $cr), $this->text);
        $text = preg_replace('/[\x00-\x1F]/', '', $text);
        $ret = "{\n\"type\":\"".$this->type."\""
                .",\n\"text\":\"".$text."\"";
        $ret .= "\n}";
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
        $text = preg_replace(array("/\\\/", "/\t/", "/ *\r\n/", "/ *\n/", "/\"/"), array("&#92;", "    ", "<br>", "<br>", "\\\""), $this->text);
        return "{\n\"type\":\"".$this->type."\""
                .",\n\"language\":\"".$this->language."\""
                .",\n\"text\":\"".$text."\""
                ."\n}";
    }
}

class ImageNodeDoc extends AbstractNodeDoc {
    const IMAGE_TYPE = "img";

    protected $id;
    protected $src;
    protected $title;
    protected $align;
    protected $width;
    protected $height;
    protected $cache;
    protected $linking;

     public function __construct($ns, $title=null, $align=null, $width=null, $height=null, $cache=null, $linking=null) {
        parent::__construct(self::IMAGE_TYPE);
        $this->id      = preg_replace("/^:/", "", $ns);
        $this->src     = mediaFN($this->id);
        $this->title   = $title;
        $this->align   = $align;
        $this->width   = $width;
        $this->height  = $height;
        $this->cache   = $cache;
        $this->linking = $linking;
    }

    public function getEncodeJson() {
        $sanitizedTitle = trim(str_replace("\"", "\\\"", $this->title));

        $ret = "{\n\"type\":\"".trim($this->type)."\""
                .",\n\"id\":\"".trim($this->id)."\""
                .",\n\"src\":\"".trim($this->src)."\""
                .",\n\"title\":\"".$sanitizedTitle."\""
                .",\n\"align\":\"".trim($this->align)."\""
                .",\n\"width\":\"".trim($this->width)."\""
                .",\n\"height\":\"".trim($this->height)."\""
                .",\n\"cache\":\"".trim($this->cache)."\""
                .",\n\"linking\":\"".trim($this->linking)."\"";
        $ret .= "\n}";
        return $ret;
    }
}

class SmileyNodeDoc extends AbstractNodeDoc {
    const SMILEY_TYPE = "smiley";
    const SMILEYS_PATH = DOKU_INC."lib/images/smileys/";
    protected $src;
    protected $file;

     public function __construct($smiley) {
        parent::__construct(self::SMILEY_TYPE);
        $this->src = self::SMILEYS_PATH.$smiley;
        $this->file = $smiley;
    }

    public function getEncodeJson() {
        $ret = "{\n\"type\":\"".trim($this->type)."\""
                .",\n\"src\":\"".trim($this->src)."\""
                .",\n\"file\":\"".trim($this->file)."\"";
        $ret .= "\n}";
        return $ret;
    }
}

class LatexMathNodeDoc extends AbstractNodeDoc {
    const LATEX_MATH_TYPE = "latex_math";
    protected $src;
    protected $title;
    protected $class;

     public function __construct($filePath, $title="", $class="inlinelatex") {
        parent::__construct(self::LATEX_MATH_TYPE);
        $this->src = $filePath;
        $this->title = $title;
        $this->class = $class;
    }

    public function getEncodeJson() {
        $sanitizedTitle = trim(str_replace("\"", "\\\"", $this->title));

        $ret = "{\n\"type\":\"".$this->type."\""
                .",\n\"src\":\"".trim($this->src)."\""
                .",\n\"title\":\"".$sanitizedTitle."\""
                .",\n\"class\":\"".$this->class."\"";
        $ret .= "\n}";
        return $ret;
    }
}
/**
 * class renderer_plugin_wikiiocmodel_psdom
 */
class renderer_plugin_wikiiocmodel_psdom extends Doku_Renderer {
    const UNEXISTENT_B_IOC_ELEMS_TYPE = -1;
    const REFERRED_B_IOC_ELEMS_TYPE = 0;
    const UNREFERRED_B_IOC_ELEMS_TYPE = 1;
    const BORDER_TYPES = ["pt_taula"];

    var $toc = NULL;
    var $rootNode = NULL;
    var $currentNode = NULL;
    var $table_types = "";

    var $tmpData=array();
    var $actualLevel;

    var $storeNode = [];
    var $bIocElems = array(array(),  array());
    var $currentBIocElemsType = self::UNEXISTENT_B_IOC_ELEMS_TYPE;
    var $bIocElemsRefQueue = array();
    var $levelDiff=0;

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

    function reset($params=NULL){
        $this->init($params);
    }

    function document_start() {
        if (!isset($this->rootNode)){
            $this->rootNode = new RootNodeDoc();
            if ($this->currentNode === NULL) {
                $node = new RootContentNode($this->rootNode);
                $this->rootNode->addContent($node);
                $this->currentNode = $node;
            }
        }
    }

    function document_end() {
        $this->doc = $this->rootNode->getEncodeJson();
    }

    function render_TOC() {
        return '';
    }

    function toc_additem($id, $text, $level) {
        $this->toc[] = ['id'=>$id, 'text'=>$text, 'level'=>$level];
    }

    function header($text, $level, $pos) {
        if ($this->actualLevel == NULL && $level > 1) {
            throw new Exception("Error en el nivell d'encapçalament");
        }
        if ($this->currentNode != NULL){
            while($this->currentNode->getType()!== HeaderNodeDoc::HEADER_TEXT_TYPE
                    && $this->currentNode->getType()!== RootNodeDoc::ROOT_TYPE
                    && $this->currentNode->getType()!== RootContentNode::ROOTCONTENT_TYPE){
                $this->currentNode = $this->currentNode->getOwner();
            }
            if ($this->currentNode->getLevel() < $level){
                //fill
                $father = $this->currentNode;
            }else if($this->currentNode->getLevel() == $level){
                //germans
                $father = $this->currentNode->getFather();
            }else{
                //antecesor
                $father = $this->currentNode->getFather();
                while($father != NULL && $father->getLevel() >= $level){
                    $father = $father->getFather();
                }
            }
        }else{
            $father = $this->rootNode;
        }
        $this->lastlevel = $this->actualLevel = $level;
        $this->currentNode = new HeaderNodeDoc($text, $level, $father);
    }

    function section_open($level) {
    }

    function section_close() {
    }

    function cdata($text) {
        if (strpos($text, "\\") !== FALSE) {
            $atext = explode("\\", $text);
            $this->currentNode->addContent(new TextNodeDoc(TextNodeDoc::PLAIN_TEXT_TYPE, $atext[0]));
            for($i=1; $i<count($atext); $i++) {
                $this->currentNode->addContent(new LeafNodeDoc(LeafNodeDoc::BACKSLASH_TYPE));
                $this->currentNode->addContent(new TextNodeDoc(TextNodeDoc::PLAIN_TEXT_TYPE, $atext[$i]));
            }
        }else{
            $this->currentNode->addContent(new TextNodeDoc(TextNodeDoc::PLAIN_TEXT_TYPE, $text));
        }
    }

    function p_open() {
        $paragraph = new StructuredNodeDoc(StructuredNodeDoc::PARAGRAPH_TYPE);
        if($this->currentNode==null){
         $this->errorNull = "p nul"   ;
        }else{
        $this->currentNode->addContent($paragraph);
        $this->currentNode = $paragraph;
        }
        $this->openForContentB("p");
    }

    function p_close() {
        $this->currentNode = $this->currentNode->getOwner();
        $this->closeForContentB("p");
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
        $this->openForContentB(StructuredNodeDoc::UNORDERED_LIST_TYPE);
    }

    function listu_close() {
        $this->currentNode = $this->currentNode->getOwner();
        $this->closeForContentB(StructuredNodeDoc::UNORDERED_LIST_TYPE);
    }

    function listo_open() {
        $node = new StructuredNodeDoc(StructuredNodeDoc::ORDERED_LIST_TYPE);
        $this->currentNode->addContent($node);
        $this->currentNode = $node;
        $this->openForContentB(StructuredNodeDoc::ORDERED_LIST_TYPE);
    }

    function listo_close() {
        $this->currentNode = $this->currentNode->getOwner();
        $this->closeForContentB(StructuredNodeDoc::ORDERED_LIST_TYPE);
    }

    function listitem_open($level, $node=false) {
        $node = new ListItemNodeDoc($level);
        $this->currentNode->addContent($node);
        $this->currentNode = $node;
    }

    function listitem_close() {
        $this->currentNode = $this->currentNode->getOwner();
    }

    function unformatted($text) {
        $this->currentNode->addContent(new TextNodeDoc(TextNodeDoc::UNFORMATED_TEXT_TYPE, $this->_xmlEntities($text)));
    }

    function code($text, $language="", $filename=null) {
        $this->currentNode->addContent(new CodeNodeDoc(CodeNodeDoc::CODE_TEXT_TYPE, $text, $language));
    }

    function php($text, $wrapper="code") {
        global $conf;
        if ($conf['phpok']){
            ob_start();
            eval($text);
            $next = ob_get_contents();
            ob_end_clean();
            $this->currentNode->addContent(new TextNodeDoc(TextNodeDoc::HTML_TEXT_TYPE, $next));
        } else {
            $next = p_xhtml_cached_geshi($text, 'php', $wrapper);
            $this->currentNode->addContent(new CodeNodeDoc(CodeNodeDoc::CODE_TEXT_TYPE, $next, "php"));
        }
    }

    function phpblock($text) {
        $this->php($text, 'pre');
    }

    function html($text, $wrapper="code") {
        global $conf;
        if ($conf['htmlok']){
            $next = $text;
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
        $this->openForContentB(StructuredNodeDoc::QUOTE_TYPE);
    }

    function quote_close() {
        $this->currentNode = $this->currentNode->getOwner();
        $this->closeForContentB(StructuredNodeDoc::QUOTE_TYPE);
    }

    function singlequoteopening() {
        $this->currentNode->addContent(new LeafNodeDoc(LeafNodeDoc::OP_SINGLEQUOTE_TYPE));
//        $node = new StructuredNodeDoc(StructuredNodeDoc::SINGLEQUOTE_TYPE);
//        $this->currentNode->addContent($node);
//        $this->currentNode = $node;
    }

    function singlequoteclosing() {
        $this->currentNode->addContent(new LeafNodeDoc(LeafNodeDoc::CL_SINGLEQUOTE_TYPE));
//        if($this->currentNode->type==StructuredNodeDoc::SINGLEQUOTE_TYPE){
//            $this->currentNode = $this->currentNode->getOwner();
//        }else{
//            $this->apostrophe();
//        }
    }

    function apostrophe() {
        $this->currentNode->addContent(new LeafNodeDoc(LeafNodeDoc::APOSTROPHE_TYPE));
    }

    function acronym($acronym) {
        $node = new LeafNodeDoc(LeafNodeDoc::ACRONYM_TYPE);
        $node->convertAcronym($acronym, $this->acronyms[$acronym]);
        $this->currentNode->addContent($node);
    }

    function smiley($smiley) {
        $node = new SmileyNodeDoc($this->smileys[$smiley]);
        $this->currentNode->addContent($node);
    }

    function doublequoteopening() {
        $node = new StructuredNodeDoc(StructuredNodeDoc::DOUBLEQUOTE_TYPE);
        $this->currentNode->addContent($node);
        $this->currentNode = $node;
    }

    function doublequoteclosing() {
        if($this->currentNode->type == StructuredNodeDoc::DOUBLEQUOTE_TYPE){
            $this->currentNode = $this->currentNode->getOwner();
        }else{
            $this->currentNode->addContent(new LeafNodeDoc(LeafNodeDoc::DOUBLEAPOSTROPHE_TYPE));
        }
    }

    //Es un link a un ID de la propia página. Ejemplo: <a href="#id_top">
    function locallink($hash, $name = null){
        $hash = urldecode($hash);
        $this->currentNode->addContent(new ReferenceNodeDoc($hash, ReferenceNodeDoc::REF_INTERNAL_LINK, IocCommon::formatTitleExternalLink("link", "pdf", $name)));
    }

    // $link like 'wiki:syntax', $title could be an array (media)
    function internallink($link, $title = null) {
        $this->currentNode->addContent(new ReferenceNodeDoc($link, ReferenceNodeDoc::REF_WIKI_LINK, IocCommon::formatTitleExternalLink("link", "pdf", $title)));
    }

    function externallink($link, $title = null) {
        $link = urldecode($link);
        $this->currentNode->addContent(new ReferenceNodeDoc($link, ReferenceNodeDoc::REF_EXTERNAL_LINK, IocCommon::formatTitleExternalLink("link", "pdf", $title)));
//        if (is_array($title)) {
//            //is a image
//        }
    }

    //Es una imagen definida como, por ejemplo: {{:common:chip.png?100|mostra de chip en circuit}}
    function internalmedia ($src, $title=null, $align=null, $width=null, $height=null, $cache=null, $linking=null) {
                global $conf;
        list($ext,$mime) = mimetype($src);
        $type = substr($mime,0,5);
        if($type === 'image'){
            $node = new ImageNodeDoc($src, IocCommon::formatTitleExternalLink("media", "pdf", $title), $align, $width, $height, $cache, $linking);
            $this->currentNode->addContent($node);
        }elseif($type === 'appli' && !$_SESSION['u0']){
            if (preg_match('/\.pdf$/', $src)){
                $src = $this->_xmlEntities(DOKU_URL.'lib/exe/fetch.php?media='.$src);
                $title = IocCommon::formatTitleExternalLink("file", "pdf", $title);
                $this->currentNode->addContent(new ReferenceNodeDoc($src, ReferenceNodeDoc::REF_EXTERNAL_LINK, IocCommon::formatTitleExternalLink("file", "pdf", $title)));
            }
        }else{
            if (!$_SESSION['u0']){
                $this->currentNode->addContent(new TextNodeDoc(TextNodeDoc::HTML_TEXT_TYPE, 'FIXME internalmedia ('.$type.'): '.$src));
            }
        }
    }

    function externalmedia ($src, $title=null, $align=null, $width=null, $height=null, $cache=null, $linking=null) {
//        list($src, $hash) = explode('#',$src,2);
        //NOT AVAILABLE

    }

    function table_open($maxcols = NULL, $numrows = NULL, $pos = NULL){
        $isBorderType = $this->_isBorderTypeTable();
        $node = new TableNodeDoc(TableNodeDoc::TABLE_TYPE, $isBorderType, $maxcols, $numrows, $pos);
        $this->currentNode->addContent($node);
        $this->currentNode = $node;
    }

    function table_close($pos = null){
        $this->currentNode = $this->currentNode->getOwner();
    }

    function tablerow_open(){
        $node = new RowNodeDoc(StructuredNodeDoc::TABLEROW_TYPE);
        $this->currentNode->addContent($node);
        $this->currentNode = $node;
    }

    function tablerow_close(){
        $this->currentNode = $this->currentNode->getOwner();
    }

    function tableheader_open($colspan = 1, $align = null, $rowspan = 1){
        $isBorderType = $this->_isBorderTypeTable();
        $node = new CellNodeDoc(CellNodeDoc::TABLEHEADER_TYPE, $colspan, $align, $rowspan, $isBorderType);
        $this->currentNode->addContent($node);
        $this->currentNode = $node;
    }

    function tableheader_close(){
        $this->currentNode = $this->currentNode->getOwner();
    }

    function tablecell_open($colspan = 1, $align = null, $rowspan = 1){
        $isBorderType = $this->_isBorderTypeTable();
        $node = new CellNodeDoc(CellNodeDoc::TABLECELL_TYPE, $colspan, $align, $rowspan, $isBorderType);
        $this->currentNode->addContent($node);
        $this->currentNode = $node;
    }

    function tablecell_close(){
        $this->currentNode = $this->currentNode->getOwner();
    }

    function _xmlEntities($string) {
        return htmlspecialchars($string,ENT_QUOTES,'UTF-8');
    }

    private function _isBorderTypeTable($types=NULL){
        if($types==NULL){
            $types = is_array($this->table_types) ? $this->table_types : array();
        }
        return count(array_intersect($types, self::BORDER_TYPES))!=0;
    }

    public function isBorderTypeTable($types=NULL){
        return $this->_isBorderTypeTable($types);
    }

    public function setTableTypes($types=""){
        if (is_string($types)) {
            $atypes = preg_split('/(\s*,\s*)*,+(\s*,\s*)*/', trim(str_replace("\t", "    ", $types)));
        }elseif(is_array($types)) {
            $atypes = $types;
        }else {
            $atypes = [[]];
        }
        $this->table_types = $atypes;
    }

    public function getCurrentNode(){
        return $this->currentNode;
    }

    public function setCurrentNode($node){
        return $this->currentNode = $node;
    }

    public function storeCurrent(){
        $this->storeNode[] = $this->currentNode;
    }

    public function restoreCurrent(){
        $this->currentNode = array_pop($this->storeNode);
    }

    private function _media ($src, $title=null, $align=null, $width=null, $height=null) {

    }

    public function openForContentB($origin){
        //Permet la insercció dels iocElemns de la columna B en el següent contenidor de text,
        //ja que a la versió WEB No hi ha columna B. Per tal de renderitzar correctament la coluna B
        //al render XHTML i PDF, el seu contingut es troba sempre per sobre del paràgraf al que fa referècia.
        //És  necessari baixar-lo un paràgraf en aquest renderer.
        if(!isset($this->tmpData["origin"])){
            if($this->tmpData["renderIocElems"]){
                $this->tmpData["renderDefaultIocElems"] = TRUE;
            }
            $this->tmpData["origin"] = $origin;
        }
    }

    public function closeForContentB($origin){
        //Permet la insercció dels iocElemns de la columna B en el següent contenidor de text,
        //ja que a la versió WEB No hi ha columna B. Per tal de renderitzar correctament la coluna B
        //al render XHTML i PDF, el seu contingut es troba sempre per sobre del paràgraf l que fa referècia.
        //És  necessari baixar-lo un paràgraf en aquest renderer.
        if($this->tmpData["origin"]===$origin){
            if(!empty($this->bIocElemsRefQueue)){
                while($this->bIocElemsRefQueue[0]){
                    $id = array_shift($this->bIocElemsRefQueue);
                    $node = $this->bIocElems[self::REFERRED_B_IOC_ELEMS_TYPE][$id];
                    $this->currentNode->addContent($node);
                }
            }
            if(isset($this->tmpData["renderDefaultIocElems"]) && $this->tmpData["renderDefaultIocElems"]){
                while($this->bIocElems[self::UNREFERRED_B_IOC_ELEMS_TYPE][0]){
                    $node = array_shift($this->bIocElems[self::UNREFERRED_B_IOC_ELEMS_TYPE]);
                    $this->currentNode->addContent($node);
                }
                $this->tmpData["renderIocElems"] = FALSE;
                $this->tmpData["renderDefaultIocElems"]=FALSE;
            }
            unset($this->tmpData["origin"]);
        }
    }
}