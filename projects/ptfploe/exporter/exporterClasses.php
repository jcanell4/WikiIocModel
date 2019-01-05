<?php
/**
 * exporterClasses: clases de procesos, establecidas en el fichero de configuración,
 *                  correspondientes a los tipos de datos del proyecto
 * @culpable Rafael Claver
 */
if (!defined('DOKU_INC')) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', realpath(DOKU_INC."lib/plugins/"));
if (!defined('EXPORT_TMP')) define('EXPORT_TMP', DOKU_PLUGIN."tmp/latex/");

abstract class AbstractRenderer {
    protected $factory;
    protected $cfgExport;
    protected $extra_data;
    protected $rendererPath;
    protected $mode;
    protected $filetype;

    public function __construct($factory, $cfgExport=NULL) {
        $this->factory = $factory;
        $this->rendererPath = dirname(realpath(__FILE__));
        $this->mode = $factory->getMode();
        $this->filetype = $factory->getFileType();
        if ($cfgExport){
            $this->cfgExport = $cfgExport;
        }else{
            $this->cfgExport = new cfgExporter();
        }
    }
    
    public function getTocs(){
        return $this->cfgExport->tocs;
    }

    public function init($extra) {
        $this->extra_data = $extra;
    }

    public function loadTemplateFile($file) {
        $tmplt = @file_get_contents("{$this->rendererPath}/$file");
        if ($tmplt == FALSE) throw new Exception("Error en la lectura de l'arxiu de plantilla: $file");
        return $tmplt;
    }

    public function isEmptyArray($param) {
        $vacio = TRUE;
        foreach ($param as $value) {
            $vacio &= (is_array($value)) ? $this->isEmptyArray($value) : empty($value);
        }
        return $vacio;
    }
}

class cfgExporter {
    public $id;
    public $langDir;        //directori amb cadenes traduïdes
    public $aLang = array();//cadenes traduïdes
    public $lang = 'ca';    //idioma amb el que es treballa
    public $tmp_dir;
    public $latex_images = array();
    public $media_files = array();
    public $graphviz_images = array();
    public $gif_images = array();
    public $toc=NULL;
    public $tocs=array();
    public $permissionToExport = TRUE;

    public function __construct() {
        $this->tmp_dir = realpath(EXPORT_TMP)."/".rand();;
    }
}

abstract class renderComposite extends AbstractRenderer {
    protected $typedef = array();
    protected $renderdef = array();
    /**
     * @param array $typedef : tipo (objeto en configMain.json) correspondiente al campo actual en $data
     * @param array $renderdef : tipo (objeto en configRender.json) correspondiente al campo actual en $data
     */
    public function __construct($factory, $typedef, $renderdef, $cfgExport=NULL) {
        parent::__construct($factory, $cfgExport);
        $this->typedef = $typedef;
        $this->renderdef = $renderdef;
    }

    public function createRender($typedef=NULL, $renderdef=NULL) {
        return $this->factory->createRender($typedef, $renderdef, $this->cfgExport);
    }
    public function getTypesDefinition($key = NULL) {
        return $this->factory->getTypesDefinition($key);
    }
    public function getTypesRender($key = NULL) {
        return $this->factory->getTypesRender($key);
    }
    public function getTypeDef($key = NULL) {
        return ($key === NULL) ? $this->typedef : $this->typedef[$key];
    }
    public function getRenderDef($key = NULL) {
        return ($key === NULL) ? $this->renderdef : $this->renderdef[$key];
    }
    public function getTypedefKeyField($field) { //@return array : objeto key solicitado (del configMain.json)
        return $this->getTypeDef('keys')[$field];
    }
    public function getRenderKeyField($field) { //@return array : objeto key solicitado (del configRender.json)
        return $this->getRenderDef('keys')[$field];
    }
}

class renderObject extends renderComposite {

