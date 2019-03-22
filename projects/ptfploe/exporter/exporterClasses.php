<?php
/**
 * projecte 'ptfploe'
 * exporterClasses: clases de procesos, establecidas en el fichero de configuración,
 *                  correspondientes a los tipos de datos del proyecto
 * @culpable Rafael Claver
 */
if (!defined('DOKU_INC')) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', realpath(DOKU_INC."lib/plugins/"));
if (!defined('EXPORT_TMP')) define('EXPORT_TMP', DOKU_PLUGIN."tmp/latex/");
require_once DOKU_PLUGIN."wikiiocmodel/exporter/BasicExporterClasses.php";

class renderObject extends BasicRenderObject {

}

require_once (DOKU_INC.'inc/inc_ioc/tcpdf/tcpdf_include.php');

class IocTcPdf extends TCPDF {
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
    static $tableCounter = 0;
    static $tableReferences = array();
    static $headerNum = array(0,0,0,0,0,0);
    static $headerFont = "helvetica";
    static $headerFontSize = 10;
    static $footerFont = "helvetica";
    static $footerFontSize = 8;
    static $firstPageFont = "Times";
    static $pagesFont = "helvetica";

    static $state = ["table" =>["type" => "table"]];

    public static function renderDocument($params, $output_filename="") {
        $style = ["B", "BI", "I", "I", ""];
        if(empty($output_filename)){
            $output_filename = str_replace(":", "_", $params["id"]);
        }

        $iocTcPdf = new IocTcPdf(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        $iocTcPdf->SetCreator("DOKUWIKI IOC");

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

        $len = count($params["data"]["contingut"]);
        for($i=0; $i<$len; $i++){
            self::resolveReferences($params["data"]["contingut"][$i]);
        }
        for($i=0; $i<$len; $i++){
            self::renderHeader($params["data"]["contingut"][$i], $iocTcPdf);
        }

        // add a new page for TOC
        $iocTcPdf->addTOCPage();

        // write the TOC title
        $iocTcPdf->SetFont('Times', 'B', 16);
        $iocTcPdf->MultiCell(0, 0, 'Índex', 0, 'C', 0, 1, '', '', true, 0);
        $iocTcPdf->Ln();

        $iocTcPdf->SetFont('Times', '', 12);

        // add a simple Table Of Content at first page
        $iocTcPdf->addTOC(2, 'courier', '.', 'INDEX', 'B', array(128,0,0));

        // end of TOC page
        $iocTcPdf->endTOCPage();

        $params["id"];
        $iocTcPdf->Output("{$params['tmp_dir']}/$output_filename", 'F');

        $result = array();

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

    private static function resolveReferences($content){
        if($content["type"]===TableFrame::TABLEFRAME_TYPE_TABLE
                || $content["type"]===TableFrame::TABLEFRAME_TYPE_ACCOUNTING){
            self::$tableCounter++;
            self::$tableReferences[$content["id"]] = self::$tableCounter;
        }
        for ($i=0; $i<count($content["content"]); $i++){
            self::resolveReferences($content["content"][$i]);
        }
        for ($i=0; $i<count($content["children"]); $i++){
            self::resolveReferences($content["children"][$i]);
        }
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
        $iocTcPdf->SetFont('helvetica', '', 10);
        $iocTcPdf->writeHTML( self::getContent($content), TRUE, FALSE);
        if($content["type"] == StructuredNodeDoc::ORDERED_LIST_TYPE
                || $content["type"] == StructuredNodeDoc::UNORDERED_LIST_TYPE
                || $content["type"] == StructuredNodeDoc::PARAGRAPH_TYPE){
            $iocTcPdf->Ln(3);
        }
    }

    private static function getContent($content){
        $char = "";
        $ret = "";
        switch ($content["type"]){
            case ListItemNodeDoc::LIST_ITEM_TYPE:
                $ret = '<li  style="text-align:justify;">'.self::getStructuredContent($content)."</li>";
                break;
            case StructuredNodeDoc::DELETED_TYPE:
                $ret = " <del>".self::getStructuredContent($content)."</del> ";
                break;
            case StructuredNodeDoc::EMPHASIS_TYPE:
                $ret = " <em>".self::getStructuredContent($content)."</em> ";
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
            case StructuredNodeDoc::SINGLEQUOTE_TYPE:
                $char = "'";
            case StructuredNodeDoc::DOUBLEQUOTE_TYPE:
                $char = empty($char)?"\"":$char;
                $ret = " $char".self::getStructuredContent($content)."$char ";
                break;
            case StructuredNodeDoc::QUOTE_TYPE:
                $ret = "<blockquote>".self::getStructuredContent($content)."</blockquote>";
                break;
            case StructuredNodeDoc::STRONG_TYPE:
                $ret = " <strong>".self::getStructuredContent($content)."</strong> ";
                break;
            case StructuredNodeDoc::SUBSCRIPT_TYPE:
                $ret = " <sub>".self::getStructuredContent($content)."</sub> ";
                break;
            case StructuredNodeDoc::SUPERSCRIPT_TYPE:
                $ret = " <sup>".self::getStructuredContent($content)."</sup> ";
                break;
            case StructuredNodeDoc::UNDERLINE_TYPE:
                $ret = " <u>".self::getStructuredContent($content)."</u> ";
                break;
            case StructuredNodeDoc::UNORDERED_LIST_TYPE:
                $ret = "<ul>".self::getStructuredContent($content)."</ul>";
                break;
            case TableFrame::TABLEFRAME_TYPE_TABLE:
            case TableFrame::TABLEFRAME_TYPE_ACCOUNTING:
                $ret = "<div nobr=\"true\">";
                if($content["title"]){
                    $ret .= "<h4  style=\"text-align:center;\"> Taula ".self::$tableReferences[$content["id"]].". ".$content["title"]."</h4>";
                }
                $ret .= self::getStructuredContent($content);
                if($content["footer"]){
                    if($content["title"]){
                        $ret .= "<p style=\"text-align:justify; font-size:80%;\">".$content["footer"]."</p>";
                    }else{
                        $ret .= "<p style=\"text-align:justify; font-size:80%;\"> Taula ".self::$tableReferences[$content["id"]].". ".$content["footer"]."</p>";
                    }
                }
                $ret .= "</div>";
                break;
            case TableNodeDoc::TABLE_TYPE:

                $ret = "<table$style   cellpadding=\"5\" nobr=\"true\">";
                $ret .= self::getStructuredContent($content)."</table>";
                break;
            case StructuredNodeDoc::TABLEROW_TYPE:
                $ret = "<tr>".self::getStructuredContent($content)."</tr>";
                break;
            case CellNodeDoc::TABLEHEADER_TYPE:
                $align = $content["align"]?"text-align:{$content["align"]};":"text-align:center;";
                $style = $content["hasBorder"]?' style="border:1px solid black; border-collapse:collapse; '.$align.' font-weight:bold; background-color:#808080;"':"style=\"$align font-weight:bold; background-color:#808080;\"";
                $colspan = $content["colspan"]>1?' colspan="'.$content["colspan"].'"':"";
                $rowspan = $content["rowspan"]>1?' rowspan="'.$content["rowspan"].'"':"";

                $ret = "<th$colspan$rowspan$style>".self::getStructuredContent($content)."</th>";
                break;
            case CellNodeDoc::TABLECELL_TYPE:
                $align = $content["align"]?"text-align:{$content["align"]};":"text-align:center";
                $style = $content["hasBorder"]?' style="border:1px solid black; border-collapse:collapse; '.$align.'"':"style=\"$align\"";
                $style = $content["hasBorder"]?' style="border:1px solid black; border-collapse:collapse; '.$align.'"':"";
                $colspan = $content["colspan"]>1?' colspan="'.$content["colspan"].'"':"";
                $rowspan = $content["rowspan"]>1?' rowspan="'.$content["rowspan"].'"':"";
                $ret = "<td$colspan$rowspan$style>".self::getStructuredContent($content)."</td>";
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
            case ReferenceNodeDoc::REFERENCE_TYPE:
                if($content["referenceType"] === ReferenceNodeDoc::REF_TABLE_TYPE){
                    $ret = " <em>Taula ".self::$tableReferences[trim($content["referenceId"])]."</em> ";
                }else{
                    //figure
                    //$ret = " <em>Figura ".self::$figureReferences[trim($content["referenceId"])]."</em> ";
                }
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
        if(!empty($content["text"]) && empty(trim($content["text"]))){
            $ret = " ";
        }else{
            $ret = trim($content["text"]);
        }
        return $ret;
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
            case LeafNodeDoc::BACKSLASH_TYPE:
                $ret = "\\";
                break;
        }
        return $ret;
    }
}
