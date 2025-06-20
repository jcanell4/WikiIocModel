<?php
/**
 * Wikiioc Basic Renderer for XHTML output
 *
 * @author Harry Fuecks <hfuecks@gmail.com>
 * @author Andreas Gohr <andi@splitbrain.org>
 */
if (!defined('DOKU_INC')) die('meh.');
if (!defined('DOKU_LF')) define ('DOKU_LF',"\n");
if (!defined('DOKU_TAB')) define ('DOKU_TAB',"\t");
require_once DOKU_INC."inc/parser/renderer.php";
require_once DOKU_INC."inc/html.php";

//[TODO:Josep]revisar els canvis que hi ha entre aquet renderer i el iocxhtml de iocexporterl

class renderer_plugin_wikiiocmodel_ptxhtml extends Doku_Renderer {
    const UNEXISTENT_B_IOC_ELEMS_TYPE = -1;
    const REFERRED_B_IOC_ELEMS_TYPE = 0;
    const UNREFERRED_B_IOC_ELEMS_TYPE = 1;

    var $doc = '';        // will contain the whole document
    var $toc = array();   // will contain the Table of Contents
    var $sectionedits = array(); // A stack of section edit data
    var $headers = array();
    var $footnotes = array();
    var $lastlevel = 0;
    var $node = array(0,0,0,0,0);
    var $store = '';
    var $_counter   = array(); // used as global counter, introduced for table classes
    var $_codeblock = 0;       // counts the code and file blocks, used to provide download links
    var $sectionElement = TRUE;

    private $lastsecid = 0; // last section edit id, used by startSectionEdit

    var $tmpData=array();

    var $storeForElems = NULL;
    var $bIocElems = array(array(),  array());
    var $currentBIocElemsType = self::UNEXISTENT_B_IOC_ELEMS_TYPE;
    var $bIocElemsRefQueue = array();
    var $levelDiff=0;

    public function __construct() {
        if(isset($_SESSION['sectionElement'])){
            $this->sectionElement=$_SESSION['sectionElement'];
        }
    }

    /**
     * Esta función construye el renderer a partir de las parámetros de configuración recibidos
     * @param array $params
     */
    public function init($params) {}

    function reset(){
        $this->doc = '';
    }

    /**
     * Register a new edit section range
     *
     * @param int    $start The byte position for the edit start
     * @param string $type  The section type identifier
     * @param string $title The section title
     * @return string A marker class for the starting HTML element
     * @author Adrian Lang <lang@cosmocode.de>
     */
    public function startSectionEdit($start, $type, $title = null) {
        $this->sectionedits[] = array(++$this->lastsecid, $start, $type, $title);
        return 'sectionedit' . $this->lastsecid;
    }

    /**
     * Finish an edit section range
     *
     * @param $end int The byte position for the edit end; null for the rest of the page
     * @author Adrian Lang <lang@cosmocode.de>
     */
    public function finishSectionEdit($end = null) {
        if($this->sectionElement){
            $this->doc .= "</section>\n";
        }
        list($id, $start, $type, $title) = array_pop($this->sectionedits);
        if (is_null($end) || $end > $start) {
            $this->doc .= "<!-- EDIT$id " . @strtoupper($type) . ' ';
            if (!is_null($title)) {
                $this->doc .= '"' . str_replace('"', '', $title) . '" ';
            }
            $this->doc .= "[$start-" . (is_null($end) ? '' : $end) . '] -->';
        }
    }

    function getFormat(){
        return 'wikiiocmodel_ptxhtml';
    }

    function document_start() {
        //reset some internals
        $this->toc     = array();
        $this->headers = array();
    }

    function document_end() {
        // Finish open section edits.
        while (count($this->sectionedits) > 0) {
            if ($this->sectionedits[count($this->sectionedits) - 1][1] <= 1) {
                // If there is only one section, do not write a section edit marker.
                array_pop($this->sectionedits);
            } else {
                $this->finishSectionEdit();
            }
        }

        if ( count ($this->footnotes) > 0 ) {
            $this->doc .= '<div class="footnotes">'.DOKU_LF;

            foreach ( $this->footnotes as $id => $footnote ) {
                // check its not a placeholder that indicates actual footnote text is elsewhere
                if (substr($footnote, 0, 5) != "@@FNT") {

                    // open the footnote and set the anchor and backlink
                    $this->doc .= '<div class="fn">';
                    $this->doc .= '<sup><a href="#fnt__'.$id.'" id="fn__'.$id.'" class="fn_bot">';
                    $this->doc .= $id.')</a></sup> '.DOKU_LF;

                    // get any other footnotes that use the same markup
                    $alt = array_keys($this->footnotes, "@@FNT$id");

                    if (count($alt)) {
                        foreach ($alt as $ref) {
                            // set anchor and backlink for the other footnotes
                            $this->doc .= ', <sup><a href="#fnt__'.($ref).'" id="fn__'.($ref).'" class="fn_bot">';
                            $this->doc .= ($ref).')</a></sup> '.DOKU_LF;
                        }
                    }

                    // add footnote markup and close this footnote
                    $this->doc .= $footnote;
                    $this->doc .= '</div>' . DOKU_LF;
                }
            }
            $this->doc .= '</div>'.DOKU_LF;
        }

        $this->info["tocItems"] = $this->toc;

        // make sure there are no empty paragraphs
        $this->doc = preg_replace('#<p>\s*</p>#', '', $this->doc);
    }

