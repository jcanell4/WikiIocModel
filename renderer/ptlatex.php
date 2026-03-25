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

class renderer_plugin_wikiiocmodel_ptpdf extends Doku_Renderer {
    const BORDER_TYPES = ["pt_taula"];
    static $convert = FALSE;    //convert images to $imgext
    static $imgext = '.pdf';    //Format to convert images
    static $img_max_table = 99; //Image max width inside tables
    static $hr_width = 354;
    static $p_width = 360;      //415.12572;
    var $code = FALSE;
    var $col_colspan;
    var $has_rowspan = FALSE;
    var $str_hhline="";
    var $col_num = 1;
    var $endimg = FALSE;
    var $formatting = '';
    var $id = '';
    var $max_cols = 0;
    var $monospace = FALSE;
    var $table = FALSE;
    var $tableheader = FALSE;
    var $tableheader_count = 0; //Only one header per table
    var $tableheader_end = FALSE;
    var $tmp_dir = 0;           //Value of temp dir
    private $isBorderTypeTable = false;
    var $levelDiff=0;
    
    /**
     * Initialize the renderer with configuration parameters
     * 
     * Builds the renderer instance from the provided configuration parameters.
     * This is the initialization method called when the renderer is instantiated.
     * 
     * @param array $params Configuration parameters for the renderer
     * @return void
     */
    public function init($params) {
    }

    /**
     * Return the output format identifier for this renderer
     * 
     * Returns the format type that this renderer produces, identifying it as
     * an IOC export LaTeX renderer.
     * 
     * @return string The format identifier 'iocexportl'
     */
    function getFormat(){
        return "iocexportl";
    }

    /**
     * Clear the accumulated document content
     * 
     * Resets the internal document buffer to empty string, preparing for
     * rendering a new document.
     * 
     * @return void
     */
    function reset(){
        $this->doc = '';
    }

    /**
     * Initialize document rendering and check export permissions
     * 
     * Sets up the rendering context by retrieving the page ID, checking if the
     * user has permission to export content, and initializing session variables.
     * Dies if export is not allowed and user is not admin.
     * 
     * @return void
     * @uses getID() to retrieve current page identifier
     * @uses auth_isadmin() to check administrator status
     * @uses _initialize_globals() to setup session variables
     */
    function document_start() {
        global $conf;

	$this->id = getID();
        //Check whether user can export
	$exportallowed = (isset($conf['plugin']['iocexportl']['allowexport']) && $conf['plugin']['iocexportl']['allowexport']);
        if (!$exportallowed && !auth_isadmin()) die;
        //Global variables
        $this->_initialize_globals();
    }

    /**
     * Finalize document rendering by replacing placeholder tokens
     * 
     * Processes the accumulated document content to replace special IOC placeholder
     * tokens with their LaTeX equivalents. This includes:
     * - Replacing @IOCKEYSTART@ with LaTeX opening brace
     * - Replacing @IOCKEYEND@ with LaTeX closing brace
     * - Replacing @IOCBACKSLASH@ with LaTeX backslash
     * - Cleaning up textbf formatting whitespace
     * - Normalizing raggedright spacing
     * 
     * @return void
     */
    function document_end(){
        $this->doc = preg_replace('/@IOCKEYSTART@/','\{', $this->doc);
        $this->doc = preg_replace('/@IOCKEYEND@/','\}', $this->doc);
        $this->doc = preg_replace('/@IOCBACKSLASH@/',"\\\\", $this->doc);
        $this->doc = preg_replace('/(textbf{)(\s*)(.*?)(\s*)(})/',"$1$3$5", $this->doc);
        $this->doc = preg_replace('/(raggedright)(\s{2,*})/',"$1 ", $this->doc);
    }

    /**
     * Initialize all session variables with default values
     * 
     * Sets up session variables used throughout the rendering process if they
     * haven't been initialized. Covers document structure (chapters, activities),
     * media handling (figures, images), tables, quiz mode, and other export options.
     * 
     * @return void
     */
    function _initialize_globals(){
        if (!isset($_SESSION['accounting']))        $_SESSION['accounting'] = FALSE;
        if (!isset($_SESSION['activities_header'])) $_SESSION['activities_header'] = FALSE;
        if (!isset($_SESSION['activities']))        $_SESSION['activities'] = FALSE;
        if (!isset($_SESSION['chapter']))           $_SESSION['chapter'] = 1;
        if (!isset($_SESSION['createbook']))        $_SESSION['createbook'] = FALSE;
        if (!isset($_SESSION['double_cicle']))      $_SESSION['double_cicle'] = FALSE;
        if (!isset($_SESSION['draft']))             $_SESSION['draft'] = FALSE;
        if (!isset($_SESSION['figfooter']))         $_SESSION['figfooter'] = '';
        if (!isset($_SESSION['figlabel']))          $_SESSION['figlabel'] = '';
        if (!isset($_SESSION['figlarge']))          $_SESSION['figlarge'] = FALSE;
        if (!isset($_SESSION['figtitle']))          $_SESSION['figtitle'] = '';
        if (!isset($_SESSION['figure']))            $_SESSION['figure'] = FALSE;
        if (!isset($_SESSION['fpd']))               $_SESSION['fpd'] = FALSE;
        if (!isset($_SESSION['iocelem']))           $_SESSION['iocelem'] = FALSE;
        if (!isset($_SESSION['imgB']))              $_SESSION['imgB'] = FALSE;
        if (!isset($_SESSION['introbook']))         $_SESSION['introbook'] = TRUE;
        if (!isset($_SESSION['onemoreparsing']))    $_SESSION['onemoreparsing'] = FALSE;
        if (!isset($_SESSION['qrcode']))            $_SESSION['qrcode'] = FALSE;
        if (!isset($_SESSION['quizmode']))          $_SESSION['quizmode'] = FALSE;
        if (!isset($_SESSION['table_id']))          $_SESSION['table_id'] = '';
        if (!isset($_SESSION['table_footer']))      $_SESSION['table_footer'] = '';
        if (!isset($_SESSION['table_large']))       $_SESSION['table_large'] = FALSE;
        if (!isset($_SESSION['table_small']))       $_SESSION['table_small'] = FALSE;
        if (!isset($_SESSION['table_title']))       $_SESSION['table_title'] = '';
        if (!isset($_SESSION['table_widths']))      $_SESSION['table_widths'] = '';
        if (!isset($_SESSION['u0']))                $_SESSION['u0'] = FALSE;
        if (!isset($_SESSION['video_url']))         $_SESSION['video_url'] = FALSE;
        if (!isset($_SESSION['xhtml_latex_quiz']))  $_SESSION['xhtml_latex_quiz'] = FALSE;
    }

    /**
     * Format and add text to document output
     * 
     * Sanitizes text by removing extended symbols and trimming whitespace.
     * Optionally converts newlines to LaTeX line breaks when in iocelem mode.
     * Appends the processed text to the document output.
     * 
     * @param string $text The text content to format and output
     * @return void
     * @uses _ttEntities() to remove invalid characters
     */
    function _format_text($text){
        $text = $this->_ttEntities(trim($text));//Remove extended symbols
        if ($_SESSION['iocelem']){
            $text = preg_replace('/\n/',"^^J$1", $text);
        }
        $this->doc .= $text . DOKU_LF;
    }

    /**
     * Generate LaTeX label for document links and cross-references
     * 
     * Creates a LaTeX label based on the MD5 hash of the current page identifier.
     * Adds debug comments indicating the page source for link resolution.
     * Used to enable internal linking via hyperref commands.
     * 
     * @return void
     * @uses md5() to generate unique label identifiers
     * @uses noNS() to remove namespace from page IDs
     * @uses cleanID() to sanitize page identifiers
     */
    function label_document() { //For links
        if (isset($this->info['current_file_id'])) {
            $cleanid = $this->info['current_file_id'];
        }else {
            $cleanid = noNS(cleanID($this->info['current_id'], TRUE));
        }
        $this->doc .= "\label{" . md5($cleanid) . "}";
        if (isset($this->info['current_file_id'])){
            $this->doc .= "%%Start: $cleanid => " . $this->info['current_file_id'].DOKU_LF;
        }else {
            $this->doc .= "%%Start: $cleanid  => " . wikiFN($cleanid).DOKU_LF;
        }
    }

    /**
     * Convert special characters to LaTeX-safe entities
     * 
     * Wrapper function that delegates to _xmlEntities() to escape characters
     * that have special meaning in LaTeX (braces, backslashes, etc.).
     * 
     * @param string $string The text to escape for LaTeX
     * @param mixed $ent Optional parameter (reserved for future use)
     * @return string The escaped text safe for LaTeX output
     * @uses _xmlEntities() to perform actual character replacement
     */
    function _latexEntities($string, $ent=null) { //JOSEP! segon parametre nou! vigilar
        return $this->_xmlEntities($string);
    }