    protected $data = array();
    /**
     * @param array $data : array correspondiente al campo actual del archivo de datos del proyecto
     * @return datos renderizados
     */
    public function process($data) {
        $this->data = $data;
        $campos = $this->getRenderFields();
        foreach ($campos as $keyField) {
            $typedefKeyField = $this->getTypedefKeyField($keyField);
            $renderKeyField = $this->getRenderKeyField($keyField);
            $render = $this->createRender($typedefKeyField, $renderKeyField);

            $dataField = $this->getDataField($keyField);
            $render->init($keyField);
            $arrayDeDatosParaLaPlantilla[$keyField] = $render->process($dataField);
        }
        $extres = $this->getRenderExtraFields();
        if($extres){
            foreach ($extres as $item) {
                if($item["valueType"] == "field" ){
                    $typedefKeyField = $this->getTypedefKeyField($item["value"]);
                    $renderKeyField = $this->getRenderKeyField($item["name"]);
                    $render = $this->createRender($typedefKeyField, $renderKeyField);
                    
                    $dataField = $this->getDataField($item["value"]);
                    $render->init($item["name"]);

                    $arrayDeDatosParaLaPlantilla[$item["name"]] = $render->process($dataField);
                }
            }
        }
        
        $ret = $this->cocinandoLaPlantillaConDatos($arrayDeDatosParaLaPlantilla);
        return $ret;
    }

    public function getRenderFields() { //devuelve el array de campos establecidos para el render
        $ret = $this->getRenderDef('render')['fields'];
        if(is_string($ret)){
            switch (strtoupper($ret)){
                case "ALL":
                    $ret = array_keys($this->typedef["keys"]);
                    break;
                case "MANDATORY":
                    $ret = array();
                    $allKeys = array_keys($this->typedef["keys"]);
                    foreach ($allKeys as $key) {
                        if($this->typedef["keys"][$key]["mandatory"]){
                          $ret [] = $key;  
                        }
                    }
                    break;
            }
        }
        return $ret;
    }

    public function getRenderExtraFields() { //devuelve el array de campos establecidos para el render
        return $this->getRenderDef('render')['extraFields'];
    }

    public function getDataField($key = NULL) {
        return ($key === NULL) ? $this->data : $this->data[$key];
    }

    public function cocinandoLaPlantillaConDatos($param) {
        if (is_array($param)) {
            foreach ($param as $value) {
                $ret .= (is_array($value)) ? $this->cocinandoLaPlantillaConDatos($value)."\n" : $value."\n";
            }
        }else {
            $ret = $param;
        }
        return $ret;
    }
}

class renderArray extends renderComposite {

    public function process($data) {
        $ret = "";
        $filter = $this->getFilter();
        $itemType = $this->getItemsType();
        $render = $this->createRender($this->getTypesDefinition($itemType), $this->getTypesRender($itemType));
        //cada $item es un array de tipo concreto en el archivo de datos
        foreach ($data as $key => $item) {
            if ($filter === "*" || in_array($key, $filter)) {
                $ret .= $render->process($item);
            }
        }
        return $ret;
    }

    protected function getItemsType() {
        return $this->getTypeDef('itemsType'); //tipo al que pertenecen los elementos del array
    }
    protected function getFilter() {
        return $this->getRenderDef('render')['filter'];
    }
}



require_once (DOKU_INC.'inc/inc_ioc/tcpdf/tcpdf_include.php');

class IocTcPdf extends TCPDF{
    private $header_logo_hight=10;
    
    public function __construct($orientation = 'P', $unit = 'mm', $format = 'A4', $unicode = true, $encoding = 'UTF-8', $diskcache = false, $pdfa = false) {
        parent::__construct($orientation, $unit, $format, $unicode, $encoding, $diskcache, $pdfa);
        $this->header_logo_width = 8;
        $this->SetMargins(20, 20);
        $this->head =20;
        $this->header_font = "helvetica";

        }
    