    function toc_additem($id, $text, $level) {
        global $conf;

        //handle TOC
        if ($level >= $conf['toptoclevel'] && $level <= $conf['maxtoclevel']){
            $this->toc[] = html_mktocitem($id, $text, $level-$conf['toptoclevel']+1);
        }
    }

    function header($text, $level, $pos) {
        global $conf;

        if (!$text) return; //skip empty headlines

        $hid = $this->_headerToLink($text,true);

        //only add items within configured levels
        $this->toc_additem($hid, $text, $level);

        // adjust $node to reflect hierarchy of levels
        $this->node[$level-1]++;
        if ($level < $this->lastlevel) {
            for ($i = 0; $i < $this->lastlevel-$level; $i++) {
                $this->node[$this->lastlevel-$i-1] = 0;
            }
        }
        $this->lastlevel = $level;

        if ($level <= $conf['maxseclevel'] &&
            count($this->sectionedits) > 0 &&
            $this->sectionedits[count($this->sectionedits) - 1][2] === 'section') {
            $this->finishSectionEdit($pos - 1);
        }

        // write the header
        if($this->sectionElement){
            $this->doc .= DOKU_LF.'<section id="'.$hid.'">';
        }
        $this->doc .= DOKU_LF.'<h'.$level;
        if ($level <= $conf['maxseclevel']) {
            $this->doc .= ' class="' . $this->startSectionEdit($pos, 'section', $text) . '"';
        }
        $this->doc .= '>';            //$this->doc .= ' id="'.$hid.'">';
        if(!$this->sectionElement){
            $this->doc .= '<a id="'.$hid.'">';
        }
        $this->doc .= $this->_xmlEntities($text);
        if(!$this->sectionElement){
            $this->doc .= '</a>';
        }
        $this->doc .= "</h$level>".DOKU_LF;
    }

    // **************************
    // reduced list of TAG sintax
    // **************************
    function section_open($level) {
        $this->doc .= '<div class="level' . $level . '">' . DOKU_LF;
    }

    function section_close() {
        $this->doc .= DOKU_LF.'</div>'.DOKU_LF;
    }

    function cdata($text) {
        $d = $this->_xmlEntities($text);
        $this->doc .= $d;
    }

    function p_open() {
        $this->doc .= DOKU_LF.'<p>'.DOKU_LF;
        $this->openForContentB("p");
    }

    function p_close() {
        $this->doc .= DOKU_LF.'</p>'.DOKU_LF;
        $this->closeForContentB("p");
    }

    function linebreak() {
        $this->doc .= '<br/>'.DOKU_LF;
    }

    function hr() {
        //$this->doc .= '<hr />'.DOKU_LF;
    }

    function strong_open() {
        $this->doc .= '<strong>';
    }

    function strong_close() {
        $this->doc .= '</strong>';
    }

    function emphasis_open() {
        $this->doc .= '<em>';
    }

    function emphasis_close() {
        $this->doc .= '</em>';
    }

    function underline_open() {
        $this->doc .= '<u>';
    }

    function underline_close() {
        $this->doc .= '</u>';
    }

    function monospace_open() {
        $this->doc .= '<code>';
    }

    function monospace_close() {
        $this->doc .= '</code>';
    }

    function subscript_open() {
        $this->doc .= '<sub>';
    }

    function subscript_close() {
        $this->doc .= '</sub>';
    }

    function superscript_open() {
        $this->doc .= '<sup>';
    }

    function superscript_close() {
        $this->doc .= '</sup>';
    }

    function deleted_open() {
        $this->doc .= '<del>';
    }

    function deleted_close() {
        $this->doc .= '</del>';
    }

    /**
     * Callback for footnote start syntax
     * All following content will go to the footnote instead of the document.
     * To achieve this the previous rendered content is moved to $store and $doc is cleared
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    function footnote_open() {
        // move current content to store and record footnote
        $this->store = $this->doc;
        $this->doc   = '';
    }

    /**
     * Callback for footnote end syntax
     * All rendered content is moved to the $footnotes array and the old
     * content is restored from $store again
     *
     * @author Andreas Gohr
     */
    function footnote_close() {
        /** @var $fnid int takes track of seen footnotes, assures they are unique even across multiple docs FS#2841 */
        static $fnid = 0;
        // assign new footnote id (we start at 1)
        $fnid++;

        // recover footnote into the stack and restore old content
        $footnote = $this->doc;
        $this->doc = $this->store;
        $this->store = '';

        // check to see if this footnote has been seen before
        $i = array_search($footnote, $this->footnotes);

        if ($i === false) {
            // its a new footnote, add it to the $footnotes array
            $this->footnotes[$fnid] = $footnote;
        } else {
            // seen this one before, save a placeholder
            $this->footnotes[$fnid] = "@@FNT".($i);
        }

        // output the footnote reference and link
        $this->doc .= '<sup><a href="#fn__'.$fnid.'" id="fnt__'.$fnid.'" class="fn_top">'.$fnid.')</a></sup>';
    }