    /**
     * Render a smiley/emoji image in the document
     * 
     * Converts a smiley code to its corresponding image file, converts the image
     * to the target format, and includes it in the LaTeX output with fixed dimensions.
     * 
     * @param string $smiley The smiley code/identifier
     * @return void
     * @uses _image_convert() to convert smiley to target format
     */
    function smiley($smiley) {
        $img = DOKU_INC . 'lib/images/smileys/'. $this->smileys[$smiley];
        $img_aux = $this->_image_convert($img, $this->tmp_dir.'/media');
        $this->doc .= '\includegraphics[height=1em, width=1em]{media/'.basename($img_aux).'}';
    }

    /**
     * Convert image to the target format with optional resizing
     * 
     * Uses ImageMagick 'convert' command to transform images to the configured format
     * (typically PDF). Optionally resizes the image to specified dimensions.
     * 
     * @param string $img Absolute path to the source image file
     * @param string $dest Destination directory for the converted image
     * @param int|null $width Optional width in pixels for resizing
     * @param int|null $height Optional height in pixels for resizing
     * @return string The absolute path to the converted image file
     * @uses ImageMagick convert command for image transformation
     */
    function _image_convert($img, $dest, $width = NULL, $height = NULL){
        $imgdest = tempnam($dest, 'ltx');
        $resize = '';
        if ($width && $height){
            $resize = "-resize $width"."x"."$height";
        }
        @exec("convert $img $resize $imgdest".self::$imgext);
        return $imgdest.self::$imgext;
    }

    function _latexAddImage($src, $width=NULL, $height=NULL, $align=NULL, $title=NULL, $linking=NULL, $external=FALSE){
        if (file_exists($src)) {
            if (!$this->tmp_dir) $this->tmp_dir = $_SESSION['tmp_dir'];
            if (!file_exists($this->tmp_dir)) mkdir($this->tmp_dir, 0775, TRUE);
            if (!file_exists($this->tmp_dir."/media")) mkdir($this->tmp_dir."/media", 0775, TRUE);

            $max_width_elem = '.9\linewidth';

            //pendiente de revisión para establecer la necesidad de su existencia
            if ($_SESSION['figure']){
                $title = $_SESSION['figtitle'];
                $title = preg_replace('/<verd>|<\/verd>/', '', $title);
            }
            $figure = FALSE;
            $footer = '';
            $icon = FALSE;
            $imgb = FALSE;

            //pendiente de revisión para establecer la necesidad de su existencia
            if (!empty($_SESSION['figfooter'])){
                $footer = $_SESSION['figfooter'];
            }

            // make sure width and height are available
            $info = getimagesize($src);
            if (!$width && !$height) {
                $width = $info[0];
            }elseif(!$width){
                $width = round($height * $info[0]/$info[1], 0);
            }

            //pendiente de revisión para establecer la necesidad de su existencia
            $align = ($_SESSION['u0']) ? "flushleft" : "centering";

            //pendiente de revisión para establecer la necesidad de su existencia
            //$this->table, $_SESSION['figure'], $_SESSION['video_url'], $_SESSION['iocelem']
            if (!$this->table && !$_SESSION['figure'] && !$_SESSION['video_url'] && $_SESSION['iocelem'] !== 'textl'){
                if ($width && $width < 133){
                    $max_width = '[width='.$width.'px]';
                    $icon = ($width < 49 && $height < 49);
                }else{
                    $max_width = '[width=35mm]';
                }
                $img_width = FALSE;

            //pendiente de revisión para establecer la necesidad de su existencia
            }elseif (!$this->table && $width > self::$p_width && !$_SESSION['iocelem']){
                $max_width = '[width=\textwidth]';
                $img_width = FALSE;
            }elseif ($_SESSION['iocelem']){
                 //Check wheter image fits on iocelem
                 if ($width >= (.9 * self::$p_width)){
                    $max_width = '[width='.$max_width_elem.']';
                    $img_width = $max_width_elem;
                 }else{
                    $max_width = '[width='.$width.'px]';
                    $img_width = $width;
                    $max_width_elem = FALSE;
                 }
            }else{
                $max_width = '[width='.$width.'px]';
                $img_width = $width;
            }

            //pendiente de revisión para establecer la necesidad de su existencia
            $imgb = (!$icon && !$this->table && !$_SESSION['figure'] && !$_SESSION['iocelem'] && !$_SESSION['video_url'] && !$_SESSION['u0']);
            $figure = (!$this->table && $_SESSION['figure'] && !$_SESSION['video_url'] && !$_SESSION['u0']);

            if (self::$convert || $_SESSION['draft'] || $external){
                $img_aux = $this->_image_convert($src, $this->tmp_dir.'/media');
            }else{
                $ext = pathinfo($src, PATHINFO_EXTENSION);
                $img_aux = $this->copyToTemp("{$this->tmp_dir}/media", $src, $ext);
            }

            if (file_exists("$img_aux.$ext")){
                if ($imgb){ //pendiente de revisión para establecer la necesidad de su existencia
                    $offset = '';
                    //Extract offset
                    if ($title){
                        //extreu la descripció
                        preg_match('/(.*?)(\/([^\/]*$))/', $title, $data);
                        $arrdesc = IocCommon::formatTitleExternalLink("media", "pdf", $title);
                        $title = $arrdesc['title'];
                        $footer = ($arrdesc['alt']) ? $arrdesc['alt'] : $title;
                        if (!empty($data)){
                            if(!empty($data[3]) &&  is_numeric($data[3])){
                                $offset = '['.trim($data[3]).'mm]';
                                $footer = $data[1];
                            }
                        }
                    }
                    $this->doc .= '\imgB'.$offset.'{';
                }
                elseif ($figure){  //pendiente de revisión para establecer la necesidad de su existencia
                    if ($_SESSION['figlarge']){
                        $this->doc .= '\checkoddpage\ifthenelse{\boolean{oddpage}}{\hspace*{0mm}}{\hspace*{-\marginparwidth}\hspace*{-10mm}}'.DOKU_LF;
                        if ($img_width) {
                            $this->doc .= '\begin{center}';
                        }
                        $this->doc .= '\begin{minipage}[c]{\textwidth+\marginparwidth+\marginparsep}'. DOKU_LF;
                    }
                    $this->doc .= '\begin{figure}[H]'.DOKU_LF;
                }

                if (!is_null($linking) && $linking !== 'details'){
                    $this->doc .= '\href{'.$linking.'}{';
                }
                if ($_SESSION['figure']){   //pendiente de revisión para establecer la necesidad de su existencia
                    $this->doc .= '\\' . $align . DOKU_LF;
                }

                //align text and image
                $hspace = 0;
                //Create title with label
                $title_width = ($img_width) ? $img_width.'px' : '\textwidth';

                if ($_SESSION['figure']){
                    //pendiente de revisión para establecer la necesidad de su existencia
                    if ($_SESSION['iocelem'] && $max_width_elem){
                        $title_width = $max_width_elem;
                    }
                    $this->doc .= '\parbox[t]{'.$title_width.'}{\caption{'.trim($this->_xmlEntities($title));
                    if (!empty($_SESSION['figlabel'])){
                        $this->doc .= '\label{'.$_SESSION['figlabel'].'}';
                    }
                    $this->doc .= '}}\\\\\vspace{2mm}'.DOKU_LF;
                }
                //Inside table, images will be centered vertically
                if ($this->table && $width > self::$img_max_table){
                    $this->doc .= '\resizebox{\linewidth}{!}{';
                }
                //Image is smaller than page size
                if ($_SESSION['figure'] && $img_width){
                    $this->doc .= '\begin{center}'.DOKU_LF;
                }
                $this->doc .= '\includegraphics'.$max_width.'{media/'.basename($img_aux).'}';
                if ($_SESSION['figure'] && $img_width){
                    $this->doc .= '\end{center}'.DOKU_LF;
                }
                if ($this->table && $width > self::$img_max_table){
                    $this->doc .= '}';
                }
                //Close href
                if (!is_null($linking) && $linking !== 'details'){
                    $this->doc .= '}';
                    if (!$_SESSION['video_url']){
                        $this->doc .= DOKU_LF;
                    }
                }
                if (!$_SESSION['video_url'] && !empty($footer)){
                    $this->doc .= DOKU_LF;
                }
                //Check whether footer exists
                if ($footer) {
                    if ($_SESSION['figure']){
                        if ($img_width && !$_SESSION['iocelem']){
                            $hspace = ($img_width + $hspace).'pt';
                        }elseif($_SESSION['iocelem']){
                            $hspace = ($max_width_elem)?$max_width_elem:$img_width.'px';
                        }else{
                           $hspace = '\textwidth';
                        }
                        $vspace = '\vspace{-2mm}';
                        $align = '\raggedleft';
                    }elseif($_SESSION['iocelem']){
                        //textboxsize .05
                        $hspace = '.9\linewidth';
                        $vspace = '\vspace{-6mm}';
                        $align = '\raggedleft';
                    }else{
                        $hspace = '\marginparwidth';
                        $vspace = '\vspace{-4mm}';
                        $align = '\iocalignment';
                    }
                    $this->doc .=  '\raisebox{\height}{\parbox[t]{'.$hspace.'}{'.$align.'\footerspacingline\textsf{\tiny'.$vspace.trim($this->_xmlEntities($footer)).'}}}';
                }
                if ($figure){
                    $this->doc .= '\end{figure}';
                    if ($_SESSION['figlarge']){
                        $this->doc .= '\end{minipage}'. DOKU_LF;
                        if ($img_width) {
                            $this->doc .= '\end{center}'. DOKU_LF;
                        }
                    }
                }elseif ($imgb){
                    if (!empty($footer)){
                        $this->doc .= DOKU_LF;
                    }
                    $this->doc .= '}' . DOKU_LF;
                }
                if ($_SESSION['iocelem'] && !$_SESSION['figure']){
                    $this->doc .= '\vspace{1ex}' . DOKU_LF;
                }
                $this->endimg = TRUE;
            }else{
                $this->doc .= '\textcolor{red}{\textbf{File '. $this->_xmlEntities(basename($src)).' does not exist.}}';
            }
        }else{
            $this->doc .= '\textcolor{red}{\textbf{No file name supplied.}}';
            throw new Exception("basiclatex::_latexAddImage: Actual instruction from p_latex_render, no file name supplied from.");
        }
    }