    //Page header
    public function Header() {
        if($this->PageNo()==1){
            return;
        }

        $margins = $this->getMargins();
        
        // Logo
        $image_file = K_PATH_IMAGES.$this->header_logo;        
        $this->Image($image_file, $margins['left'], 5, $this->header_logo_width, $this->header_logo_hight, 'JPG', '', 'T', true, 300, '', false, false, 0, false, false, false);
        
        $cell_height = $this->getCellHeight($headerfont[2] / $this->k);
        $header_x = $margins['left'] + $margins['padding_left'] + ($this->header_logo_width * 1.1);
        $header_w = 105 - $header_x;

        $this->SetTextColorArray($this->header_text_color);
        // header title
        $this->SetFont($this->header_font[0], $this->header_font[1], $this->header_font[2]);
        $this->SetX($header_x);
        $this->MultiCell($header_w, $cell_height, $this->header_title, 0, 'L', 0, 0, "", "", true);
        
        // header string
        $this->MultiCell(65, $cell_height, $this->header_string, 0, 'R', 0, 0, "", "", true);
        $this->Line(5, 19, 180,19);
    }

    // Page footer
    public function Footer() {
        if($this->PageNo()==1){
            return;
        }

        // Position at 15 mm from bottom
        $this->SetY(-15);
        // Set font
        $this->SetFont($this->footer_font[0], $this->footer_font[1], $this->footer_font[2]);
        // Page number
        $this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
    
    public function setHeaderData($ln='', $lw=0, $lh=0, $ht='', $hs='', $tc=array(0,0,0), $lc=array(0,0,0)) {
        parent::setHeaderData($ln, $lw, $ht, $hs, $tc, $lc);
        $this->header_logo_hight = $lh;
    }    
 }

/**
 * 
 * 
 * params = hashArray:{
 *      id: string  //id del projecte
 *      path_templates:string,  // directori on es troben les plantilles latex usades per crear el pdf
 *      tmp_dir: string,    //directori temporal on crear el pdf
 *      lang: string  // idioma usat (CA, EN, ES, ...)
 *      mode: string  // pdf o zip
 *      data: hashArray:{
 *              titol:array os string    // linies de títol del document (cada ítem és una línia)
 *              contingut: string   //contingut latex ja rendaritzat
 */
class StaticPdfRenderer{    
    static $headerNum = array(0,0,0,0,0,0);
    static $headerFont = "helvetica";
    static $headerFontSize = 10;
    static $footerFont = "helvetica";
    static $footerFontSize = 8;
    static $firstPageFont = "Times";
    static $pagesFont = "helvetica";
    