    function listu_open() {
        $this->doc .= '<ul>'.DOKU_LF;
        $this->openForContentB("ul");
    }

    function listu_close() {
        $this->doc .= '</ul>'.DOKU_LF;
        $this->closeForContentB("ul");
    }

    function listo_open() {
        $this->doc .= '<ol>'.DOKU_LF;
        $this->openForContentB("ol");
    }

    function listo_close() {
        $this->doc .= '</ol>'.DOKU_LF;
        $this->closeForContentB("ol");
    }

    function listitem_open($level, $node=false) {
        $this->doc .= '<li class="level'.$level.'">';
    }

    function listitem_close() {
        $this->doc .= '</li>'.DOKU_LF;
    }

        function listcontent_open() {
        $this->doc .= '<div class="li">';
    }

    function listcontent_close() {
        $this->doc .= '</div>'.DOKU_LF;
    }

    function unformatted($text) {
        $this->doc .= $this->_xmlEntities($text);
    }

    /**
     * Execute PHP code if allowed
     *
     * @param  string   $text      PHP code that is either executed or printed
     * @param  string   $wrapper   html element to wrap result if $conf['phpok'] is okff
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    function php($text, $wrapper='code') {
        global $conf;

        if ($conf['phpok']){
            ob_start();
            eval($text);
            $this->doc .= ob_get_contents();
            ob_end_clean();
        } else {
            $this->doc .= p_xhtml_cached_geshi($text, 'php', $wrapper);
        }
    }

    function phpblock($text) {
        $this->php($text, 'pre');
    }

    /**
     * Insert HTML if allowed
     *
     * @param  string   $text      html text
     * @param  string   $wrapper   html element to wrap result if $conf['htmlok'] is okff
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    function html($text, $wrapper='code') {
        global $conf;

        if ($conf['htmlok']){
            $this->doc .= $text;
        } else {
            $this->doc .= p_xhtml_cached_geshi($text, 'html4strict', $wrapper);
        }
    }

    function htmlblock($text) {
        $this->html($text, 'pre');
    }

    function quote_open() {
        $this->doc .= '<blockquote><div class="no">'.DOKU_LF;
        $this->openForContentB("blockquote");
    }

    function quote_close() {
        $this->doc .= '</div></blockquote>'.DOKU_LF;
        $this->closeForContentB("blockquote");
    }

    function preformatted($text) {
        $this->doc .= '<pre class="code">' . trim($this->_xmlEntities($text),"\n\r") . '</pre>'. DOKU_LF;
    }

    function file($text, $language=null, $filename=null) {
        $this->_highlight('file',$text,$language,$filename);
    }

    function code($text, $language=null, $filename=null) {
        $this->_highlight('code',$text,$language,$filename);
    }

    /**
     * Use GeSHi to highlight language syntax in code and file blocks
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    function _highlight($type, $text, $language=null, $filename=null) {
        global $ID;
        global $lang;

        if ($filename){
            // add icon
            list($ext) = mimetype($filename,false);
            $class = preg_replace('/[^_\-a-z0-9]+/i','_',$ext);
            $class = 'mediafile mf_'.$class;

            $this->doc .= '<dl class="'.$type.'">'.DOKU_LF;
            $this->doc .= '<dt><a href="'.exportlink($ID,'code',array('codeblock'=>$this->_codeblock)).'" title="'.$lang['download'].'" class="'.$class.'">';
            $this->doc .= hsc($filename);
            $this->doc .= '</a></dt>'.DOKU_LF.'<dd>';
        }

        if ($text{0} == "\n") {
            $text = substr($text, 1);
        }
        if (substr($text, -1) == "\n") {
            $text = substr($text, 0, -1);
        }

        if ( is_null($language) ) {
            $this->doc .= '<pre class="'.$type.'">'.$this->_xmlEntities($text).'</pre>'.DOKU_LF;
        } else {
            $class = 'code'; //we always need the code class to make the syntax highlighting apply
            if ($type != 'code') $class .= ' '.$type;

            $this->doc .= "<pre class=\"$class $language\">".p_xhtml_cached_geshi($text, $language, '').'</pre>'.DOKU_LF;
        }

        if ($filename){
            $this->doc .= '</dd></dl>'.DOKU_LF;
        }

        $this->_codeblock++;
    }

    function acronym($acronym) {
        if ( array_key_exists($acronym, $this->acronyms) ) {
            $title = $this->_xmlEntities($this->acronyms[$acronym]);
            $this->doc .= '<abbr title="'.$title .'">'.$this->_xmlEntities($acronym).'</abbr>';
        } else {
            $this->doc .= $this->_xmlEntities($acronym);
        }
    }

    function smiley($smiley) {
        if ( array_key_exists($smiley, $this->smileys) ) {
            $this->doc .= '<img src="'.DOKU_BASE.'lib/images/smileys/'.$this->_xmlEntities($this->smileys[$smiley]).
                '" class="icon" alt="'.$this->_xmlEntities($smiley).'" />';
        } else {
            $this->doc .= $this->_xmlEntities($smiley);
        }
    }

    function entity($entity) {
        if ( array_key_exists($entity, $this->entities) ) {
            $this->doc .= $this->entities[$entity];
        } else {
            $this->doc .= $this->_xmlEntities($entity);
        }
    }

    function multiplyentity($x, $y) {
        $this->doc .= "$x&times;$y";
    }

    function singlequoteopening() {
        global $lang;
        $this->doc .= $lang['singlequoteopening'];
    }

    function singlequoteclosing() {
        global $lang;
        $this->doc .= $lang['singlequoteclosing'];
    }

    function apostrophe() {
        global $lang;
        $this->doc .= $lang['apostrophe'];
    }

    function doublequoteopening() {
        global $lang;
        $this->doc .= $lang['doublequoteopening'];
    }

    function doublequoteclosing() {
        global $lang;
        $this->doc .= $lang['doublequoteclosing'];
    }

    /**
     */
    function camelcaselink($link) {
        $this->internallink($link,$link);
    }