    /**
     * Copy a file to temporary directory with unique name
     * 
     * Creates a unique temporary file in the specified directory and copies the
     * source file to it, appending the specified extension. Retries until successful.
     * 
     * @param string $dir Destination temporary directory path
     * @param string $src Absolute path to source file
     * @param string|null $ext File extension for the temporary copy
     * @return string|bool The full path of the temporary copy with extension
     * @uses tempnam() to generate unique temporary filenames
     */
    public function copyToTemp($dir, $src, $ext=NULL) {
        $result = FALSE;
        while (!$result) {
            if (($name = tempnam($dir, "ltx"))) {
                if (rename($name, "$name.$ext")) {
                    $result = copy($src, "$name.$ext");
                }
            }
            if (!$result) unlink($name);
        }
        return $name;
    }

    /**
     * Render table of contents (not implemented)
     * 
     * Returns empty string as TOC generation is handled elsewhere in this renderer.
     * 
     * @return string Empty string
     */
    function render_TOC() {
         return '';
    }

    /**
     * Add item to table of contents (not implemented)
     * 
     * Empty implementation as TOC generation is handled elsewhere.
     * 
     * @param string $id Page identifier
     * @param string $text Item text/title
     * @param int $level Heading level (1-5)
     * @return void
     */
    function toc_additem($id, $text, $level) {}

    /**
     * Add character data to document output
     * 
     * Appends text content to the document, escaping it for LaTeX output.
     * If monospace mode is active, converts newlines to LaTeX newline commands.
     * 
     * @param string $text The character data to add
     * @return void
     * @uses _xmlEntities() to escape special characters
     */
    function cdata($text) {
        if ($this->monospace){
            $text = preg_replace('/\n/', '\\newline ', $text);
        }
        $this->doc .= $this->_xmlEntities($text);
    }

    /**
     * Open paragraph element (not rendered in LaTeX)
     * 
     * Empty implementation as paragraph markup is implicit in LaTeX.
     * 
     * @return void
     */
    function p_open() {}

    /**
     * Close paragraph element and add vertical spacing
     * 
     * Ends paragraph by adding line feeds, unless an image was just rendered.
     * Resets the endimg flag to allow spacing after normal content.
     * 
     * @return void
     */
    function p_close(){
        if (!$this->endimg){
            $this->doc .= DOKU_LF;
        }else{
            $this->endimg = FALSE;
        }
        $this->doc .= DOKU_LF;
    }

    /**
     * Render a heading/header at the specified level
     * 
     * Converts wiki headings to LaTeX sectioning commands (chapter, section, subsection, etc).
     * Applies special formatting based on document type and session settings (activities,
     * introbook, etc). Adjusts level for activity mode and manages page breaks appropriately.
     * 
     * @param string $text The heading text content
     * @param int $level Heading level (1-5, where 1=chapter)
     * @param int $pos Position in document (unused)
     * @return void
     * @uses _xmlEntities() to escape special characters in heading text
     */
    function header($text, $level, $pos){
        global $conf;

        if ($_SESSION['activities']){
            $level += 1;
        }
        $levels = array(
    		    1 => '\chapter',
    		    2 => '\section',
    		    3 => '\subsection',
    		    4 => '\subsubsection',
    		    5 => '\paragraph',
    		    );

        if ( isset($levels[$level]) ) {
          $token = $levels[$level];
        } else {
          $token = $levels[1];
        }
        $text = $this->_xmlEntities(trim($text));
        $chapternumber = '';
        if ($_SESSION['u0']){
            $chapternumber = '*';
            $this->doc .= '\headingnonumbers';
        }elseif ($_SESSION['introbook'] && $_SESSION['createbook'] && $level === 1 && $_SESSION['chapter'] < 3){
            $chapternumber = '*';
            $_SESSION['chapter'] += 1;
            $this->doc .= '\cleardoublepage\phantomsection\addcontentsline{toc}{chapter}{' . $text . '}'.DOKU_LF;
        }elseif($level === 1){ //Change chapter style
            $this->doc .= '\headingnumbers';
            $_SESSION['activities_header'] = TRUE;
        }
        if ($_SESSION['activities'] && $_SESSION['activities_header'] === TRUE){
            $this->doc .= '\newpage'.DOKU_LF;
            $_SESSION['activities_header'] = FALSE;
        }
        if ($_SESSION['activities'] && $level !== 2){
            $this->doc .= '\headingnonumbers\phantomsection';
            $chapternumber = '*';
        }elseif($_SESSION['activities']){
            $this->doc .= '\headingnumbers';
        }
		//CAL ELIMINAR VARIABLE breakline al canviar el header de nivell 5!!!!!!!
        $breakline = ($level === 5)?"\hspace*{\\fill}\\\\":"";
        $this->doc .= '\hyphenpenalty=100000'.DOKU_LF;
        $this->doc .= "$token$chapternumber{" . $text . "}". $breakline .DOKU_LF;
        $this->doc .= '\hyphenpenalty=1000'.DOKU_LF;
        $this->lastlevel = $level;
    }

    /**
     * Render horizontal rule as page break
     * 
     * In LaTeX output, horizontal rules are rendered as page breaks to provide
     * clear visual separation in the PDF.
     * 
     * @return void
     */
    function hr() {
        $this->doc .= '\newpage'.DOKU_LF;
    }

    /**
     * Render a line break in document content
     * 
     * Inserts appropriate line break markup depending on context. In tables,
     * uses LaTeX break command; otherwise uses double line feed for paragraph break.
     * Maintains text formatting across the break.
     * 
     * @return void
     */
    function linebreak() {
        if ($this->table && !empty($this->formatting)){
            $this->doc .= '}';
        }
        if ($this->table){
            $this->doc .= '\break ';
        }else{
            $this->doc .= DOKU_LF.DOKU_LF;
        }
        $this->doc .= $this->formatting;
    }

    /**
     * Open strong/bold text formatting
     * 
     * Starts bold text formatting using LaTeX textbf command. In table context,
     * also stores the formatting directive for use across line breaks.
     * 
     * @return void
     */
    function strong_open() {
        if ($this->table){
            $this->formatting = '\textbf{';
        }
        $this->doc .= '\textbf{';
    }

    /**
     * Close strong/bold text formatting
     * 
     * Ends bold text formatting and clears the formatting state.
     * 
     * @return void
     */
    function strong_close() {
        $this->doc .= '}';
        $this->formatting = '';
    }

    /**
     * Open italic/emphasis text formatting
     * 
     * Starts italic text formatting using LaTeX textit command. In table context,
     * also stores the formatting directive for use across line breaks.
     * 
     * @return void
     */
    function emphasis_open() {
        if ($this->table){
            $this->formatting = '\textit{';
        }
        $this->doc .= '\textit{';
    }