    public static function renderDocument($params) {
        $style = ["B", "BI", "I", "I", ""];
        $output_filename = str_replace(":", "_", $params["id"]);
        
        $iocTcPdf = new IocTcPdf(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        
        $iocTcPdf->SetCreator("DOKUWIKI IOC");
//        $iocTcPdf->SetAuthor('Nicola Asuni');
//        $iocTcPdf->SetTitle('TCPDF Example 005');
//        $iocTcPdf->SetSubject('TCPDF Tutorial');
//        $iocTcPdf->SetKeywords('TCPDF, PDF, example, test, guide');
        
        $iocTcPdf->setHeaderData( $params["data"]["header_page_logo"], $params["data"]["header_page_wlogo"], $params["data"]["header_page_hlogo"], $params["data"]["header_ltext"], $params["data"]["header_rtext"]);
        
        // set header and footer fonts
        $iocTcPdf->setHeaderFont(Array(self::$headerFont, '', self::$headerFontSize));
        $iocTcPdf->setFooterFont(Array(self::$footerFont, '', self::$footerFontSize));

        // set default monospaced font
        $iocTcPdf->SetDefaultMonospacedFont("Courier");

        // set margins
        $iocTcPdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $iocTcPdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $iocTcPdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        // set auto page breaks
        $iocTcPdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // set image scale factor
        $iocTcPdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        //primera pàgina
        $iocTcPdf->SetFont(self::$firstPageFont, 'B', 35);
        $iocTcPdf->AddPage();
        $iocTcPdf->SetX(100);
        $y = 100;
        $iocTcPdf->SetY($y);
        for($i=0; $i<2; $i++){
            $iocTcPdf->Cell(0, 0, $params["data"]["titol"][$i], 0, 1);
        }
        $y +=100;
        $iocTcPdf->SetY($y);

        $iocTcPdf->SetFont(self::$firstPageFont, 'B', 20);
        for($i=2; $i<count($params["data"]["titol"]); $i++){
            $iocTcPdf->Cell(0, 0, $params["data"]["titol"][$i], 0, 1);
        }
        
        $iocTcPdf->AddPage();
        foreach ($params["data"]["contingut"] as $itemsDoc){
            self::renderHeader($itemsDoc, $iocTcPdf);
        }
        
        // add a new page for TOC
        $iocTcPdf->addTOCPage();

        // write the TOC title
        $iocTcPdf->SetFont('Times', 'B', 16);
        $iocTcPdf->MultiCell(0, 0, 'Índex', 0, 'C', 0, 1, '', '', true, 0);
        $iocTcPdf->Ln();

        $iocTcPdf->SetFont('Times', '', 12);

        // add a simple Table Of Content at first page
        // (check the example n. 59 for the HTML version)
        $iocTcPdf->addTOC(2, 'courier', '.', 'INDEX', 'B', array(128,0,0));

        // end of TOC page
        $iocTcPdf->endTOCPage();

        $params["id"];
        $iocTcPdf->Output("{$params['tmp_dir']}/$output_filename", 'F');

//        if (!file_exists($params["tmp_dir"])) mkdir($params["tmp_dir"], 0775, TRUE);
//        if (!file_exists($params["tmp_dir"]."/media")) mkdir($params["tmp_dir"]."/media", 0775, TRUE);
//
//        $frontCover = "frontCoverDoc.ltx";
//        $tocPage = "tocPageDoc.ltx";
//        $background = "bgCoverDoc.pdf";
//
//        $latex = self::renderHeader($params["data"]["htitol"], $params["path_templates"], $params["lang"]);
//        $latex.= self::renderCoverPage($params["data"]["titol"], $params["path_templates"], $frontCover, $params["tmp_dir"], $background);
//        $latex.= self::renderTocPage($params["data"]["htitol"], $params["path_templates"], $tocPage);
//        $latex.= $params["data"]['contingut'];
//        if (file_exists($params["path_templates"]."/footer.ltx")){
//            $latex .= io_readFile($params["path_templates"]."/footer.ltx");
//        }
//
        $result = array();
//        if ($params["mode"] === 'zip'){
////            self::createZip($output_filename,$params["tmp_dir"], $latex);
//        }else{
//            self::createLatex($params["id"], $output_filename, $params["tmp_dir"], $latex, $result);
//        }

        return $result;
    }    
    
    private static function getHeaderCounter($level){
        $ret = "";
        for($i=0; $i<=$level; $i++){
            $ret .= self::$headerNum[$i].".";
        }
        return $ret." ";
    }
    
    private static function incHeaderCounter($level){
        self::$headerNum[$level]++;
        for($i=$level+1; $i<count(self::$headerNum); $i++){
            self::$headerNum[$i]=0;
        }
        return self::getHeaderCounter($level);
    }
    
    private static function renderHeader($header, IocTcPdf &$iocTcPdf){
        $level = $header["level"]-1;
        $iocTcPdf->SetFont('Times', 'B', 12);
        $title = self::incHeaderCounter($level).$header["title"];
        $iocTcPdf->Bookmark($title, $level, 0);
        $iocTcPdf->Ln(5);
        $iocTcPdf->Cell(0, 0, $title, 0,1, "L");              
        $iocTcPdf->Ln(3);
        for ($i=0; $i<count($header["content"]); $i++){
            self::renderContent($header["content"][$i], $iocTcPdf);
        }
        for ($i=0; $i<count($header["children"]); $i++){
            self::renderHeader($header["children"][$i], $iocTcPdf);
        }
    }
    
    private static function renderContent($content, IocTcPdf &$iocTcPdf, $pre="", $post=""){
//        if($content instanceof StructuredNodeDoc){
//            self::renderStructuredContent($content, $iocTcPdf);
//        }else if($content instanceof LeafNodeDoc){
//            self::renderLeafContent($content, $iocTcPdf);
//        }else if($content instanceof TextNodeDoc){
//            self::renderLeafContent($content, $iocTcPdf);
//        }else{
//            //ERROR
//        }
        $iocTcPdf->SetFont('helvetica', '', 10);
        $iocTcPdf->writeHTML( self::getContent($content), TRUE, FALSE);      
        if($content["type"] == StructuredNodeDoc::ORDERED_LIST_TYPE
                || $content["type"] == StructuredNodeDoc::UNORDERED_LIST_TYPE
                || $content["type"] == StructuredNodeDoc::PARAGRAPH_TYPE){
            $iocTcPdf->Ln(3);
        }
    }
    
//    private static function renderStructuredContent($content, IocTcPdf &$iocTcPdf, $pre="", $post=""){
//        $limit = count($content["content"]);
//        for ($i=0; $i<$limit; $i++){
//            self::renderContent($content["content"][$i], $iocTcPdf, $pre, $post);
//        }        
//    }
//    
//    private static function renderTextContent($content, IocTcPdf &$iocTcPdf, $pre="", $post=""){
//        $iocTcPdf->SetFont('helvetica', '', 10);
//        $iocTcPdf->writeHTML($pre.$content["text"].$post, FALSE, FALSE);                      
//    }
//
//    private static function renderLeafContent($content, IocTcPdf &$iocTcPdf, $pre="", $post=""){
//        $iocTcPdf->SetFont('helvetica', '', 10);
//        switch($content["type"]){
//            case LeafNodeDoc::HORIZONTAL_RULE_TYPE:
//                $iocTcPdf->writeHTML("<hr>", true, false, false, false, '');
//                break;
//            case LeafNodeDoc::LINE_BREAK_TYPE:
//                $iocTcPdf->writeHTML("<br>", false, false, false, false, '');
//                break;
//        }
//    }
    
    private static function getContent($content){
        $ret = "";
        switch ($content["type"]){
            case ListItemNodeDoc::LIST_ITEM_TYPE:
                $ret = '<li  style="text-align:justify;">'.self::getStructuredContent($content)."</li>";
                break;
            case StructuredNodeDoc::DELETED_TYPE:
                $ret = "<del>".self::getStructuredContent($content)."</del>";
                break;
            case StructuredNodeDoc::EMPHASIS_TYPE:
                $ret = "<em>".self::getStructuredContent($content)."</em>";
                break;
            case StructuredNodeDoc::FOOT_NOTE_TYPE:
                //unsupported
                $ret = "";
                break;
            case StructuredNodeDoc::LIST_CONTENT_TYPE:
                $ret = "";
                break;
            case StructuredNodeDoc::MONOSPACE_TYPE:
                $ret = "<code>".self::getStructuredContent($content)."</code>";
                break;
            case StructuredNodeDoc::ORDERED_LIST_TYPE:
                $ret = "<ol>".self::getStructuredContent($content)."</ol>";
                break;
            case StructuredNodeDoc::PARAGRAPH_TYPE:
                $ret = '<p style="text-align:justify;">'.self::getStructuredContent($content).'</p>';
                break;
            case StructuredNodeDoc::QUOTE_TYPE:
                $ret = "<blockquote>".self::getStructuredContent($content)."</blockquote>";
                break;
            case StructuredNodeDoc::STRONG_TYPE:
                $ret = "<strong>".self::getStructuredContent($content)."</strong>";
                break;
            case StructuredNodeDoc::SUBSCRIPT_TYPE:
                $ret = "<sub>".self::getStructuredContent($content)."</sub>";
                break;
            case StructuredNodeDoc::SUPERSCRIPT_TYPE:
                $ret = "<sup>".self::getStructuredContent($content)."</sup>";
                break;
            case StructuredNodeDoc::UNDERLINE_TYPE:
                $ret = "<u>".self::getStructuredContent($content)."</u>";
                break;
            case StructuredNodeDoc::UNORDERED_LIST_TYPE:
                $ret = "<ul>".self::getStructuredContent($content)."</ul>";
                break;
            case TableNodeDoc::TABLE_TYPE:
                $style = $content["hasBorder"]?"style='border:1px solid black; border-collapse:collapse;'":"";
                $ret = "<table $style>".self::getStructuredContent($content)."</table>";
                break;
            case StructuredNodeDoc::TABLEROW_TYPE:
                $ret = "<tr>".self::getStructuredContent($content)."</tr>";
                break;
            case CellNodeDoc::TABLEHEADER_TYPE:
                $align = $content["align"]?"text-align:{$content["align"]};":"";
                $style = $content["hasBorder"]?"style='border:1px solid black; border-collapse:collapse; $align'":"";
                $colspan = $content["colspan"]>1?"colspan='{$content["colspan"]}'":"";
                $rowspan = $content["rowspan"]>1?"rowspan='{$content["rowspan"]}'":"";
                
                $ret = "<th $colspan $rowspan $style>".self::getStructuredContent($content)."</th>";
                break;
            case CellNodeDoc::TABLECELL_TYPE:
                $align = $content["align"]?"text-align:{$content["align"]};":"";
                $style = $content["hasBorder"]?"style='border:1px solid black; border-collapse:collapse; $align'":"";
                $colspan = $content["colspan"]>1?"colspan='{$content["colspan"]}'":"";
                $rowspan = $content["rowspan"]>1?"rowspan='{$content["rowspan"]}'":"";
                $ret = "<td $colspan $rowspan $style>".self::getStructuredContent($content)."</td>";
                break;
            case CodeNodeDoc::CODE_TEXT_TYPE:
                $ret = self::getTextContent($content);
                break;
            case TextNodeDoc::HTML_TEXT_TYPE:
                $ret = self::getTextContent($content);
                break;
            case TextNodeDoc::PLAIN_TEXT_TYPE:
                $ret = self::getTextContent($content);
                break;
            case TextNodeDoc::PREFORMATED_TEXT_TYPE:
                $ret = self::getTextContent($content);
                break;
            case TextNodeDoc::UNFORMATED_TEXT_TYPE:
                $ret = self::getTextContent($content);
                break;
            default :
                $ret = self::getLeafContent($content);
        }
        return $ret;
    }
    
    private static function getStructuredContent($content){
        $ret = "";
        $limit = count($content["content"]);
        for ($i=0; $i<$limit; $i++){
            $ret .= self::getContent($content["content"][$i]);
        }        
        return $ret;
    }
    
    private static function getTextContent($content){
        return $content["text"];
    }

    private static function getLeafContent($content){
        switch($content["type"]){
            case LeafNodeDoc::HORIZONTAL_RULE_TYPE:
                $ret = "<hr>";
                break;
            case LeafNodeDoc::LINE_BREAK_TYPE:
                $ret = "<br>";
                break;
            case LeafNodeDoc::APOSTROPHE_TYPE:
                $ret = "'";
                break;
        }
        return $ret;
    }
    
//    private static function renderHeader($titol, $path_templates, $lang) {
//        $langcontinue = array('CA' => 'continuació', 'DE' => 'fortsetzung', 'EN' => 'continued','ES' => 'continuación','FR' => 'suite','IT' => 'continua');
//        $latex = "";
//        if (file_exists("{$path_templates}/header.ltx")) {
//            $latex = io_readFile("{$path_templates}/header.ltx");
//            if ($latex) {
//                $qrcode = ($_SESSION['qrcode']) ? '\usepackage{pst-barcode,auto-pst-pdf}' : '';
//                $titol = trim(wordwrap($titol), 77, '\break ');
//                $aSearch = array("@IOCLANGUAGE@", "@IOCQRCODE@", "@DOC_TITOL@", "@IOCLANGCONTINUE@");
//                $aReplace = array($lang, $qrcode, $titol, $langcontinue[$lang]);
//                $latex = str_replace($aSearch, $aReplace, $latex);
//            }
//        }
//        return $latex;
//    }    
//    
//    private static function renderCoverPage($titol, $path_templates, $frontCover, $tmp_dir=NULL, $background=NULL){
//        $latex = "";
//        if (file_exists($path_templates."/$frontCover")) {
//            $latex = io_readFile($path_templates."/$frontCover");
//            if ($latex) {
//                $aSearch = array("@DOC_BACKGROUND@");
//                $aReplace = array("media/$background"); 
//                for ($i=0; $i<count($titol); $i++){
//                    $aSearch []= "@DOC_TITOL[$i]@";
//                    $aReplace []= $titol[$i];
//                }
//                $latex = str_replace($aSearch, $aReplace, $latex);
//                if ($background) {
//                    self::copyToTmp($path_templates, $background, $tmp_dir, "media/$background");
//                }
//            }
//        }
//        return $latex;
//    }
//    
//    private static function renderTocPage($titol, $path_templates, $tocPage) {
//        $latex = "";
//        if (file_exists($path_templates."/$tocPage")) {
//            $latex = io_readFile($path_templates."/$tocPage");
//            if ($latex) {
//                $aSearch = array("@DOC_TITOL@");
//                $aReplace = array($titol);
//                $latex = str_replace($aSearch, $aReplace, $latex);
//            }
//        }
//        return $latex;
//    }
    
    private static function copyToTmp($path_templates, $template, $tmp_dir, $dest){
        return copy("$path_templates/$template", "$tmp_dir/$dest");
    }

    private static function createLatex($id, $filename, $path, &$text, &$result){
        io_saveFile("$path/$filename.tex", $text);

        $shell_escape = ($_SESSION['qrcode']) ? "-shell-escape" : "";
        @exec("cd $path && pdflatex -draftmode $shell_escape -halt-on-error $filename.tex", $sortida, $return);
        if ($return === 0){
            //One more to calculate correctly size tables
            @exec("cd $path && pdflatex -draftmode $shell_escape -halt-on-error $filename.tex" , $sortida, $return);
            if ($_SESSION['onemoreparsing']){
                @exec("cd $path && pdflatex -draftmode $shell_escape -halt-on-error $filename.tex" , $sortida, $return);
            }
            @exec("cd $path && pdflatex $shell_escape -halt-on-error $filename.tex" , $sortida, $return);
        }
        //Si pdflatex no está instalado localmente, probaremos ejecutarlo en otro servidor con una conexión ssh remota
        else {
            $destino = mediaFN(str_replace("_", ":", $id));
            $moreparsing = ($_SESSION['onemoreparsing']) ? 1 : 0;
            @exec(DOKU_INC."../sh/remoteSSHexport.sh $path $filename $destino $moreparsing $shell_escape", $sortida, $return);
        }
        return $return;
    }    
    
//    private static function createZip($filename, $path, $text){
//
//        $zip = new ZipArchive;
//        $res = $zip->open("$path/$filename.zip", ZipArchive::CREATE);
//        if ($res === TRUE) {
//            $zip->addFromString("$filename.tex", $text);
//            $zip->addEmptyDir('media');
//            $files = array();
//            if ($this->getFiles("$path/media", $files)) {
//                foreach($files as $f){
//                    $zip->addFile($f, 'media/'.basename($f));
//                }
//                $zip->close();
//                $result = $this->returnData($path, "$filename.zip", 'zip');
//            }else {
//                $res = FALSE;
//            }
//        }
//
//        if ($res !== TRUE) {
//            $zip->close();
//            $result = $this->getLogError($filename);
//        }
//
//        return $result;
//    }

}