    function locallink($hash, $name = null){
        global $ID;
        $name  = $this->_getLinkTitle($name, $hash, $isImage);
        $hash  = $this->_headerToLink($hash);
        $title = $ID.' ↵';
        $this->doc .= '<a href="#'.$hash.'" title="'.$title.'" class="wikilink1">';
        $this->doc .= $name;
        $this->doc .= '</a>';
    }

    /**
     * Render an internal Wiki Link
     *
     * $search,$returnonly & $linktype are not for the renderer but are used
     * elsewhere - no need to implement them in other renderers
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    function internallink($id, $name = null, $search=null,$returnonly=false,$linktype='content') {
        global $conf;
        global $ID;
        global $INFO;

        $params = '';
        $parts = explode('?', $id, 2);
        if (count($parts) === 2) {
            $id = $parts[0];
            $params = $parts[1];
        }

        // For empty $id we need to know the current $ID
        // We need this check because _simpleTitle needs
        // correct $id and resolve_pageid() use cleanID($id)
        // (some things could be lost)
        if ($id === '') {
            $id = $ID;
        }

        // default name is based on $id as given
        $default = $this->_simpleTitle($id);

        // now first resolve and clean up the $id
        resolve_pageid(getNS($ID),$id,$exists);

        $name = $this->_getLinkTitle($name, $default, $isImage, $id, $linktype);
        if ( !$isImage ) {
            if ( $exists ) {
                $class='wikilink1';
            } else {
                $class='wikilink2';
                $link['rel']='nofollow';
            }
        } else {
            $class='media';
        }

        //keep hash anchor
        list($id,$hash) = explode('#',$id,2);
        if (!empty($hash)) $hash = $this->_headerToLink($hash);

        //prepare for formating
        $link['target'] = $conf['target']['wiki'];
        $link['style']  = '';
        $link['pre']    = '';
        $link['suf']    = '';
        // highlight link to current page
        if ($id == $INFO['id']) {
            $link['pre']    = '<span class="curid">';
            $link['suf']    = '</span>';
        }
        $link['more']   = '';
        $link['class']  = $class;
        $link['url']    = wl($id, $params);
        $link['name']   = $name;
        $link['title']  = $id;
        //add search string
        if ($search){
            ($conf['userewrite']) ? $link['url'].='?' : $link['url'].='&amp;';
            if (is_array($search)){
                $search = array_map('rawurlencode',$search);
                $link['url'] .= 's[]='.join('&amp;s[]=',$search);
            }else{
                $link['url'] .= 's='.rawurlencode($search);
            }
        }

        //keep hash
        if ($hash) $link['url'].='#'.$hash;

        //output formatted
        if ($returnonly){
            return $this->_formatLink($link);
        }else{
            $this->doc .= $this->_formatLink($link);
        }
    }

    function externallink($url, $name = null) {
        global $conf;

        $name = $this->_getLinkTitle($name, $url, $isImage);

        // url might be an attack vector, only allow registered protocols
        if (is_null($this->schemes)) $this->schemes = getSchemes();
        list($scheme) = explode('://',$url);
        $scheme = strtolower($scheme);
        if (!in_array($scheme,$this->schemes)) $url = '';

        // is there still an URL?
        if (!$url){
            $this->doc .= $name;
            return;
        }

        // set class
        if ( !$isImage ) {
            $class='urlextern';
        } else {
            $class='media';
        }

        //prepare for formating
        $link['target'] = $conf['target']['extern'];
        $link['style']  = '';
        $link['pre']    = '';
        $link['suf']    = '';
        $link['more']   = '';
        $link['class']  = $class;
        $link['url']    = $url;

        $link['name']   = $name;
        $link['title']  = $this->_xmlEntities($url);
        if ($conf['relnofollow']) $link['more'] .= ' rel="nofollow"';

        //output formatted
        $this->doc .= $this->_formatLink($link);
    }

    /**
    */
    function interwikilink($match, $name = null, $wikiName, $wikiUri) {
        global $conf;

        $link = array();
        $link['target'] = $conf['target']['interwiki'];
        $link['pre']    = '';
        $link['suf']    = '';
        $link['more']   = '';
        $link['name']   = $this->_getLinkTitle($name, $wikiUri, $isImage);

        //get interwiki URL
        $url = $this->_resolveInterWiki($wikiName,$wikiUri);

        if ( !$isImage ) {
            $class = preg_replace('/[^_\-a-z0-9]+/i','_',$wikiName);
            $link['class'] = "interwiki iw_$class";
        } else {
            $link['class'] = 'media';
        }

        //do we stay at the same server? Use local target
        if ( strpos($url,DOKU_URL) === 0 ){
            $link['target'] = $conf['target']['wiki'];
        }

        $link['url'] = $url;
        $link['title'] = htmlspecialchars($link['url']);

        //output formatted
        $this->doc .= $this->_formatLink($link);
    }