    /**
     * Close italic/emphasis text formatting
     * 
     * Ends italic text formatting and clears the formatting state.
     * 
     * @return void
     */
    function emphasis_close() {
        $this->doc .= '}';
        $this->formatting = '';
    }

    /**
     * Open underline text formatting
     * 
     * Starts underline formatting using LaTeX underline command. In table context,
     * also stores the formatting directive for use across line breaks.
     * 
     * @return void
     */
    function underline_open() {
        if ($this->table){
            $this->formatting = '\underline{';
        }
        $this->doc .= '\underline{';
    }

    /**
     * Close underline text formatting
     * 
     * Ends underline formatting and clears the formatting state.
     * 
     * @return void
     */
    function underline_close() {
        $this->doc .= '}';
        $this->formatting = '';
    }

    /**
     * Open monospace/teletype text formatting
     * 
     * Activates monospace mode and starts teletype formatting using LaTeX texttt command.
     * While active, newlines are converted to explicit line break commands.
     * 
     * @return void
     */
    function monospace_open() {
        $this->monospace = TRUE;
        $this->doc .= '\texttt{';
    }

    /**
     * Close monospace/teletype text formatting
     * 
     * Ends teletype formatting and deactivates monospace mode.
     * 
     * @return void
     */
    function monospace_close() {
        $this->doc .= '}';
        $this->monospace = FALSE;
    }

    /**
     * Open subscript text formatting
     * 
     * Starts subscript formatting using LaTeX textsubscript command.
     * 
     * @return void
     */
    function subscript_open() {
        $this->doc .= '\textsubscript{';
    }

    /**
     * Close subscript text formatting
     * 
     * Ends subscript formatting.
     * 
     * @return void
     */
    function subscript_close() {
        $this->doc .= '}';
    }

    /**
     * Open superscript text formatting
     * 
     * Starts superscript formatting using LaTeX textsuperscript command.
     * 
     * @return void
     */
    function superscript_open() {
        $this->doc .= '\textsuperscript{';
    }

    /**
     * Close superscript text formatting
     * 
     * Ends superscript formatting.
     * 
     * @return void
     */
    function superscript_close() {
        $this->doc .= '}';
    }

    /**
     * Open deleted/strikethrough text formatting
     * 
     * Starts strikethrough formatting using LaTeX sout command from the ulem package.
     * 
     * @return void
     */
    function deleted_open() {
        $this->doc .= '\sout{';
    }

    /**
     * Close deleted/strikethrough text formatting
     * 
     * Ends strikethrough formatting.
     * 
     * @return void
     */
    function deleted_close() {
        $this->doc .= '}';
    }

    /**
     * Open table element and initialize table rendering
     * 
     * Sets up table rendering context including column count, table type selection
     * (tabu, longtabu), caption handling, and border styling. Applies session settings
     * for accounting tables, large tables, small tables, and iocelem tables.
     * 
     * @param int|null $maxcols Maximum number of columns in the table
     * @param int|null $numrows Number of rows (informational, not directly used)
     * @return void
     * @uses _isBorderTypeTable() to check if table needs border styling
     */
    function table_open($maxcols = NULL, $numrows = NULL){
        global $conf;

        $this->table = TRUE;
        $this->tableheader = TRUE;
        $this->max_cols = $maxcols;
        $this->col_num = 1;
        $this->table_align = array();
        $this->doc .= '\fonttable'.DOKU_LF;
        $this->isBorderTypeTable = $this->_isBorderTypeTable($_SESSION["table_types"]);
        $border = ($_SESSION['accounting'] || $this->isBorderTypeTable)?'|':'';
        $large = '';
        $csetup = '';
        $col_width = '-1,';
        $tablecaption = '\tablecaption';
        $table_type = 'longtabu';
        if ($_SESSION['table_large']){
            $large = ' to 170mm';
            $csetup = '\tablelargecaption';

        }elseif($_SESSION['table_small']){
            $this->doc .= '\addtocounter{table}{-1}\caption{'.$_SESSION['table_title'].
            			  '\label{'.$_SESSION['table_id'].'}}'.DOKU_LF;
            $large = ' spread 0pt';
            $tablecaption = '\tablesmallcaption{'.$maxcols.'}';
            $col_width = '';
            $table_type = 'tabu';
        }elseif($_SESSION['iocelem']){
            $large = ' to \tableiocelemsize';
            $tablecaption = '\tableiocelemcaption';
        }
        $this->doc .= '\begin{'.$table_type.'}'.$large.'{';
        for($i=0; $i < $maxcols; $i++) {
            $table_widths = ($_SESSION['accounting'] || $_SESSION['table'])
                             && is_array($_SESSION['table_widths'])
                             && array_key_exists($i, $_SESSION['table_widths']);
            if ($table_widths) {
                $value = floatval($_SESSION['table_widths'][$i]);
                if ($value <= 1) {
                    $col_width = '-1,';
                } else {
                    $col_width = $value . ',';
                }
                if ($i === 0) {
                    $this->doc .= $border;
                }
            } elseif($_SESSION['accounting'] && $i===0) {//default behaviour
                $col_width = '3,';
                $this->doc .= $border;
            } elseif($_SESSION['accounting']) {
                $col_width = '-1,';
            } elseif(!empty ($border) && $i===0) {
                $this->doc .= $border;
            }
            $this->doc .= 'X['.$col_width.'l] '.$border;
        }
        $this->doc .= '}';
        if (!$_SESSION['table_small']){
            if (!$_SESSION['table_large']){
                $vspace = '\vspace{-2.5ex}';
            } else {
                $separation = (isset($conf['plugin']['iocexportl']['largetablecaptmargin'])?'-2.9ex':'-2.5ex');
                $vspace = '\vspace{'.$separation.'}';
            }
            if (strlen($_SESSION['table_title']) > 86){
                $vspace = '';
            }
            $this->doc .= $csetup.$tablecaption.'\caption{'.$_SESSION['table_title']. $vspace.
            			  '\label{'.$_SESSION['table_id'].'}}'.
            			  '\\\\'.DOKU_LF;
        }
        $this->doc .= '\hline'.DOKU_LF;
    }

    /**
     * Close table element and finalize table markup
     * 
     * Ends table rendering by closing LaTeX table environment (tabu or longtabu).
     * Processes table header/footer markers, restores font sizing, and handles
     * multi-page table continuations.
     * 
     * @return void
     */
    function table_close(){
        $this->table = FALSE;
        if (!$_SESSION['accounting']){
            $this->doc .= '\noalign{\vspace{1mm}}'.DOKU_LF;
            $this->doc .= '\hline'.DOKU_LF;
        }
        if (($_SESSION['iocelem'] || $_SESSION['accounting']) && $_SESSION['table_footer']){
            $this->doc .='\multicolumn{'.$this->max_cols.'}{l@{\hspace{0mm}}}{\hspace{-2mm}'.$_SESSION['table_footer'].'}'.DOKU_LF;
        }
        $this->tableheader_count = 0;
        preg_match('/(?<=@IOCHEADERSTART@)([^@]*)(?=@IOCHEADEREND@)/',$this->doc, $matches);
        $this->doc = preg_replace('/@IOCHEADERSTART@|@IOCHEADEREND@/','', $this->doc);
        $this->doc = preg_replace('/@IOCHEADERBIS@/',isset($matches[1])?$matches[1]:'', $this->doc, 1);
        $this->doc .= '\tabuphantomline';
        if ($_SESSION['table_small']){
            $this->doc .= '\end{tabu}'.DOKU_LF;
        }else{
            $this->doc .= '\end{longtabu}'.DOKU_LF;
        }
        if (!$_SESSION['iocelem']){
            $this->doc .= '\normalfont\normalsize'.DOKU_LF;
        }else{
            $this->doc .= '\defaultspacingpar\ioctextfont'.DOKU_LF;
        }
    }

    /**
     * Open table row element
     * 
     * Initializes a new table row. Applies accounting color if in accounting mode
     * and this is the header row. Resets column counter for the new row.
     * 
     * @return void
     */
    function tablerow_open(){
        if($_SESSION['accounting'] && $this->tableheader && $this->tableheader_count === 0){
            $this->doc .='\rowcolor{coloraccounting}';
        }

        $this->col_num = 1;
    }