    /**
     */
    function windowssharelink($url, $name = null) {
        global $conf;
        //simple setup
        $link['target'] = $conf['target']['windows'];
        $link['pre']    = '';
        $link['suf']   = '';
        $link['style']  = '';

        $link['name'] = $this->_getLinkTitle($name, $url, $isImage);
        if ( !$isImage ) {
            $link['class'] = 'windows';
        } else {
            $link['class'] = 'media';
        }

        $link['title'] = $this->_xmlEntities($url);
        $url = str_replace('\\','/',$url);
        $url = 'file:///'.$url;
        $link['url'] = $url;

        //output formatted
        $this->doc .= $this->_formatLink($link);
    }

    function emaillink($address, $name = null) {
        global $conf;
        //simple setup
        $link = array();
        $link['target'] = '';
        $link['pre']    = '';
        $link['suf']   = '';
        $link['style']  = '';
        $link['more']   = '';

        $name = $this->_getLinkTitle($name, '', $isImage);
        if ( !$isImage ) {
            $link['class']='mail';
        } else {
            $link['class']='media';
        }

        $address = $this->_xmlEntities($address);
        $address = obfuscate($address);
        $title   = $address;

        if (empty($name)){
            $name = $address;
        }

        if ($conf['mailguard'] == 'visible') $address = rawurlencode($address);

        $link['url']   = 'mailto:'.$address;
        $link['name']  = $name;
        $link['title'] = $title;

        //output formatted
        $this->doc .= $this->_formatLink($link);
    }

    function internalmedia ($src, $title=null, $align=null, $width=null, $height=null, $cache=null, $linking=null) {
        global $ID;
        list($src) = explode('#', $src, 2);
        resolve_mediaid(getNS($ID), $src, $exists);

        $render = ($linking == 'linkonly') ? false : true;
        $link = $this->_getMediaLinkConf($src, $ID, $title, $align, $width, $height, $cache, $render);

        $this->doc .= $link['name'];
    }

    function externalmedia ($src, $title=null, $align=null, $width=null, $height=null, $cache=null, $linking=null) {
        list($src,$hash) = explode('#',$src,2);
        $noLink = false;
        $render = ($linking == 'linkonly') ? false : true;
        $link = $this->_getMediaLinkConf($src, "", $title, $align, $width, $height, $cache, $render);

        $link['url']    = ml($src,array('cache'=>$cache));

        list($ext,$mime,$dl) = mimetype($src,false);
        if (substr($mime,0,5) == 'image' && $render){
            // link only jpeg images
            // if ($ext != 'jpg' && $ext != 'jpeg') $noLink = true;
        }elseif ($mime == 'application/x-shockwave-flash' && $render){
            // don't link flash movies
            $noLink = true;
        }else{
            // add file icons
            $class = preg_replace('/[^_\-a-z0-9]+/i','_',$ext);
            $link['class'] .= ' mediafile mf_'.$class;
        }

        if ($hash) $link['url'] .= '#'.$hash;

        //output formatted
        if ($linking == 'nolink' || $noLink)
            $this->doc .= $link['name'];
        else
            $this->doc .= $this->_formatLink($link);
    }