    /**
     * Close table row element and apply row formatting
     * 
     * Ends the current table row with appropriate line breaks and horizontal lines.
     * Handles header row formatting, multi-page table continuations, and rowspan
     * lines (hhline). Manages table footer placement and decoration.
     * 
     * @return void
     */
    function tablerow_close(){
        if ($this->tableheader_end){
            $this->tableheader_count += 1;
            $this->tableheader = TRUE;
        }
        if ($this->tableheader_end && $this->tableheader_count === 1
            && !$_SESSION['table_small'] && !$_SESSION['iocelem'] && !$_SESSION['accounting']){
            $this->doc .= '@IOCHEADEREND@';
            $this->doc .= '\\\\ \hline \noalign{\vspace{1mm}} \endfirsthead'.DOKU_LF;
            $this->doc .= '\tablecaptioncontinue\caption[]{(\ioclangcontinue)\vspace{-3mm}} \\\\' . DOKU_LF;
            $this->doc .= '\hline' . DOKU_LF;
            $this->doc .= '@IOCHEADERBIS@ \\\\ \hline' . DOKU_LF;
            $this->doc .= '\endhead' . DOKU_LF;
            if (!$_SESSION['table_small']){
                $headrule = '\tableheadrule';
            }else{
                $headrule = '\tablesmallheadrule';
            }
            $this->doc .= '\noalign{\vspace{-2mm}}\multicolumn{'.$this->max_cols.'}{c}{'.$headrule.'}' . DOKU_LF;
            $this->doc .= '\endfoot' . DOKU_LF;
            $this->doc .= (!empty($_SESSION['table_footer']))?'\multicolumn{'.$this->max_cols.'}{r@{\hspace{0mm}}}{\tablefooter{'.$_SESSION['table_footer'].'}}'.DOKU_LF:''.DOKU_LF;
            $this->doc .= '\endlastfoot' . DOKU_LF;
        }elseif ($this->tableheader_end && $this->tableheader_count === 1
            && !$_SESSION['table_small'] && $_SESSION['iocelem'] && $_SESSION['accounting']){
            $this->doc .= '\\\\ \hline \endfirsthead\endhead'.DOKU_LF;
        }else{
            $this->doc .= '\\\\'.DOKU_LF;
            if ($this->tableheader_end){
                $this->doc .= '\hline'.DOKU_LF;
            }elseif($_SESSION['accounting']){
                $this->doc .= '\hline'.DOKU_LF;
            }elseif($this->isBorderTypeTable){
                if($this->has_rowspan){
                    $this->doc .= '\hhline{'.$this->str_hhline.'}'.DOKU_LF;
                }else{
                    $this->doc .= '\hline'.DOKU_LF;
                }
            }
        }
        $this->tableheader_end = FALSE;
        $this->str_hhline = "";
        $this->has_rowspan=FALSE;        
    }

    /**
     * Open table header cell element
     * 
     * Initializes a table header cell with optional column spanning, alignment,
     * and row spanning. Applies bold formatting and parbox wrapping for proper
     * text alignment. Marks the start of the header row for later processing.
     * 
     * @param int $colspan Number of columns to span (default: 1)
     * @param string|null $align Cell alignment ('left', 'right', 'center')
     * @param int $rowspan Number of rows to span (default: 1)
     * @return void
     */
    function tableheader_open($colspan = 1, $align = NULL, $rowspan = 1){
        $position = 'p{\the\tabucolX * '.$colspan.'}';
        if($this->tableheader){
              $this->doc .= '@IOCHEADERSTART@';
              $this->tableheader = FALSE;
        }
        $this->col_colspan = $colspan;
        if ($colspan > 1){
            $this->doc .= '\multicolumn{'.$colspan.'}{'.$position.'}{';
        }else{
            $this->doc .= '\raggedright ';
        }
        if ($this->tableheader_count > 0 && !$_SESSION['table_small']){
            $this->doc .= '\raisebox{-\height}{';
        }
        if ($align){
            if ($align === 'left'){
                $align = '\raggedright';
            }elseif($align === 'right'){
                $align = '\raggedleft';
            }else{
                $align = '\centering';
            }
        }else{
            $align = '\raggedright';
        }
        if (!$_SESSION['table_small']){
                    $this->doc .= '\parbox[t]{\linewidth}{'.$align;
        }
        $this->formatting = '\textbf{';
        $this->doc .= $this->formatting;
    }

    /**
     * Close table header cell element
     * 
     * Ends the header cell by closing formatting and container elements (parbox,
     * raisebox, multicolumn). Handles column advancement and adds column separator
     * if not the last column. Marks header as ended for row processing.
     * 
     * @return void
     */
    function tableheader_close(){
        $this->formatting = '';
        $this->doc .= '}';//close format
        if (!$_SESSION['table_small']){
            $this->doc .= '}';//close parbox
        }
        if ($this->tableheader_count > 0 && !$_SESSION['table_small']){
            $this->doc .= '}';//close raisebox
        }
        $col_num_aux = ($this->col_colspan > 1)?$this->col_num + ($this->col_colspan-1):$this->col_num;
        if ($this->col_colspan > 1){
            $this->doc .= '}';
        }
        if ($col_num_aux < $this->max_cols){
           $this->doc .= '& ';
        }
       $this->col_num += $this->col_colspan;
       $this->tableheader_end = TRUE;
    }

    /**
     * Open table data cell element
     * 
     * Initializes a table data cell with optional column spanning, alignment,
     * and row spanning. Special handling for accounting tables with full-width cells.
     * Applies appropriate formatting and parbox wrapping for text alignment.
     * 
     * @param int $colspan Number of columns to span (default: 1)
     * @param string|null $align Cell alignment ('left', 'right', 'center')
     * @param int $rowspan Number of rows to span (default: 1)
     * @return void
     */
    function tablecell_open($colspan = 1, $align = NULL, $rowspan = 1){
        if ($_SESSION['accounting'] && $colspan === $this->max_cols){
            $this->doc .= '\rowcolor{coloraccounting}';
            for($i=1;$i<$colspan;$i++){
                $this->doc .= ' &';
            }
            $this->col_colspan = $colspan;
        }else{
            $position = 'p{\the\tabucolX * '.$colspan.'}';
            $this->tableheader = FALSE;
            if ($colspan > 1){
                $this->doc .= '\multicolumn{'.$colspan.'}{'.$position.'}{';
            }
            $this->col_colspan = $colspan;
            if($rowspan>1){
                $this->doc .= '\multirow{'.$rowspan.'}{*}{';
            }
            $this->has_rowspan = $this->has_rowspan || $rowspan>1;
            if (!$_SESSION['table_small']){
                $this->doc .= '\raisebox{-\height}{';
            }
            if ($align){
                if ($align === 'left'){
                    $align = '\raggedright';
                }elseif($align === 'right'){
                    $align = '\raggedleft';
                }else{
                    $align = '\centering';
                }
            }else{
                $align = '\raggedright';
            }
            if (!$_SESSION['table_small']){
                $this->doc .= '\parbox[t]{\linewidth}{'.$align.' ';
            }
        }
    }

    /**
     * Close table data cell element
     * 
     * Ends the data cell by closing formatting and container elements (parbox,
     * raisebox, multicolumn, multirow). Generates hhline pattern for rowspan
     * visualization. Advances column counter and adds separator if not last column.
     * 
     * @return void
     */
    function tablecell_close(){
        //Cal afegir la comanda \hhline{~~--~~--} on ~ = no línia (colspan>1) i - = linia (colspan=1)
        if($this->col_colspan>1){
            for($i=0; $i<$this->col_colspan; $i++){
                $this->str_hhline .= "~";
            }            
        }else{
            $this->str_hhline .= "-";
        }
        if ($_SESSION['accounting'] && $this->col_colspan >= 3){
            $this->col_num += $this->col_colspan;
        }else{
            $col_num_aux = ($this->col_colspan > 1)?$this->col_num + $this->col_colspan:$this->col_num;
            if (!$_SESSION['table_small']){
                $this->doc .= '}';//close parbox
                $this->doc .= '}';//close raisebox
            }
            if ($this->col_colspan > 1) {
                $col_num_aux--;
                $this->doc .= '} ';//close multicolumn
            }
            if ($col_num_aux < $this->max_cols){
                $this->doc .= ' & ';
            }
            $this->col_num += $this->col_colspan;
        }
    }

    /**
     * Open footnote element
     * 
     * Starts a footnote using LaTeX footnote command. Footnote content
     * will be automatically placed at page bottom by LaTeX.
     * 
     * @return void
     */
    function footnote_open() {
        $this->doc .= '\footnote{';
    }

    /**
     * Close footnote element
     * 
     * Ends the footnote definition.
     * 
     * @return void
     */
    function footnote_close() {
        $this->doc .= '}'.DOKU_LF;
    }

    /**
     * Open unordered list element
     * 
     * Starts an unordered (bullet point) list using LaTeX itemize environment.
     * In quiz mode, switches to ordered list. In iocelem context, applies
     * left-aligned formatting for lists.
     * 
     * @return void
     */
    function listu_open() {
        //Quiz questions are numered
        if ($_SESSION['quizmode']){
            $this->listo_open();
        }else{
            $this->doc .= '\nobreak\begin{itemize}'.DOKU_LF;
            //Inside iocelems lists are aligned to left
            if ($_SESSION['iocelem'] && $_SESSION['iocelem'] !== 'textl'){
                $this->doc .= '\raggedright'.DOKU_LF;
            }
        }
    }

    /**
     * Close unordered list element
     * 
     * Ends the unordered list environment and restores alignment if in iocelem context.
     * In quiz mode, delegates to listo_close().
     * 
     * @return void
     */
    function listu_close() {
        if ($_SESSION['quizmode']){
            $this->listo_close();
        }else{
            $this->doc .= '\end{itemize}'.DOKU_LF;
            //Return to normal align
            if ($_SESSION['iocelem'] && $_SESSION['iocelem'] !== 'textl'){
                $this->doc .= '\iocalignment'.DOKU_LF;
            }
        }
    }

    /**
     * Open ordered list element
     * 
     * Starts an ordered (numbered) list using LaTeX enumerate environment.
     * In iocelem context, applies left-aligned formatting for lists.
     * 
     * @return void
     */
    function listo_open() {
        $this->doc .= '\nobreak\begin{enumerate}'.DOKU_LF;
        //Inside iocelems lists are aligned to left
        if ($_SESSION['iocelem'] && $_SESSION['iocelem'] !== 'textl'){
            $this->doc .= '\raggedright'.DOKU_LF;
        }
    }

    /**
     * Close ordered list element
     * 
     * Ends the ordered list environment and restores alignment if in iocelem context.
     * 
     * @return void
     */
    function listo_close() {
        $this->doc .= '\end{enumerate}'.DOKU_LF;
        //Return to normal align
        if ($_SESSION['iocelem'] && $_SESSION['iocelem'] !== 'textl'){
            $this->doc .= '\iocalignment'.DOKU_LF;
        }
    }

    /**
     * Open list item element
     * 
     * Starts a new list item using LaTeX item command.
     * 
     * @param int $level Nesting level of the list item
     * @param bool $node Optional node reference (unused)
     * @return void
     */
    function listitem_open($level, $node=false) {
        $this->doc .= '\item ';
    }

    /**
     * Close list item element
     * 
     * Ends the list item with a line feed.
     * 
     * @return void
     */
    function listitem_close() {
        $this->doc .= DOKU_LF;
    }

    /**
     * Open list item content element (not rendered)
     * 
     * Empty implementation as list content is implicit in LaTeX.
     * 
     * @return void
     */
    function listcontent_open() {
    }

    /**
     * Close list item content element (not rendered)
     * 
     * Empty implementation as list content is implicit in LaTeX.
     * 
     * @return void
     */
    function listcontent_close() {
    }

    /**
     * Add unformatted text to document
     * 
     * Appends text to document output, escaping it for LaTeX without any
     * additional formatting applied.
     * 
     * @param string $text Text content to add
     * @return void
     * @uses _latexEntities() to escape special characters
     */
    function unformatted($text) {
        $this->doc .= $this->_latexEntities($text);
    }

    /**
     * Render an acronym in the document
     * 
     * Outputs an acronym, escaping it for LaTeX. In a full implementation,
     * could expand to full text with hover information.
     * 
     * @param string $acronym The acronym text
     * @return void
     * @uses _latexEntities() to escape special characters
     */
    function acronym($acronym) {
        $this->doc .= $this->_latexEntities($acronym);
    }

    /**
     * Render an HTML/XML entity
     * 
     * Outputs an entity (such as &copy; or &nbsp;), escaping it for LaTeX.
     * 
     * @param string $entity The entity text
     * @return void
     * @uses _xmlEntities() to escape special characters
     */
    function entity($entity) {
        $this->doc .= $this->_xmlEntities($entity);
    }

    /**
     * Render a multiplication entity (x operator)
     * 
     * Outputs a multiplication symbol between two values, typically used for
     * dimensions like "10x20".
     * 
     * @param string|int $x First operand
     * @param string|int $y Second operand
     * @return void
     */
    function multiplyentity($x, $y) {
        $this->doc .= $x.'x'.$y;
    }

    /**
     * Render opening single quote
     * 
     * Outputs an opening single quote character (backtick).
     * 
     * @return void
     */
    function singlequoteopening() {
        $this->doc .= "`";
    }

    /**
     * Render closing single quote
     * 
     * Outputs a closing single quote character (apostrophe).
     * 
     * @return void
     */
    function singlequoteclosing() {
        $this->doc .= "'";
    }

    /**
     * Render apostrophe character
     * 
     * Outputs an apostrophe character.
     * 
     * @return void
     */
    function apostrophe() {
        $this->doc .= "'";
    }

    /**
     * Render opening double quote
     * 
     * Outputs an opening double quote (LaTeX style with two backticks).
     * 
     * @return void
     */
    function doublequoteopening() {
        $this->doc .= "``";
    }

    /**
     * Render closing double quote
     * 
     * Outputs a closing double quote (LaTeX style with two apostrophes).
     * 
     * @return void
     */
    function doublequoteclosing() {
        $this->doc .= "''";
    }

    /**
     * Render inline PHP code
     * 
     * Outputs PHP code in monospace format with escaped special characters.
     * 
     * @param string $text The PHP code to render
     * @param string $wrapper Optional wrapper element type (unused, default 'dummy')
     * @return void
     * @uses monospace_open() and monospace_close() for formatting
     * @uses _xmlEntities() to escape special characters
     */
    function php($text, $wrapper='dummy') {
        $this->monospace_open();
        $this->doc .= $this->_xmlEntities($text);
        $this->monospace_close();
    }

    /**
     * Render block-level PHP code
     * 
     * Outputs PHP code block with preformatted formatting.
     * Delegates to file() method for consistent block handling.
     * 
     * @param string $text The PHP code block to render
     * @return void
     * @uses file() for block-level formatting
     */
    function phpblock($text) {
        $this->file($text);
    }

    /**
     * Render inline HTML code
     * 
     * Outputs HTML code in monospace format with escaped special characters.
     * 
     * @param string $text The HTML code to render
     * @param string $wrapper Optional wrapper element type (unused, default 'dummy')
     * @return void
     * @uses monospace_open() and monospace_close() for formatting
     * @uses _xmlEntities() to escape special characters
     */
    function html($text, $wrapper='dummy') {
        $this->monospace_open();
        $this->doc .= $this->_xmlEntities($text);
        $this->monospace_close();
    }

    /**
     * Render block-level HTML code
     * 
     * Outputs HTML code block with preformatted formatting.
     * Delegates to file() method for consistent block handling.
     * 
     * @param string $text The HTML code block to render
     * @return void
     * @uses file() for block-level formatting
     */
    function htmlblock($text) {
        $this->file($text);
    }

    /**
     * Render preformatted/fixed-width text block
     * 
     * Outputs text in preformatted style with reserved symbols cleaned.
     * Used for code snippets and technical content.
     * 
     * @param string $text The preformatted text to render
     * @return void
     * @uses clean_reserved_symbols() to clean invalid characters
     * @uses _format_text() to format and output the text
     */
    function preformatted($text) {
        $this->doc .= '\codeinline{';
        $text = clean_reserved_symbols($text);
        $this->_format_text($text);
        $this->doc .= '}';
    }

    /**
     * Render file/code content block
     * 
     * Outputs file content in preformatted style.
     * Delegates to preformatted() for consistent handling.
     * 
     * @param string $text The file content to render
     * @return void
     * @uses preformatted() for formatting
     */
    function file($text) {
        $this->preformatted($text);
    }

    /**
     * Open block quote element
     * 
     * Starts a block quote context by outputting a text bar character.
     * 
     * @return void
     */
    function quote_open() {
        $this->doc .= "\textbar";
    }

    /**
     * Close block quote element
     * 
     * Ends the block quote context (no special action needed).
     * 
     * @return void
     */
    function quote_close() {
    }