    /**
     * Renders an RSS feed
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    function rss ($url,$params){
        global $lang;
        global $conf;

        require_once(DOKU_INC.'inc/FeedParser.php');
        $feed = new FeedParser();
        $feed->set_feed_url($url);

        //disable warning while fetching
        if (!defined('DOKU_E_LEVEL')) { $elvl = error_reporting(E_ERROR); }
        $rc = $feed->init();
        if (!defined('DOKU_E_LEVEL')) { error_reporting($elvl); }

        //decide on start and end
        if ($params['reverse']){
            $mod = -1;
            $start = $feed->get_item_quantity()-1;
            $end   = $start - ($params['max']);
            $end   = ($end < -1) ? -1 : $end;
        }else{
            $mod   = 1;
            $start = 0;
            $end   = $feed->get_item_quantity();
            $end   = ($end > $params['max']) ? $params['max'] : $end;
        }

        $this->doc .= '<ul class="rss">';
        if ($rc){
            for ($x = $start; $x != $end; $x += $mod) {
                $item = $feed->get_item($x);
                $this->doc .= '<li><div class="li">';
                // support feeds without links
                $lnkurl = $item->get_permalink();
                if ($lnkurl){
                    // title is escaped by SimplePie, we unescape here because it
                    // is escaped again in externallink() FS#1705
                    $this->externallink($item->get_permalink(),
                                        html_entity_decode($item->get_title(), ENT_QUOTES, 'UTF-8'));
                }else{
                    $this->doc .= ' '.$item->get_title();
                }
                if ($params['author']){
                    $author = $item->get_author(0);
                    if ($author){
                        $name = $author->get_name();
                        if (!$name) $name = $author->get_email();
                        if ($name) $this->doc .= ' '.$lang['by'].' '.$name;
                    }
                }
                if ($params['date']){
                    $this->doc .= ' ('.$item->get_local_date($conf['dformat']).')';
                }
                if ($params['details']){
                    $this->doc .= '<div class="detail">';
                    if ($conf['htmlok']){
                        $this->doc .= $item->get_description();
                    }else{
                        $this->doc .= strip_tags($item->get_description());
                    }
                    $this->doc .= '</div>';
                }

                $this->doc .= '</div></li>';
            }
        }else{
            $this->doc .= '<li><div class="li">';
            $this->doc .= '<em>'.$lang['rssfailed'].'</em>';
            $this->externallink($url);
            if ($conf['allowdebug']){
                $this->doc .= '<!--'.hsc($feed->error).'-->';
            }
            $this->doc .= '</div></li>';
        }
        $this->doc .= '</ul>';
    }

    // $numrows not yet implemented
    function table_open($maxcols = null, $numrows = null, $pos = null){
        // initialize the row counter used for classes
        $this->_counter['row_counter'] = 0;
        $class = 'table';
        if ($pos !== null) {
            $class .= ' ' . $this->startSectionEdit($pos, 'table');
        }
        $this->doc .= '<div class="'.$class.'"><table class="taula inline">' . DOKU_LF;
    }

    function table_close($pos = null){
        $this->doc .= '</table></div>'.DOKU_LF;
//        if ($pos !== null) {
//            $this->finishSectionEdit($pos);
//        }
    }

    function tablerow_open(){
        // initialize the cell counter used for classes
        $this->_counter['cell_counter'] = 0;
        $class = 'row' . $this->_counter['row_counter']++;
        $this->doc .= DOKU_TAB . '<tr class="'.$class.'">' . DOKU_LF . DOKU_TAB . DOKU_TAB;
    }

    function tablerow_close(){
        $this->doc .= DOKU_LF . DOKU_TAB . '</tr>' . DOKU_LF;
    }

    function tableheader_open($colspan = 1, $align = null, $rowspan = 1){       
        $class = 'class="col' . $this->_counter['cell_counter']++;
        if ( !is_null($align) ) {
            $class .= ' '.$align.'align';
        }
        $class .= '"';
        $this->doc .= '<th ' . $class;
        if ( $colspan > 1 ) {
            $this->_counter['cell_counter'] += $colspan-1;
            $this->doc .= ' colspan="'.$colspan.'"';
        }
        if ( $rowspan > 1 ) {
            $this->doc .= ' rowspan="'.$rowspan.'"';
        }
        $this->doc .= '>';
    }

    function tableheader_close(){
        $this->doc .= '</th>';
    }

    function tablecell_open($colspan = 1, $align = null, $rowspan = 1){
        $class = 'class="col' . $this->_counter['cell_counter']++;
        if ( !is_null($align) ) {
            $class .= ' '.$align.'align';
        }
        $class .= '"';
        $this->doc .= '<td '.$class;
        if ( $colspan > 1 ) {
            $this->_counter['cell_counter'] += $colspan-1;
            $this->doc .= ' colspan="'.$colspan.'"';
        }
        if ( $rowspan > 1 ) {
            $this->doc .= ' rowspan="'.$rowspan.'"';
        }
        $this->doc .= '>';
    }

    function tablecell_close(){
        $this->doc .= '</td>';
    }

    //----------------------------------------------------------
    // Utils

    /**
     * Build a link
     * Assembles all parts defined in $link returns HTML for the link
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    function _formatLink($link){
        //make sure the url is XHTML compliant (skip mailto)
        if (substr($link['url'],0,7) != 'mailto:'){
            $link['url'] = str_replace('&','&amp;',$link['url']);
            $link['url'] = str_replace('&amp;amp;','&amp;',$link['url']);
        }
        //remove double encodings in titles
        $link['title'] = str_replace('&amp;amp;','&amp;',$link['title']);

        // be sure there are no bad chars in url or title
        // (we can't do this for name because it can contain an img tag)
        $link['url']   = strtr($link['url'],array('>'=>'%3E','<'=>'%3C','"'=>'%22'));
        $link['title'] = strtr($link['title'],array('>'=>'&gt;','<'=>'&lt;','"'=>'&quot;'));

        $ret  = '';
        $ret .= $link['pre'];
        $ret .= '<a href="'.$link['url'].'"';
        if (!empty($link['class']))  $ret .= ' class="'.$link['class'].'"';
        if (!empty($link['target'])) $ret .= ' target="'.$link['target'].'"';
        if (!empty($link['title']))  $ret .= ' title="'.$link['title'].'"';
        if (!empty($link['style']))  $ret .= ' style="'.$link['style'].'"';
        if (!empty($link['rel']))    $ret .= ' rel="'.$link['rel'].'"';
        if (!empty($link['more']))   $ret .= ' '.$link['more'];
        $ret .= '>';
        $ret .= IocCommon::formatTitleExternalLink("link", "html", $link['name']);
        $ret .= '</a>';
        $ret .= $link['suf'];
        return $ret;
    }

    function _isMediaFile($src){
        $pos = strrpos((string)$src,':');
        $ret = $pos!==false;
        return $ret;
    }


    /**
     * Renders internal and external media
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    function _media ($src, $parent_id="", $title=null, $align=null, $width=null, $height=null, $cache=null, $render = true) {
        $ret = '';
        //attach url media file
        if($this->_isMediaFile($src)){
            array_push($_SESSION['media_files'], $src);
        }

        list($ext, $mime, $dl) = mimetype($src);
        if (substr($mime,0,5) == 'image'){
            $icon = FALSE;
            if ($width || $height){
                $icon = (($width && $width < 49) || ($height && $height < 49));
            }
            $imgb = (!$icon && !$this->table);
            // first get the $title
            if (!is_null($title)) {
                $title  = $this->_xmlEntities($title);
            }elseif ($ext == 'jpg' || $ext == 'jpeg'){
                //try to use the caption from IPTC/EXIF
                require_once(DOKU_INC.'inc/JpegMeta.php');
                $jpeg = new JpegMeta(mediaFN($src));
                if ($jpeg !== false)
                    $cap = $jpeg->getTitle();
                if ($cap)
                    $title = $this->_xmlEntities($cap);
            }
            if (!$render) {
                // if the picture is not supposed to be rendered
                // return the title of the picture
                if (!$title) {
                    // just show the sourcename
                    $title = $this->_xmlEntities(utf8_basename(noNS($src)));
                }
                return $title;
            }
            if ($_SESSION['figure']){
                $ret .= '<figure>'.DOKU_LF;
                $figtitle = '<span class="figuretitle">Figura</span>'.$_SESSION['fig_title'];
                $ret .= '<figcaption>'.$figtitle.'</figcaption>';
            }elseif($_SESSION['iocelem']){
                $ret .= '<div class="imgelem">'.DOKU_LF;
            }elseif($imgb){
                $ret .= '<div class="iocfigurec">'.DOKU_LF;
                $ret .= '<ul>'.DOKU_LF;
                $ret .= '<li>'.DOKU_LF;
            }
            //add image tag
            //versión anterior que eliminaba la wikiruta del archivo
            //$ret .= '<img src="img/'.basename(str_replace(':', '/', $src)).'"';
            $ret .= '<img src="img/'.str_replace(':', '/', $src).'"';

            if ($icon || $_SESSION['figure'] || $_SESSION['iocelem'] || $this->table){
                $ret .= ' class="media'.$align.'"';
            }else{
                $ret .= ' class="imgB'.$align.'"';
            }

              // make left/right alignment for no-CSS view work (feeds)
            if($align == 'right') $ret .= ' align="right"';
            if($align == 'left')  $ret .= ' align="left"';

            $alt = "";
            if ($title) {
                if ($imgb) {
                    $titol = IocCommon::formatTitleExternalLink("media", "html", $title);
                    if (is_array($titol)) {
                        $title = $titol['title'];
                        $alt = $titol['alt'];
                    }else {
                        $title = $alt = $titol;
                    }
                }
                $ret .= " title=\"$title\"";
            }
            $alt = ($_SESSION['fig_description']) ? $_SESSION['fig_description'] : $alt;
            $ret .= " alt=\"$alt\"";
            //marjose start
            //if($this->table && !is_null($width) )
            if(!is_null($width) )
                $ret .= ' width="'.$this->_xmlEntities($width).'"';

            //if ($this->table && !is_null($height) )
            if(!is_null($width) )
                $ret .= ' height="'.$this->_xmlEntities($height).'"';
            //marjose end
            $ret .= ' />';

            if ($_SESSION['figure']){
                $ret .= '</figure>'.DOKU_LF;
            }elseif($_SESSION['iocelem']){
                $ret .= '</div>'.DOKU_LF;
            }elseif($imgb){
                $ret .= '</li>';
                if ($title) {
                    $title = preg_replace('/\/[+-]?\d+$/', '', $title);
                    $ret .= '<li><small>'.$title.'</small></li>'.DOKU_LF;
                }
                $ret .= '</ul>';
                $ret .= '</div>'.DOKU_LF;
            }

        }
        elseif ($mime == 'application/x-shockwave-flash'){
            if (!$render) {
                // if the flash is not supposed to be rendered
                // return the title of the flash
                if (!$title) {
                    // just show the sourcename
                    $title = utf8_basename(noNS($src));
                }
                return $this->_xmlEntities($title);
            }

            $att = array();
            $att['class'] = "media$align";
            if ($align == 'right') $att['align'] = 'right';
            if ($align == 'left')  $att['align'] = 'left';
            $ret .= html_flashobject(ml($src,array('cache'=>$cache),true,'&'),$width,$height,
                                     array('quality' => 'high'),
                                     null,
                                     $att,
                                     $this->_xmlEntities($title));
        }
        elseif($dl){
            resolve_mediaid(getNS($src),$src,$exists);
            if ($exists){
                $filesize = filesize(mediaFN($src));
                $filesize = ' ( '.filesize_h($filesize) .' )';
            }
            $filename = basename(str_replace(':', '/', $src));
            $path = (!$parent_id || (getNS($src) != getNS($parent_id))) ? dirname(str_replace(':', '/', $src))."/" : "";
            // well at least we have a title to display
            if (!is_null($title) && !empty($title)) {
                $titleAndNobreak = IocCommon::formatTitleExternalLink("file", "html", $title);
                $title = $titleAndNobreak["title"];
                $noBreak = $titleAndNobreak["nobreak"];
                $title  = $this->_xmlEntities($title);

            }else{
                $title = $filename;
            }
            $src = "$path$filename";
            if($noBreak){
                $ret .= '<a class="media mediafile mf_'.$ext.'" href="media/'.$src.'">'.$title.'</a>';
            }else{
                $ret .= '<div class="mediaf file'.$ext.'">';
                $ret .= '<div class="mediacontent">';
                $ret .= '<a href="img/'.$src.'">'.$title.'</a>'.
                        '<span>'.$filesize.'</span>';
                $ret .= '</div>';
                $ret .= '</div>';
            }
        }
        elseif ($title){
            // well at least we have a title to display
            $ret .= $this->_xmlEntities($title);
        }else{
            // just show the sourcename
            $ret .= $this->_xmlEntities(utf8_basename(noNS($src)));
        }

        return $ret;
    }

    function _xmlEntities($string) {
        return htmlspecialchars($string, ENT_HTML5+ENT_QUOTES,'UTF-8');
    }

    /**
     * Creates a linkid from a headline
     *
     * @param string  $title   The headline title
     * @param boolean $create  Create a new unique ID?
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    function _headerToLink($title,$create=false) {
        if ($create){
            return sectionID($title,$this->headers);
        }else{
            $check = false;
            return sectionID($title,$check);
        }
    }

    /**
     * Construct a title and handle images in titles
     *
     * @author Harry Fuecks <hfuecks@gmail.com>
     */
    function _getLinkTitle($title, $default, & $isImage, $id=null, $linktype='content') {
        $isImage = false;
        if ( is_array($title) ) {
            $isImage = true;
            return $this->_imageTitle($title);
        } elseif ( is_null($title) || trim($title)=='') {
            if (useHeading($linktype) && $id) {
                $heading = p_get_first_heading($id);
                if ($heading) {
                    return $this->_xmlEntities($heading);
                }
            }
            return $this->_xmlEntities($default);
        } else {
            return $this->_xmlEntities($title);
        }
    }