    /**
     * Render code block with optional syntax highlighting language
     * 
     * Outputs a code block with specified programming language for syntax
     * highlighting. Handles large code blocks (flag 'l'), different contexts
     * (iocelem vs normal), and language-specific formatting.
     * 
     * @param string $text The code content to render
     * @param string|null $language Programming language for syntax highlighting (e.g., 'php', 'html')
     * @param string|null $filename Optional filename for the code block
     * @return void
     * @uses _format_text() to format and output the code
     */
    function code($text, $language=null, $filename=null) {
        $large = preg_split('/\//', $language, 2);
        $language = preg_replace('/\/.*$/', '', $language);
        if (preg_match('/html|css|dtd|rss/i', $language)){
            $language = 'HTML';
        }
        if(!$_SESSION['iocelem']){
            if (isset($large[1]) && $large[1] === 'l'){
                $this->doc .= '\checkoddpage\ifthenelse{\boolean{oddpage}}{\hspace*{4mm}}{\hspace*{-\marginparwidth}\hspace*{-6mm}}'.DOKU_LF;
                $this->doc .= '\begin{minipage}[c]{\textwidth+\marginparwidth+4mm}'. DOKU_LF;
            }
            $this->doc .= '\vspace{1ex}'.DOKU_LF;
            if ( !$language ) {
                $this->doc .= '\begin{csource}{language=}'.DOKU_LF;
            } else {
                $this->doc .= '\begin{csource}{language='.$language.'}'.DOKU_LF;
            }
            $this->doc .=  $this->_format_text($text);
            $this->doc .= '\end{csource}'.DOKU_LF;
            if (isset($large[1]) && $large[1] === 'l'){
                $this->doc .= '\end{minipage}'.DOKU_LF.DOKU_LF;
            }
        }else{
            $this->doc .= '\vspace{1ex}'. DOKU_LF;
            $this->doc .= '\begin{adjustwidth}{12mm}{9mm}'. DOKU_LF;
            if ( !$language ) {
                $this->doc .= '\begin{csource}{language=}^^J';
            } else {
                $this->doc .= '\begin{csource}{language='.$language.'}^^J';
            }
            $text = preg_replace('/\\\\/', '\\\\\\\\', $text);
            $text = preg_replace('/ /', '\\\\ ', $text);
            $text = preg_replace('/([%{}])/', '\\\\$1', $text);
            $this->doc .=  $this->_format_text($text) . '^^J';
            $this->doc .= '\end{csource}'.DOKU_LF;
            $this->doc .= '\end{adjustwidth}'.DOKU_LF;
            $this->doc .= '\vspace{-2ex}'. DOKU_LF;
        }
    }

    /**
     * Render internal wiki media (images or PDFs)
     * 
     * Processes internal media from the wiki media directory. Handles images
     * by rendering them directly, and PDFs by generating QR codes linking to them.
     * 
     * @param string $src The media identifier/path within the wiki
     * @param string|null $title Optional title/caption for the media
     * @param string|null $align Optional alignment (left, center, right)
     * @param int|null $width Optional width in pixels
     * @param int|null $height Optional height in pixels
     * @param string|null $cache Cache control directive (unused)
     * @param string|null $linking Optional link URL for the media
     * @return void
     * @uses resolve_mediaid() to resolve media path
     * @uses mimetype() to determine media type
     * @uses _latexAddImage() for image rendering
     */
    function internalmedia ($src, $title=null, $align=null, $width=null, $height=null, $cache=null, $linking=null) {
        resolve_mediaid(getNS($this->id), $src, $exists);
        list($ext, $mime) = mimetype($src);
        $type = substr($mime,0,5);
        if ($type === 'image'){
            $file = mediaFN($src);
            $this->_latexAddImage($file, $width, $height, $align, $title, $linking);
        }elseif($type === 'appli'){
            if (preg_match('/\.pdf$/', $src)){
                $_SESSION['qrcode'] = TRUE;
                $src = $this->_xmlEntities(DOKU_URL.'lib/exe/fetch.php?media='.$src);
                qrcode_media_url($this, $src, $title, 'pdf');
            }
        }else{
            if (!$_SESSION['u0']){
                $this->code('FIXME internalmedia ('.$type.'): '.$src);
            }
        }
    }

    /**
     * Render external media (remote images)
     * 
     * Processes external media from remote URLs. Downloads images and includes
     * them in the document. Falls back to external link for non-image content.
     * 
     * @param string $src The external URL of the media
     * @param string|null $title Optional title/caption for the media
     * @param string|null $align Optional alignment (left, center, right)
     * @param int|null $width Optional width in pixels
     * @param int|null $height Optional height in pixels
     * @param string|null $cache Cache control directive (unused)
     * @param string|null $linking Optional link URL for the media
     * @return void
     * @uses mimetype() to determine media type
     * @uses DokuHTTPClient to download remote images
     * @uses _latexAddImage() for image rendering
     * @uses externallink() as fallback for non-image content
     */
    function externalmedia ($src, $title=NULL, $align=NULL, $width=NULL, $height=NULL, $cache=NULL, $linking=NULL) {
        list($ext, $mime) = mimetype($src);
        if (substr($mime,0,5) == 'image'){
            $tmp_name = tempnam($this->tmp_dir.'/media', 'ext');
            $client = new DokuHTTPClient;
            $img = $client->get($src);
            if (!$img) {
                $this->externallink($src, $title);
            } else {
                $tmp_img = fopen($tmp_name, "w") or die("Can't create temp file $tmp_img");
                fwrite($tmp_img, $img);
                fclose($tmp_img);
		//Add and convert image to pdf
                $this->_latexAddImage($tmp_name, $width, $height, $align, $title, $linking, TRUE);
            }
        }else{
            $this->externallink($src, $title);
        }
    }

    /**
     * Render CamelCase wiki link
     * 
     * Converts a CamelCase link to an internal link using the same text as both
     * the link target and display text.
     * 
     * @param string $link The CamelCase link identifier
     * @return void
     * @uses internallink() to render as internal wiki link
     */
    function camelcaselink($link) {
        $this->internallink($link, $link);
    }

    /**
     * Render internal wiki link with hyperref support
     * 
     * Creates a hyperref link to another wiki page, with optional custom link text.
     * Resolves the page ID, generates MD5 hash for label matching, and creates
     * a LaTeX hyperref command. Supports section anchors via # notation.
     * 
     * @param string $id Page identifier or page#section format
     * @param string|null $name Optional custom link text (defaults to page title or ID)
     * @return void
     * @uses resolve_pageid() to resolve and validate page ID
     * @uses _getLinkTitle() to determine display text
     * @uses _simpleTitle() to generate default title from page ID
     * @uses cleanID() to generate hash-friendly identifiers
     */
    function internallink($id, $name = NULL) {
        // default name is based on $id as given
        $default = $this->_simpleTitle($id);
        // now first resolve and clean up the $id
        resolve_pageid(getNS($this->id),$id,$exists);
        $name = $this->_getLinkTitle($name, $default, $isImage, $id);
        list($page, $section) = preg_split('/#/', $id, 2);
        if (!empty($section)){
            $cleanid = noNS(cleanID($section, TRUE));
        }else{
            $cleanid = noNS(cleanID($id, TRUE));
        }
        $md5 = md5($cleanid);

        $this->doc .= '\hyperref[';
        $this->doc .= $md5;
        $this->doc .= ']{';
        $this->doc .= $name;
        $this->doc .= '}';
    }

    /**
     * Render external URL link
     * 
     * Creates a hyperlink to an external URL using LaTeX href/url commands.
     * Supports optional link text and image links. Escapes special characters
     * (# and %) in URLs when in iocelem context.
     * 
     * @param string $url The external URL to link to
     * @param string|null $title Optional link text (defaults to URL)
     * @return void
     * @uses _getLinkTitle() to determine display text or handle image links
     * @uses externalmedia() for external image links
     * @uses internalmedia() for internal image links
     */
    function externallink($url, $title = NULL) {
        //Escape # only inside iocelem
        if ($_SESSION['iocelem']){
            $url = preg_replace('/(#|%)/','\\\\$1', $url);
        }
        if (!$title){
            $this->doc .= '\url{'.$url.'}';
        }else{
            $title = $this->_getLinkTitle($title, $url, $isImage);
            if (is_string($title)){
                $this->doc .= '\href{'.$url.'}{'.$title.'}';
            }else{//image
                if (preg_match('/http|https|ftp/', $title['src'])){
                    $this->externalmedia($title['src'],null,$title['align'],$title['width'],null,null,$url);
                }else{
                    $this->internalmedia($title['src'],null,$title['align'],$title['width'],null,null,$url);
                }
            }
        }
   }

    /**
     * Render local page anchor link (document internal)
     * 
     * Creates a link to a location within the same document using a page anchor/hash.
     * Displays the link text without creating an active hyperlink in LaTeX.
     * 
     * @param string $hash The anchor/hash identifier within the page
     * @param string|null $name Optional link text (defaults to anchor)
     * @return void
     * @uses _getLinkTitle() to determine display text
     * @todo Add image handling for embedded images in links
     */
    function locallink($hash, $name = NULL){
        $name = $this->_getLinkTitle($name, $hash, $isImage);
        $this->doc .= $name;
    }