    /**
     * Returns an HTML code for images used in link titles
     *
     * @todo Resolve namespace on internal images
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    function _imageTitle($img) {
        global $ID;

        // some fixes on $img['src']
        // see internalmedia() and externalmedia()
        list($img['src'],$hash) = explode('#',$img['src'],2);
        if ($img['type'] == 'internalmedia') {
            resolve_mediaid(getNS($ID),$img['src'],$exists);
        }

        return $this->_media($img['src'],
                              $ID,
                              $img['title'],
                              $img['align'],
                              $img['width'],
                              $img['height'],
                              $img['cache']);
    }

    /**
     * Helperfunction to internalmedia() and externalmedia() which returns a basic link to a media.
     *
     * @author Pierre Spring <pierre.spring@liip.ch>
     * @param string $src,$title,$align,$width,$height,$cache,$render
     * @return array
     */
    function _getMediaLinkConf($src, $parent_id, $title, $align, $width, $height, $cache, $render) {
        global $conf;

        $link = array();
        $link['class']  = 'media';
        $link['style']  = '';
        $link['pre']    = '';
        $link['suf']    = '';
        $link['more']   = '';
        $link['target'] = $conf['target']['media'];
        $link['title']  = $this->_xmlEntities($src);
        $link['name']   = $this->_media($src, $parent_id, $title, $align, $width, $height, $cache, $render);

        return $link;
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
                    $text = $this->bIocElems[self::REFERRED_B_IOC_ELEMS_TYPE][$id];
                    $this->doc.=$text;
                }
            }
            if(isset($this->tmpData["renderDefaultIocElems"]) && $this->tmpData["renderDefaultIocElems"]){
                while($this->bIocElems[self::UNREFERRED_B_IOC_ELEMS_TYPE][0]){
                    $text = array_shift($this->bIocElems[self::UNREFERRED_B_IOC_ELEMS_TYPE]);
                    $this->doc.=$text;
                }
                $this->tmpData["renderIocElems"] = FALSE;
                $this->tmpData["renderDefaultIocElems"]=FALSE;
            }
            unset($this->tmpData["origin"]);
        }
    }

    public function storeCurrent($clean=FALSE){
        $this->storeForElems = $this->doc;
        if($clean)
            $this->doc = "";

    }

    public function restoreCurrent($clean=FALSE){
        $this->doc = $this->storeForElems;
        if($clean)
            $this->storeForElems="";
    }
}