    /**
     * Render InterWiki link (not implemented)
     * 
     * Empty implementation for InterWiki protocol links linking to external wikis.
     * Not currently supported in this LaTeX renderer.
     * 
     * @param string $match The InterWiki match pattern
     * @param string|null $name Optional link text
     * @param string $wikiName The target wiki name
     * @param string $wikiUri The target wiki URI
     * @return void
     */
    function interwikilink($match, $name = NULL, $wikiName, $wikiUri) {}

    /**
     * Render Windows share/SMB network link
     * 
     * Renders Windows network share links (SMB/UNC paths).
     * Outputs as unformatted wiki link syntax since LaTeX doesn't natively
     * support network shares.
     * 
     * @param string $url The Windows share URL (\\\\server\\share format)
     * @param string|null $name Optional link text
     * @return void
     * @uses unformatted() to output wiki link syntax
     * @todo Add image handling for embedded images in links
     */
    function windowssharelink($url, $name = NULL) {
        $this->unformatted('[['.$url.'|'.$name.']]');
    }

    /**
     * Render email mailto link
     * 
     * Creates a hyperlink to an email address using LaTeX href command with mailto: protocol.
     * Displays the email address as the link text.
     * 
     * @param string $address The email address to link to
     * @param string|null $name Optional link text (currently unused, address is always shown)
     * @return void
     * @uses _xmlEntities() to escape special characters in email address
     * @todo Add support for custom link text parameter
     */
    function emaillink($address, $name = NULL) {
        $this->doc .= '\href{mailto:'.$this->_xmlEntities($address).'}{'.$this->_xmlEntities($address).'}';
    }

    /**
     * Determine link display text or extract image from title
     * 
     * Processes link title to determine what should be displayed. Handles:
     * - Null titles: uses configured page heading or provided default
     * - String titles: returns escaped text
     * - Image references: returns image information array and sets isImage flag
     * 
     * @param string|array|null $title The title text, image array, or null
     * @param string $default Default text to use if title is null
     * @param bool &$isImage Output flag set to true if an image was found in title
     * @param string|null $id Optional page ID for heading lookup
     * @return string|array Either the display text (string) or image info array
     * @author Harry Fuecks <hfuecks@gmail.com>
     * @uses p_get_first_heading() to retrieve page heading
     * @uses IocCommon::formatTitleExternalLink() to process title
     * @uses _latexEntities() to escape text
     */
    function _getLinkTitle($title, $default, & $isImage, $id=null) {
        global $conf;

        $isImage = FALSE;
        if ( is_null($title) ) {
            if ($conf['useheading'] && $id) {
                $heading = p_get_first_heading($id);
                if ($heading) {
                    return $this->_latexEntities($heading);
                }
            }
            return $this->_latexEntities($default);
        }else if ( is_string($title) ) {
            $title = IocCommon::formatTitleExternalLink("link", "pdf", $title);
            return $this->_latexEntities($title);
        }else if ( is_array($title) ) {
            $isImage = TRUE;
            if (isset($title['caption'])) {
                $title['title'] = $title['caption'];
            }else {
                $title['title'] = $default;
            }
            return $title;
        }
    }

    /**
     * Escape special characters for safe LaTeX output
     * 
     * Converts characters with special meaning in LaTeX to escaped or placeholder
     * equivalents. Handles: braces, backslash, underscore, caret, angle brackets,
     * hash, percent, dollar, ampersand, tilde, quotes, and special dashes.
     * Different handling for monospace mode (uses newline commands).
     * 
     * @param string $value The text to escape
     * @return string The text with special characters escaped for LaTeX
     * @uses str_ireplace() for case-insensitive replacement
     * @uses preg_replace() for newline handling in monospace mode
     */
    function _xmlEntities($value) {
        static $find = array('{', '}', '\\', '_', '^', '<', '>', '#', '%', '$', '&', '~', '"', '−');
        static $replace = array('@IOCKEYSTART@', '@IOCKEYEND@', '\textbackslash ', '@IOCBACKSLASH@_', '@IOCBACKSLASH@^{}',
				'@IOCBACKSLASH@textless{}','@IOCBACKSLASH@textgreater{}','@IOCBACKSLASH@#','@IOCBACKSLASH@%',
                                '@IOCBACKSLASH@$', '@IOCBACKSLASH@&', '@IOCBACKSLASH@~{}', '@IOCBACKSLASH@textquotedbl{}', '-');

        if ($this->monospace){
            $value = str_ireplace($find, $replace, $value);
            return preg_replace('/\n/', '\\newline ', $value);
        }else{
            return str_ireplace($find, $replace, $value);
        }
    }

    /**
     * Remove invalid characters from text
     * 
     * Replaces extended/invalid Unicode characters with a placeholder message.
     * Used to clean text before formatting to ensure LaTeX compatibility.
     * 
     * @param string $value The text to clean
     * @return string The text with invalid characters replaced
     * @global array $symbols The list of invalid characters to replace
     * @uses str_ireplace() for case-insensitive replacement
     */
    function _ttEntities($value) {
        global $symbols;
        return str_ireplace($symbols, ' (Invalid character) ', $value);
    }

    function _latexElements($value){
        //LaTeX mode
        $replace = FALSE;
        while(preg_match('/<latex>(.*?)<\/latex>/', $value, $matches)){
            $text = str_ireplace($symbols, ' (Invalid character) ', $matches[1]);
			$text = preg_replace('/(\$)/', '\\\\$1', $text);
			$value = preg_replace('/<latex>(.*?)<\/latex>/', filter_tex_sanitize_formula($text), $value, 1);
			$replace = TRUE;
        }
        //Math block mode
        while(preg_match('/\${2}\n?([^\$]+)\n?\${2}/', $value, $matches)){
            $text = str_ireplace($symbols, ' (Invalid character) ', $matches[1]);
			$text = preg_replace('/(\$)/', '\\\\$1', $text);
            $value = preg_replace('/\${2}\n?([^\$]+)\n?\${2}/', '\begin{center}\begin{math}'.filter_tex_sanitize_formula($text).'\end{math}\end{center}', $value, 1);
            $replace = TRUE;
        }
        //Math inline mode
        if(preg_match_all('/\$\n?([^\$]+)\n?\$/', $value, $matches, PREG_SET_ORDER)){
            foreach($matches as $m){
                $text = str_ireplace($symbols, ' (Invalid character) ', $m[1]);
    			$text = preg_replace('/(\$)/', '\\\\$1', $text);
                $value = str_replace($m[0], '$ '.filter_tex_sanitize_formula($text).' $', $value);
                $replace = TRUE;
            }
        }
        return array($value, $replace);
    }

    function rss ($url,$params){
        global $lang;
        global $conf;

        require_once(DOKU_INC.'inc/FeedParser.php');
        $feed = new FeedParser();
        $feed->feed_url($url);

        //disable warning while fetching
        if (!defined('DOKU_E_LEVEL')) { $elvl = error_reporting(E_ERROR); }
        $rc = $feed->init();
        if (!defined('DOKU_E_LEVEL')) { error_reporting($elvl); }

        //decide on start and end
        if($params['reverse']){
            $mod = -1;
            $start = $feed->get_item_quantity()-1;
            $end   = $start - ($params['max']);
            $end   = ($end < -1) ? -1 : $end;
        }else{
            $mod   = 1;
            $start = 0;
            $end   = $feed->get_item_quantity();
            $end   = ($end > $params['max']) ? $params['max'] : $end;;
        }

        $this->listu_open();
        if($rc){
            for ($x = $start; $x != $end; $x += $mod) {
                $item = $feed->get_item($x);
                $this->listitem_open(0);
                $this->listcontent_open();
                $this->externallink($item->get_permalink(),
                                    $item->get_title());
                if($params['author']){
                    $author = $item->get_author(0);
                    if($author){
                        $name = $author->get_name();
                        if(!$name) $name = $author->get_email();
                        if($name) $this->cdata(' '.$lang['by'].' '.$name);
                    }
                }
                if($params['date']){
                    $this->cdata(' ('.$item->get_date($conf['dformat']).')');
                }
                if($params['details']){
                    $this->cdata(strip_tags($item->get_description()));
                }
                $this->listcontent_close();
                $this->listitem_close();
            }
        }else{
            $this->listitem_open(0);
            $this->listcontent_open();
            $this->emphasis_open();
            $this->cdata($lang['rssfailed']);
            $this->emphasis_close();
            $this->externallink($url);
            $this->listcontent_close();
            $this->listitem_close();
        }
        $this->listu_close();
    }
    
    private function _isBorderTypeTable($types){
        return count(array_intersect($types, self::BORDER_TYPES))!=0;
    }
}
